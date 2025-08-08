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
     * Find all users except the specified user (typically the current admin)
     * 
     * @param User $excludeUser The user to exclude from results
     * @return User[]
     */
    public function findAllExceptUser(User $excludeUser): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.id != :excludeUserId')
            ->setParameter('excludeUserId', $excludeUser->getId())
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all users except the one with specified ID
     * 
     * @param int $excludeUserId The user ID to exclude from results
     * @return User[]
     */
    public function findAllExceptUserId(int $excludeUserId): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.id != :excludeUserId')
            ->setParameter('excludeUserId', $excludeUserId)
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Save a user entity
     */
    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a user entity
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find users by role
     * 
     * @param string $role The role to search for (e.g., 'ROLE_ADMIN', 'ROLE_USER')
     * @return User[]
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode($role))
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Count users by role
     * 
     * @param string $role The role to count
     * @return int
     */
    public function countByRole(string $role): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode($role))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find users who haven't verified their email
     * 
     * @return User[]
     */
    public function findUnverifiedUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isVerified = :isVerified')
            ->setParameter('isVerified', false)
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find verified users only
     * 
     * @return User[]
     */
    public function findVerifiedUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isVerified = :isVerified')
            ->setParameter('isVerified', true)
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unverified users
     * 
     * @return int
     */
    public function countUnverifiedUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.isVerified = :isVerified')
            ->setParameter('isVerified', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find unverified users by role
     * 
     * @param string $role The role to search for
     * @return User[]
     */
    public function findUnverifiedUsersByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isVerified = :isVerified')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('isVerified', false)
            ->setParameter('role', json_encode($role))
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users by verification status and exclude a specific user
     * 
     * @param bool $isVerified Verification status to filter by
     * @param User $excludeUser User to exclude from results
     * @return User[]
     */
    public function findByVerificationStatusExceptUser(bool $isVerified, User $excludeUser): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isVerified = :isVerified')
            ->andWhere('u.id != :excludeUserId')
            ->setParameter('isVerified', $isVerified)
            ->setParameter('excludeUserId', $excludeUser->getId())
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users by first name and/or last name
     * 
     * @param string|null $firstName First name to search for
     * @param string|null $lastName Last name to search for
     * @return User[]
     */
    public function findByName(?string $firstName = null, ?string $lastName = null): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($firstName !== null) {
            $qb->andWhere('u.firstName LIKE :firstName')
               ->setParameter('firstName', '%' . $firstName . '%');
        }

        if ($lastName !== null) {
            $qb->andWhere('u.lastName LIKE :lastName')
               ->setParameter('lastName', '%' . $lastName . '%');
        }

        return $qb->orderBy('u.lastName', 'ASC')
                  ->addOrderBy('u.firstName', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Get user statistics
     * 
     * @return array Statistics about users
     */
    public function getUserStatistics(): array
    {
        $totalUsers = $this->count([]);
        $verifiedUsers = $this->count(['isVerified' => true]);
        $unverifiedUsers = $this->count(['isVerified' => false]);

        return [
            'total' => $totalUsers,
            'verified' => $verifiedUsers,
            'unverified' => $unverifiedUsers,
            'verification_rate' => $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 2) : 0
        ];
    }
}