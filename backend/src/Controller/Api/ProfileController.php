<?php

namespace App\Controller\Api;

use App\Entity\Instrument;
use App\Entity\Style;
use App\Entity\User;
use App\Entity\UserInstrument;
use App\Entity\UserStyle;
use App\Enum\Niveau;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController extends AbstractController
{
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB

    #[Route('/api/profile/upload-photo', name: 'api_profile_upload_photo', methods: ['POST'])]
    public function uploadPhoto(
        Request $request,
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('photo');

        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier envoye'], 400);
        }

        // Security: check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return new JsonResponse(['error' => 'Fichier trop volumineux (max 5 Mo)'], 400);
        }

        // Security: check MIME type from file content (not just extension)
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            return new JsonResponse(['error' => 'Format non autorise. Formats acceptes : JPEG, PNG, WebP, GIF'], 400);
        }

        // Security: verify it's a real image by trying to read it
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            return new JsonResponse(['error' => 'Le fichier n\'est pas une image valide'], 400);
        }

        // Security: check image dimensions (max 4000x4000)
        if ($imageInfo[0] > 4000 || $imageInfo[1] > 4000) {
            return new JsonResponse(['error' => 'Image trop grande (max 4000x4000 pixels)'], 400);
        }

        // Generate safe filename: uniqid + webp extension
        $newFilename = uniqid('user_', true) . '.webp';
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/users/';

        // Convert to WebP for consistent format and smaller size
        $srcImage = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($file->getPathname()),
            'image/png' => @imagecreatefrompng($file->getPathname()),
            'image/webp' => @imagecreatefromwebp($file->getPathname()),
            'image/gif' => @imagecreatefromgif($file->getPathname()),
            default => false,
        };

        if (!$srcImage) {
            return new JsonResponse(['error' => 'Impossible de traiter l\'image'], 500);
        }

        // Resize to max 500x500 keeping aspect ratio
        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);
        $maxDim = 500;

        if ($srcW > $maxDim || $srcH > $maxDim) {
            $ratio = min($maxDim / $srcW, $maxDim / $srcH);
            $newW = (int)($srcW * $ratio);
            $newH = (int)($srcH * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
            imagedestroy($srcImage);
            $srcImage = $resized;
        }

        // Save as WebP
        if (!imagewebp($srcImage, $uploadDir . $newFilename, 85)) {
            imagedestroy($srcImage);
            return new JsonResponse(['error' => 'Erreur lors de la sauvegarde'], 500);
        }
        imagedestroy($srcImage);

        // Delete old photo if exists
        $oldImage = $currentUser->getImage();
        if ($oldImage) {
            $oldPath = $uploadDir . $oldImage;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Update user
        $currentUser->setImage($newFilename);
        $currentUser->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse([
            'image' => $newFilename,
            'message' => 'Photo mise a jour avec succes',
        ]);
    }

    #[Route('/api/profile/preferences', name: 'api_profile_get_preferences', methods: ['GET'])]
    public function getPreferences(
        #[CurrentUser] User $currentUser,
    ): JsonResponse {
        $userStyles = [];
        foreach ($currentUser->getUserStyles() as $us) {
            $userStyles[] = [
                'id' => $us->getId(),
                'styleId' => $us->getStyle()->getId(),
                'nomStyle' => $us->getStyle()->getNomStyle(),
                'isPrincipal' => $us->isPrincipal(),
            ];
        }

        $userInstruments = [];
        foreach ($currentUser->getUserInstruments() as $ui) {
            $userInstruments[] = [
                'id' => $ui->getId(),
                'instrumentId' => $ui->getInstrument()->getId(),
                'nomInstrument' => $ui->getInstrument()->getNomInstrument(),
                'isMain' => $ui->isMain(),
                'niveau' => $ui->getNiveau()->value,
            ];
        }

        return new JsonResponse([
            'styles' => $userStyles,
            'instruments' => $userInstruments,
        ]);
    }

    #[Route('/api/profile/preferences', name: 'api_profile_save_preferences', methods: ['POST'])]
    public function savePreferences(
        Request $request,
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $em,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // --- Styles ---
        if (isset($data['styles'])) {
            // Remove existing
            foreach ($currentUser->getUserStyles() as $us) {
                $em->remove($us);
            }
            $em->flush();

            foreach ($data['styles'] as $styleData) {
                $style = $em->getRepository(Style::class)->find($styleData['styleId']);
                if (!$style) continue;

                $us = new UserStyle();
                $us->setUser($currentUser);
                $us->setStyle($style);
                $us->setIsPrincipal($styleData['isPrincipal'] ?? false);
                $em->persist($us);
            }
        }

        // --- Instruments ---
        if (isset($data['instruments'])) {
            // Remove existing
            foreach ($currentUser->getUserInstruments() as $ui) {
                $em->remove($ui);
            }
            $em->flush();

            foreach ($data['instruments'] as $instrData) {
                $instrument = $em->getRepository(Instrument::class)->find($instrData['instrumentId']);
                if (!$instrument) continue;

                $ui = new UserInstrument();
                $ui->setUser($currentUser);
                $ui->setInstrument($instrument);
                $ui->setIsMain($instrData['isMain'] ?? false);
                $ui->setNiveau(Niveau::from($instrData['niveau'] ?? 'debutant'));
                $em->persist($ui);
            }
        }

        $em->flush();

        return new JsonResponse(['message' => 'Preferences sauvegardees']);
    }
}
