<?php

namespace App\Repository;

use App\Entity\Reclamation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['dateSoumission' => 'DESC']);
    }

    public function findAllEnCours(): array
    {
        return $this->findBy(['statut' => 'en_cours'], ['dateSoumission' => 'DESC']);
    }

    public function countByStatut(): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.statut', 'COUNT(r.id) as count')
            ->groupBy('r.statut')
            ->getQuery()
            ->getResult();

        $result = ['en_cours' => 0, 'resolue' => 0, 'rejetee' => 0];
        foreach ($qb as $row) {
            $result[$row['statut']] = (int) $row['count'];
        }

        return $result;
    }
}
