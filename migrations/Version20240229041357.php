<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229041357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crypto (id INT AUTO_INCREMENT NOT NULL, crypto_name VARCHAR(85) NOT NULL, description LONGTEXT NOT NULL, symbole VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crypto_rates (id INT AUTO_INCREMENT NOT NULL, crypto_id_id INT DEFAULT NULL, rate_date DATETIME NOT NULL, rate_value DOUBLE PRECISION NOT NULL, INDEX IDX_5262BB4769F28E2C (crypto_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, wallet_id_id INT DEFAULT NULL, trans_type VARCHAR(85) NOT NULL, trans_amount DOUBLE PRECISION NOT NULL, trans_date DATETIME NOT NULL, trans_price DOUBLE PRECISION NOT NULL, INDEX IDX_723705D1F43F82D (wallet_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(60) NOT NULL, last_name VARCHAR(85) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallet (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, purchase_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_7C68921F9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallet_crypto (wallet_id INT NOT NULL, crypto_id INT NOT NULL, INDEX IDX_D888B7C1712520F3 (wallet_id), INDEX IDX_D888B7C1E9571A63 (crypto_id), PRIMARY KEY(wallet_id, crypto_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crypto_rates ADD CONSTRAINT FK_5262BB4769F28E2C FOREIGN KEY (crypto_id_id) REFERENCES crypto (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F43F82D FOREIGN KEY (wallet_id_id) REFERENCES wallet (id)');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921F9D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE wallet_crypto ADD CONSTRAINT FK_D888B7C1712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wallet_crypto ADD CONSTRAINT FK_D888B7C1E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE crypto_rates DROP FOREIGN KEY FK_5262BB4769F28E2C');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F43F82D');
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921F9D86650F');
        $this->addSql('ALTER TABLE wallet_crypto DROP FOREIGN KEY FK_D888B7C1712520F3');
        $this->addSql('ALTER TABLE wallet_crypto DROP FOREIGN KEY FK_D888B7C1E9571A63');
        $this->addSql('DROP TABLE crypto');
        $this->addSql('DROP TABLE crypto_rates');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE wallet');
        $this->addSql('DROP TABLE wallet_crypto');
    }
}
