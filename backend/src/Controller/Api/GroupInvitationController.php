<?php

namespace App\Controller\Api;

use App\Entity\Band;
use App\Entity\BandMember;
use App\Entity\GroupInvitation;
use App\Entity\Style;
use App\Entity\User;
use App\Repository\GroupInvitationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GroupInvitationController extends AbstractController
{
    /**
     * Search users by keyword (name, city, instrument, style)
     */
    #[Route('/api/search/users', name: 'api_users_search', methods: ['GET'])]
    public function searchUsers(
        Request $request,
        UserRepository $userRepository
    ): JsonResponse {
        $query = trim($request->query->get('q', ''));

        if ($query === '') {
            return new JsonResponse([]);
        }

        $users = $userRepository->search($query);

        $data = array_map(function (User $user) {
            // Main instrument
            $mainInstrument = null;
            foreach ($user->getUserInstruments() as $ui) {
                if ($ui->isMain()) {
                    $mainInstrument = $ui->getInstrument()->getNomInstrument();
                    break;
                }
            }

            // Main style
            $mainStyle = null;
            foreach ($user->getUserStyles() as $us) {
                if ($us->isPrincipal()) {
                    $mainStyle = $us->getStyle()->getNomStyle();
                    break;
                }
            }

            return [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'image' => $user->getImage(),
                'city' => $user->getCity(),
                'country' => $user->getCountry(),
                'mainInstrument' => $mainInstrument,
                'mainStyle' => $mainStyle,
            ];
        }, $users);

        return new JsonResponse($data);
    }

    /**
     * Get bands needing setup where current user is admin
     */
    #[Route('/api/bands/pending-setup', name: 'api_bands_pending_setup', methods: ['GET'])]
    public function getBandsPendingSetup(
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $qb = $entityManager->createQueryBuilder();
        $bands = $qb->select('b')
            ->from(Band::class, 'b')
            ->join('b.members', 'm')
            ->where('m.user = :user')
            ->andWhere('m.isAdmin = true')
            ->andWhere('b.needsSetup = true')
            ->setParameter('user', $currentUser)
            ->getQuery()
            ->getResult();
        
        $data = array_map(function (Band $band) use ($entityManager) {
            $members = [];
            foreach ($band->getMembers() as $member) {
                $members[] = [
                    'id' => $member->getUser()->getId(),
                    'firstName' => $member->getUser()->getFirstName(),
                    'lastName' => $member->getUser()->getLastName(),
                    'image' => $member->getUser()->getImage(),
                    'isAdmin' => $member->isAdmin(),
                ];
            }
            
            return [
                'id' => $band->getId(),
                'nameBand' => $band->getNameBand(),
                'members' => $members,
                'dateCreation' => $band->getDateCreation()->format('c'),
            ];
        }, $bands);
        
        return new JsonResponse($data);
    }

    /**
     * Setup/update a band's details
     */
    #[Route('/api/bands/{id}/setup', name: 'api_band_setup', methods: ['POST'])]
    public function setupBand(
        int $id,
        Request $request,
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $band = $entityManager->getRepository(Band::class)->find($id);
        
        if (!$band) {
            return new JsonResponse(['error' => 'Groupe non trouvé'], 404);
        }
        
        // Check if current user is admin of this band
        $isAdmin = false;
        foreach ($band->getMembers() as $member) {
            if ($member->getUser()->getId() === $currentUser->getId() && $member->isAdmin()) {
                $isAdmin = true;
                break;
            }
        }
        
        if (!$isAdmin) {
            return new JsonResponse(['error' => 'Vous devez être admin du groupe'], 403);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['nameBand']) && !empty($data['nameBand'])) {
            $band->setNameBand($data['nameBand']);
        }
        
        if (isset($data['description'])) {
            $band->setDescription($data['description']);
        }
        
        if (isset($data['styleId'])) {
            if ($data['styleId']) {
                $style = $entityManager->getRepository(Style::class)->find($data['styleId']);
                if ($style) {
                    $band->setStyle($style);
                }
            } else {
                $band->setStyle(null);
            }
        }
        
        // Mark setup as complete
        $band->setNeedsSetup(false);
        
        $entityManager->flush();
        
        return new JsonResponse([
            'id' => $band->getId(),
            'nameBand' => $band->getNameBand(),
            'description' => $band->getDescription(),
            'style' => $band->getStyle() ? [
                'id' => $band->getStyle()->getId(),
                'nom_style' => $band->getStyle()->getNomStyle(),
            ] : null,
            'message' => 'Groupe configuré avec succès !',
        ]);
    }

    /**
     * Upload band image
     */
    #[Route('/api/bands/{id}/upload-image', name: 'api_band_upload_image', methods: ['POST'])]
    public function uploadBandImage(
        int $id,
        Request $request,
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $band = $entityManager->getRepository(Band::class)->find($id);
        
        if (!$band) {
            return new JsonResponse(['error' => 'Groupe non trouvé'], 404);
        }
        
        // Check if current user is admin of this band
        $isAdmin = false;
        foreach ($band->getMembers() as $member) {
            if ($member->getUser()->getId() === $currentUser->getId() && $member->isAdmin()) {
                $isAdmin = true;
                break;
            }
        }
        
        if (!$isAdmin) {
            return new JsonResponse(['error' => 'Vous devez être admin du groupe'], 403);
        }
        
        /** @var UploadedFile|null $imageFile */
        $imageFile = $request->files->get('image');
        
        if (!$imageFile) {
            return new JsonResponse(['error' => 'Aucune image fournie'], 400);
        }
        
        // Use VichUploader
        $band->setImageFile($imageFile);
        $entityManager->flush();
        
        return new JsonResponse([
            'id' => $band->getId(),
            'photoBand' => $band->getPhotoBand(),
            'message' => 'Image mise à jour avec succès !',
        ]);
    }

    /**
     * Get all styles for dropdown
     */
    #[Route('/api/styles', name: 'api_styles_list', methods: ['GET'])]
    public function getStyles(EntityManagerInterface $entityManager): JsonResponse {
        $styles = $entityManager->getRepository(Style::class)->findAll();
        
        $data = array_map(function (Style $style) {
            return [
                'id' => $style->getId(),
                'nom_style' => $style->getNomStyle(),
            ];
        }, $styles);
        
        return new JsonResponse($data);
    }

    /**
     * Check if current user shares a band with another user
     */
    #[Route('/api/users/{userId}/share-band', name: 'api_user_share_band', methods: ['GET'])]
    public function checkShareBand(
        int $userId,
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $entityManager,
        GroupInvitationRepository $invitationRepository
    ): JsonResponse {
        $otherUser = $entityManager->getRepository(User::class)->find($userId);
        if (!$otherUser) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        $shareBand = $invitationRepository->usersShareBand($currentUser, $otherUser);
        $pendingInvitation = $invitationRepository->findPendingBetweenUsers($currentUser, $otherUser);

        return new JsonResponse([
            'shareBand' => $shareBand,
            'hasPendingInvitation' => $pendingInvitation !== null,
            'pendingInvitation' => $pendingInvitation ? [
                'id' => $pendingInvitation->getId(),
                'senderId' => $pendingInvitation->getSender()->getId(),
                'receiverId' => $pendingInvitation->getReceiver()->getId(),
            ] : null,
        ]);
    }

    /**
     * Send a group invitation to another user
     */
    #[Route('/api/invitations/send', name: 'api_invitation_send', methods: ['POST'])]
    public function sendInvitation(
        Request $request,
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $entityManager,
        GroupInvitationRepository $invitationRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['receiverId'])) {
            return new JsonResponse(['error' => 'receiverId requis'], 400);
        }

        $receiver = $entityManager->getRepository(User::class)->find($data['receiverId']);
        if (!$receiver) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        if ($receiver->getId() === $currentUser->getId()) {
            return new JsonResponse(['error' => 'Vous ne pouvez pas vous inviter vous-même'], 400);
        }

        // Check if they already share a band
        if ($invitationRepository->usersShareBand($currentUser, $receiver)) {
            return new JsonResponse(['error' => 'Vous êtes déjà dans un groupe ensemble'], 400);
        }

        // Check if there's already a pending invitation
        $existingInvitation = $invitationRepository->findPendingBetweenUsers($currentUser, $receiver);
        if ($existingInvitation) {
            return new JsonResponse(['error' => 'Une invitation est déjà en cours'], 400);
        }

        $invitation = new GroupInvitation();
        $invitation->setSender($currentUser);
        $invitation->setReceiver($receiver);

        $entityManager->persist($invitation);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $invitation->getId(),
            'message' => 'Invitation envoyée avec succès',
            'sender' => [
                'id' => $currentUser->getId(),
                'firstName' => $currentUser->getFirstName(),
                'lastName' => $currentUser->getLastName(),
            ],
            'receiver' => [
                'id' => $receiver->getId(),
                'firstName' => $receiver->getFirstName(),
                'lastName' => $receiver->getLastName(),
            ],
        ], 201);
    }

    /**
     * Get pending invitations for current user
     */
    #[Route('/api/invitations/pending', name: 'api_invitations_pending', methods: ['GET'])]
    public function getPendingInvitations(
        #[CurrentUser] User $currentUser,
        GroupInvitationRepository $invitationRepository
    ): JsonResponse {
        $invitations = $invitationRepository->findPendingForUser($currentUser);

        $data = array_map(function (GroupInvitation $invitation) {
            return [
                'id' => $invitation->getId(),
                'sender' => [
                    'id' => $invitation->getSender()->getId(),
                    'firstName' => $invitation->getSender()->getFirstName(),
                    'lastName' => $invitation->getSender()->getLastName(),
                    'image' => $invitation->getSender()->getImage(),
                ],
                'createdAt' => $invitation->getCreatedAt()->format('c'),
            ];
        }, $invitations);

        return new JsonResponse($data);
    }

    /**
     * Respond to an invitation (accept or reject)
     */
    #[Route('/api/invitations/{id}/respond', name: 'api_invitation_respond', methods: ['POST'])]
    public function respondToInvitation(
        int $id,
        Request $request,
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $entityManager,
        GroupInvitationRepository $invitationRepository
    ): JsonResponse {
        $invitation = $invitationRepository->find($id);

        if (!$invitation) {
            return new JsonResponse(['error' => 'Invitation non trouvée'], 404);
        }

        if ($invitation->getReceiver()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Vous ne pouvez pas répondre à cette invitation'], 403);
        }

        if (!$invitation->isPending()) {
            return new JsonResponse(['error' => 'Cette invitation a déjà été traitée'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $accept = $data['accept'] ?? false;

        if ($accept) {
            $invitation->accept();
            
            // Create a new band with needsSetup = true
            $band = new Band();
            $band->setNameBand('Nouveau groupe'); // Temporary name
            $band->setDateCreation(new \DateTime());
            $band->setNeedsSetup(true);
            $entityManager->persist($band);
            
            // Add sender as admin member
            $senderMember = new BandMember();
            $senderMember->setUser($invitation->getSender());
            $senderMember->setBand($band);
            $senderMember->setIsAdmin(true);
            $senderMember->setJoinedAt(new \DateTime());
            $entityManager->persist($senderMember);
            
            // Add receiver as member
            $receiverMember = new BandMember();
            $receiverMember->setUser($invitation->getReceiver());
            $receiverMember->setBand($band);
            $receiverMember->setIsAdmin(false);
            $receiverMember->setJoinedAt(new \DateTime());
            $entityManager->persist($receiverMember);
            
            // Link invitation to created band
            $invitation->setCreatedBand($band);
        } else {
            $invitation->reject();
        }

        $entityManager->flush();

        $response = [
            'id' => $invitation->getId(),
            'status' => $invitation->getStatus(),
            'message' => $accept ? 'Invitation acceptée - Groupe créé !' : 'Invitation refusée',
        ];
        
        if ($accept && $invitation->getCreatedBand()) {
            $response['bandId'] = $invitation->getCreatedBand()->getId();
            $response['needsSetup'] = true;
        }

        return new JsonResponse($response);
    }
}
