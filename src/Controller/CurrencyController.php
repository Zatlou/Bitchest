<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Currency;
use App\Entity\Transaction;
use App\Entity\Rate;

use App\Form\OrderType;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CurrencyController extends AbstractController
{
    /**
     * Homepage
     * Dashboard of currencies and their rates
     *
     * @Route("/", name="currency")
     */
    public function currencyAction()
    {
        // Gets available currencies
        $repository = $this->getDoctrine()->getRepository(Currency::class);
        $currencies = $repository->findAll();

        $params = [
          'currencies' => $currencies
        ];

        return $this->render('currency/currency.html.twig', $params);
    }

    /**
     * Order a currency
     *
     * @Route("/currency/order/add/{currencyId}", name="currency_order")
     */
    public function currencyOrderAddAction($currencyId, Request $request, ObjectManager $manager)
    {
        // Creates new order
        $order = new Transaction();

        // Creates form
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        // Gets account
        $repository = $this->getDoctrine()->getRepository(Account::class);
        $account = $repository->find($this->getUser()->getAccounts()[0]->getId());

        // Gets currency
        $repository = $this->getDoctrine()->getRepository(Currency::class);
        $currency = $repository->find($currencyId);

        // Gets rate
        $repository = $this->getDoctrine()->getRepository(Rate::class);
        $rate = $repository->findLastRate($currencyId)->getPrice();

        // Saves order
        if ($form->isSubmitted() && $form->isValid()) {

          $amount = $form['amount']->getData();
          $quantity = $amount / $rate;

          // Prepares order
          $order->setType('purchase')
                ->setState('valided') // As part of the project, transactions are automatically validated
                ->setAccountId($account)
                ->setCurrencyId($currency)
                ->setQuantity($quantity)
                ->setPrice($rate)
                ->setAmount($amount);
          $manager->persist($order);

          // Prepares account update
          $account->setCredit($account->getCredit() - $amount)
                  ->setDebit($account->getDebit() + $amount)
                  ->setBalance($account->getCredit() - $account->getDebit());
          $manager->persist($account);

          // Order is canceled if customer does not have enough money for the requested quantity
          if ($account->getCredit() < 0) {
            $this->addFlash('error', 'Vous n\'avez pas assez d\'argent');
            return $this->redirectToRoute('currency_order', ['currencyId' => $currencyId]);
          }

          // Applies operations
          $manager->flush();
          $this->addFlash('success', 'Commande effectuée');
          return $this->redirectToRoute('account');
          
        }

        $params = [
          'currency' => $currency,
          'order' => $order,
          'orderForm' => $form->createView()
        ];

        return $this->render('currency/form.html.twig', $params);
    }

}
