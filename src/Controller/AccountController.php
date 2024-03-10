<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Currency;
use App\Entity\Transaction;
use App\Entity\Rate;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * Homepage of wallet
     *
     * @Route("/account", name="account")
     */
    public function accountAction()
    {
        // Gets purchased currencies
        $repository = $this->getDoctrine()->getRepository(Transaction::class);
        $currencies = $repository->findCurrencies($this->getUser()->getAccounts()[0]->getId());



        $params = [
          'currencies' => $currencies
        ];

        return $this->render('account/account.html.twig', $params);
    }

    /**
     * Details of wallet
     * List of purchases transactions (by currency)
     *
     * @Route("/account/details/{currencyId}", name="account_details")
     */
    public function accountDetailsAction($currencyId)
    {
        // Gets currency
        $repository = $this->getDoctrine()->getRepository(Currency::class);
        $currency = $repository->find($currencyId);

        // Gets purchase transactions
        $repository = $this->getDoctrine()->getRepository(Transaction::class);
        $transactions = $repository->findPurchaseTransactions($this->getUser()->getAccounts()[0]->getId(), $currencyId);

        $params = [
          'currency' => $currency,
          'transactions' => $transactions
        ];

        return $this->render('account/details.html.twig', $params);
    }

    /**
     * Sells currencies
     *
     * @Route("/account/sell/{currencyId}", name="account_sell")
     */
    public function accountSellAction($currencyId, ObjectManager $manager)
    {
        // Gets user's account
        $repository = $this->getDoctrine()->getRepository(Account::class);
        $account = $repository->find($this->getUser()->getAccounts()[0]->getId());

        // Gets user's currencies
        $repository = $this->getDoctrine()->getRepository(Transaction::class);
        $currencies = $repository->findPurchaseTransactions($account->getId(), $currencyId);

        // Calculates the potential sale gain
        $repository = $this->getDoctrine()->getRepository(Rate::class);
        $price = $repository->findLastRate($currencyId)->getPrice();
        $quantities = 0;
        foreach ($currencies as $currency) {
          $quantities += (float) $currency['quantity'];
        }
        $credits = $quantities * $price;

        // Gets currency
        $repository = $this->getDoctrine()->getRepository(Currency::class);
        $currency = $repository->find($currencyId);

        // Creates a transaction
        $transaction = new Transaction();
        $transaction->setType('sale')
                    ->setState('valided')
                    ->setAccountId($account)
                    ->setCurrencyId($currency)
                    ->setQuantity($quantities)
                    ->setPrice($price)
                    ->setAmount($credits);

        // Updates account
        $account->setCredit($account->getCredit() + $credits);
        $account->setBalance($account->getCredit() - $account->getDebit());

        // Applies operations
        $manager->persist($transaction);
        $manager->persist($account);
        $manager->flush();

        $this->addFlash('success', 'Vos coins ont bien été vendus');

        return $this->redirectToRoute('account');
    }

}
