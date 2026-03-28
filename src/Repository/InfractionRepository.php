<?php

namespace App\Repository;

use App\Entity\Infraction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Infraction>
 */
class InfractionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Infraction::class);
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['dateInfraction' => 'DESC']);
    }

    public function findByUserAndStatut(User $user, string $statut): array
    {
        return $this->findBy(['user' => $user, 'statut' => $statut], ['dateInfraction' => 'DESC']);
    }

    public function findByAgent(User $agent): array
    {
        return $this->findBy(['agent' => $agent], ['dateInfraction' => 'DESC']);
    }

    public function countByStatut(): array
    {
        $qb = $this->createQueryBuilder('i')
            ->select('i.statut', 'COUNT(i.id) as count')
            ->groupBy('i.statut')
            ->getQuery()
            ->getResult();

        $result = ['a_payer' => 0, 'paye' => 0, 'conteste' => 0];
        foreach ($qb as $row) {
            $result[$row['statut']] = (int) $row['count'];
        }

        return $result;
    }
}
