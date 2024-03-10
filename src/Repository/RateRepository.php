<?php

namespace App\Repository;

use App\Entity\Rate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Rate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rate[]    findAll()
 * @method Rate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Rate::class);
    }

    /**
     * Price
     *
     * @return an float corresponding of last currency rate
     */
    public function findLastRate($currencyId) {

      return $this->createQueryBuilder('r')
        ->where('r.currencyId =' . $currencyId)
        ->orderBy('r.date', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult()
      ;

    }

}
