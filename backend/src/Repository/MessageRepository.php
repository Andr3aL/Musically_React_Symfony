<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findConversation(int $userId1, int $userId2): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)')
            ->setParameter('user1', $userId1)
            ->setParameter('user2', $userId2)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUserConversations(int $userId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT DISTINCT 
                CASE 
                    WHEN m.sender_id = :userId THEN m.receiver_id 
                    ELSE m.sender_id 
                END as other_user_id,
                u.first_name,
                u.last_name,
                u.image,
                (SELECT content FROM message WHERE 
                    (sender_id = :userId AND receiver_id = other_user_id) OR 
                    (sender_id = other_user_id AND receiver_id = :userId) 
                    ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM message WHERE 
                    (sender_id = :userId AND receiver_id = other_user_id) OR 
                    (sender_id = other_user_id AND receiver_id = :userId) 
                    ORDER BY created_at DESC LIMIT 1) as last_message_at
            FROM message m
            JOIN user u ON u.id = CASE 
                WHEN m.sender_id = :userId THEN m.receiver_id 
                ELSE m.sender_id 
            END
            WHERE m.sender_id = :userId OR m.receiver_id = :userId
            ORDER BY last_message_at DESC
        ';
        
        $result = $conn->executeQuery($sql, ['userId' => $userId]);
        
        return $result->fetchAllAssociative();
    }
}