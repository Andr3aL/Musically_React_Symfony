<?php

namespace App\Controller\Api;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class MessageController extends AbstractController
{
    #[Route('/api/chat/{userId}', name: 'api_chat_get', methods: ['GET'])]
    public function getMessages(
        int $userId,
        #[CurrentUser] User $currentUser,
        MessageRepository $messageRepository
    ): JsonResponse {
        $messages = $messageRepository->findConversation($currentUser->getId(), $userId);
        
        $data = array_map(function (Message $message) {
            return [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('c'),
                'readStatus' => $message->isReadStatus(),
                'sender' => [
                    'id' => $message->getSender()->getId(),
                    'firstName' => $message->getSender()->getFirstName(),
                    'lastName' => $message->getSender()->getLastName(),
                ],
                'receiver' => [
                    'id' => $message->getReceiver()->getId(),
                    'firstName' => $message->getReceiver()->getFirstName(),
                    'lastName' => $message->getReceiver()->getLastName(),
                ],
            ];
        }, $messages);

        return new JsonResponse($data);
    }

    #[Route('/api/chat/send', name: 'api_chat_send', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        #[CurrentUser] User $currentUser,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['receiverId']) || !isset($data['content'])) {
            return new JsonResponse(['error' => 'Champs requis manquants'], 400);
        }

        $receiver = $entityManager->getRepository(User::class)->find($data['receiverId']);
        if (!$receiver) {
            return new JsonResponse(['error' => 'Destinataire non trouvé'], 404);
        }

        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setContent($data['content']);
        $message->setCreatedAt(new \DateTime());
        $message->setReadStatus(false);

        $entityManager->persist($message);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'createdAt' => $message->getCreatedAt()->format('c'),
            'readStatus' => $message->isReadStatus(),
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

    #[Route('/api/chat/conversations', name: 'api_chat_conversations', methods: ['GET'])]
    public function getConversations(
        #[CurrentUser] User $currentUser,
        MessageRepository $messageRepository
    ): JsonResponse {
        $conversations = $messageRepository->findUserConversations($currentUser->getId());
        
        return new JsonResponse($conversations);
    }
}