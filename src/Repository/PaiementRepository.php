<?php

namespace App\Repository;

use App\Entity\Paiement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiement>
 */
class PaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    public function findWithFilter(?string $date, ?string $statut, ?int $taxeId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.taxe', 't')
            ->orderBy('p.datePaiement', 'DESC');

        if ($date) {
            $qb->andWhere('p.datePaiement LIKE :date')
               ->setParameter('date', $date.'%');
        }

        if ($statut) {
            $qb->andWhere('p.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($taxeId) {
            $qb->andWhere('t.id = :taxe')
               ->setParameter('taxe', $taxeId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['datePaiement' => 'DESC']);
    }

    public function getTotalCollecte(): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.montant)')
            ->where('p.statut = :statut')
            ->setParameter('statut', 'paye')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }
}
