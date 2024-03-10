<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Account;
use App\Entity\Currency;
use App\Entity\Rate;
use App\Entity\Transaction;

use Faker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Fixtures load a "fake" set of data into a database that can then be used
 * while developing application
 *
 */
class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Generates first price
     *
     */
    function getFirstCotation($cryptoname) {
      return ord(substr($cryptoname, 0, 1)) + rand(0, 10);
    }

    /**
     * Generates prices based on first price
     *
     */
    function getCotationFor($cryptoname) {
      return ((rand(0, 99) > 40) ? 1 : -1) * ((rand(0, 99) > 49) ? ord(substr($cryptoname, 0, 1)) : ord(substr($cryptoname, -1))) * (rand(1, 10) * .01);
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        // Creates an admin and customer user

        $admin = new User();
        $admin->setFirstname('Anusan')
              ->setLastname('Baskaramoorthy')
              ->setUsername('Admin')
              ->setEmail('admin@bitchest.com')
              ->setAddress($faker->streetAddress)
              ->setDistrict($faker->region)
              ->setPostalCode(75003)
              ->setCity($faker->city)
              ->setCountry($faker->country)
              ->setPhone('0142928100')
              ->setEnabled(1)
              ->setRoles(['ROLE_ADMIN'])
              ->setPassword($this->passwordEncoder->encodePassword($admin, 'password'))
              ->setLocation('48.8706371-2.3169393')
              ->setIp($faker->ipv4);

        $customer = new User();
        $customer->setFirstname('Anusan')
                 ->setLastname('Baskaramoorthy')
                 ->setUsername('Client')
                 ->setEmail('user@bitchest.com')
                 ->setAddress($faker->streetAddress)
                 ->setDistrict($faker->region)
                 ->setPostalCode(75003)
                 ->setCity($faker->city)
                 ->setCountry($faker->country)
                 ->setPhone('0142928100')
                 ->setEnabled(1)
                 ->setRoles(['ROLE_USER'])
                 ->setPassword($this->passwordEncoder->encodePassword($customer, 'password'))
                 ->setLocation('48.8706371-2.3169393')
                 ->setIp($faker->ipv4);

        $manager->persist($admin);
        $manager->persist($customer);
        $manager->flush();

        // Creates an account for customer
        $account = new Account();
        $account->setUserId($customer)
                ->setCredit(300)
                ->setDebit(100)
                ->setBalance(200);
        $manager->persist($account);
        $manager->flush();

        // Creates currencies
        $examples = [
          [
            'name' => 'Bitcoin',
            'acronym' => 'BTC',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/btc@2x.png'
          ],
          [
            'name' => 'Ethereum',
            'acronym' => 'ETH',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/eth@2x.png'
          ],
          [
            'name' => 'Ripple',
            'acronym' => 'XRP',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/xrp@2x.png'
          ],
          [
            'name' => 'Bitcoin Cash',
            'acronym' => 'BCH',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/bch@2x.png'
          ],
          [
            'name' => 'Cardano',
            'acronym' => 'ADA',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/ada@2x.png'
          ],
          [
            'name' => 'Litecoin',
            'acronym' => 'LTC',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/ltc@2x.png'
          ],
          [
            'name' => 'Nem',
            'acronym' => 'NANO',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/10/nano@2x.png'
          ],
          [
            'name' => 'Stellar Lumens',
            'acronym' => 'XLM',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/xlm@2x-1.png'
          ],
          [
            'name' => 'IOTA',
            'acronym' => 'MIOTA',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/miota@2x.png'
          ],
          [
            'name' => 'Dash',
            'acronym' => 'DASH',
            'thumbnailUrl' => 'https://cryptoast.fr/wp-content/uploads/2018/04/dash@2x.png'
          ]
        ];
        $currencies = [];
        foreach ($examples as $example) {
          $currency = new Currency();
          $currency->setName($example['name'])
                   ->setAcronym($example['acronym'])
                   ->setThumbnailUrl($example['thumbnailUrl']);
          $currencies[] = $currency;
          $manager->persist($currency);
        }
        $manager->flush();

        // Creates currencies rates
        $rates = [];
        foreach ($currencies as $currency) {
          $priceReference = $this->getFirstCotation($currency->getName());
          $price = $priceReference;
          $date = new \DateTime(date('d F Y', strtotime('-30 days')));
          for ($i = 1; $i <= 30; $i++) { // 30 days
            if ($i > 1) {
              $priceReference = $price;
              $price = $price + $this->getCotationFor($currency->getName());
            }
            $variation = (($price - $priceReference) / $priceReference) * 100;
            $date->add(new \DateInterval('P1D'));
            $rate = new Rate();
            $rate->setCurrencyId($currency)
                 ->setPrice($price)
                 ->setVariation($variation)
                 ->setDate($date);
            $rates[] = $rate;
            $manager->persist($rate);
            $manager->flush();
          }
        }

        // Create a transaction
        $yesterday = new \DateTime(date('d F Y', strtotime('-1 day')));
        foreach ($rates as $key => $rate) {
          if ($rate->getCurrencyId()->getId() == $currencies[0]->getId()) {
            if ($key == 28) {
              $price = $rate->getPrice();
            }
          }
        }
        $transaction = new Transaction();
        $transaction->setType('purchase')
                    ->setState('valided')
                    ->setAccountId($account)
                    ->setCurrencyId($currencies[0])
                    ->setAmount(100)
                    ->setPrice($price)
                    ->setQuantity(100 / $price)
                    ->setDate($yesterday);
        $manager->persist($transaction);
        $manager->flush();

    }
}
