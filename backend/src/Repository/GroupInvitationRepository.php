<?php

namespace App\Repository;

use App\Entity\Band;
use App\Entity\GroupInvitation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupInvitation>
 */
class GroupInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupInvitation::class);
    }

    /**
     * Find pending invitations received by a user
     */
    public function findPendingForUser(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.receiver = :user')
            ->andWhere('i.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', GroupInvitation::STATUS_PENDING)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find pending invitation between two users (in either direction)
     */
    public function findPendingBetweenUsers(User $user1, User $user2): ?GroupInvitation
    {
        return $this->createQueryBuilder('i')
            ->where('(i.sender = :user1 AND i.receiver = :user2) OR (i.sender = :user2 AND i.receiver = :user1)')
            ->andWhere('i.status = :status')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('status', GroupInvitation::STATUS_PENDING)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Check if two users share at least one band
     */
    public function usersShareBand(User $user1, User $user2): bool
    {
        $em = $this->getEntityManager();
        
        $query = $em->createQuery('
            SELECT COUNT(bm1.id)
            FROM App\Entity\BandMember bm1
            JOIN App\Entity\BandMember bm2 WITH bm1.band = bm2.band
            WHERE bm1.user = :user1 AND bm2.user = :user2
        ')
        ->setParameter('user1', $user1)
        ->setParameter('user2', $user2);

        return (int) $query->getSingleScalarResult() > 0;
    }

    /**
     * Find pending invitation for a specific band and receiver
     */
    public function findPendingForBandAndReceiver(Band $band, User $receiver): ?GroupInvitation
    {
        return $this->createQueryBuilder('i')
            ->where('i.createdBand = :band')
            ->andWhere('i.receiver = :receiver')
            ->andWhere('i.status = :status')
            ->setParameter('band', $band)
            ->setParameter('receiver', $receiver)
            ->setParameter('status', GroupInvitation::STATUS_PENDING)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
