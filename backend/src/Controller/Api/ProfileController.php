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
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController extends AbstractController
{
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
