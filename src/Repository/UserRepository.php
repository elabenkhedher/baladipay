<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByCin(string $cin): ?User
    {
        return $this->findOneBy(['cin' => $cin, 'role' => 'ROLE_CITOYEN']);
    }

    public function findAllCitoyens(): array
    {
        return $this->findBy(['role' => 'ROLE_CITOYEN']);
    }

    public function findAllPolice(): array
    {
        return $this->findBy(['role' => 'ROLE_POLICE']);
    }
}
