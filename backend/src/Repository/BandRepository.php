<?php

namespace App\Repository;

use App\Entity\Band;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Band>
 */
class BandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Band::class);
    }

    /**
     * @return Band[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.members', 'm')
            ->leftJoin('m.user', 'u')
            ->where('LOWER(b.nameBand) LIKE :q')
            ->orWhere('LOWER(u.firstName) LIKE :q')
            ->orWhere('LOWER(u.lastName) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($query) . '%')
            ->groupBy('b.id')
            ->orderBy('b.nameBand', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
