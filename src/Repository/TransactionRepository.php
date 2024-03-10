<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\Currency;
use App\Entity\Rate;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Customer's currencies
     *
     * @return an array of purchased currencies
     */
    public function findCurrencies($accountId) {

      $em = $this->getEntityManager();

      $query = $em->createQuery('
        SELECT (c.id) as id, (c.name) as name, (c.acronym) as acronym, (c.thumbnailUrl) as thumbnailUrl, SUM(t.quantity) as quantity, SUM(t.amount) as amount, (r.price) as price
        FROM App\Entity\Transaction t, App\Entity\Currency c, App\Entity\Rate r
        WHERE
          t.accountId = :accountId
          AND t.type = \'purchase\'
          AND t.state = \'valided\'
          AND t.currencyId = c.id
          AND t.currencyId = r.currencyId
          AND r.date = :date
          AND NOT EXISTS (
            SELECT t2.id
            FROM App\Entity\Transaction t2
            WHERE
              t2.accountId = t.accountId
              AND t2.type = \'sale\'
              AND t2.state = \'valided\'
              AND t2.currencyId = t.currencyId
              AND t2.date >= t.date
          )
          GROUP BY c.name
      ');

      $query->setParameters(array(
        'accountId' => $accountId,
        'date' =>  new \DateTime(date("d F Y", strtotime('NOW')))
      ));

      return $query->getResult();

    }

    /**
     * Customer's transactions
     *
     * @return an array of purchase transactions
     */
    public function findPurchaseTransactions($accountId, $currencyId) {

      $em = $this->getEntityManager();

      $query = $em->createQuery('
        SELECT (t.accountId) as accountId, (t.quantity) as quantity, (t.price) as price, (t.amount) as amount, (t.date) as date
        FROM App\Entity\Transaction t
        WHERE
          t.accountId = :accountId
          AND t.currencyId = :currencyId
          AND t.type = \'purchase\'
          AND t.state = \'valided\'
          AND NOT EXISTS (
            SELECT t2.id
            FROM App\Entity\Transaction t2
            WHERE
              t2.accountId = t.accountId
        	    AND t2.currencyId = t.currencyId
        	    AND t2.type = \'sale\'
              AND t2.state = \'valided\'
              AND t2.date >= t.date
          )
      ');

      $query->setParameters(array(
        'accountId' => $accountId,
        'currencyId' =>  $currencyId
      ));

      return $query->getResult();

    }

}
