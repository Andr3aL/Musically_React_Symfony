<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Search users by keyword across name, city, instrument, and style.
     *
     * @return User[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.userInstruments', 'ui')
            ->leftJoin('ui.instrument', 'i')
            ->leftJoin('u.userStyles', 'us')
            ->leftJoin('us.style', 's')
            ->where('LOWER(u.firstName) LIKE :q')
            ->orWhere('LOWER(u.lastName) LIKE :q')
            ->orWhere('LOWER(u.city) LIKE :q')
            ->orWhere('LOWER(i.nom_instrument) LIKE :q')
            ->orWhere('LOWER(s.nom_style) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($query) . '%')
            ->groupBy('u.id')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
