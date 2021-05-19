<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210519132624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE data_mod (id INT AUTO_INCREMENT NOT NULL, smart_mod_id INT NOT NULL, INDEX IDX_5378B2FD2CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enterprise (id INT AUTO_INCREMENT NOT NULL, social_reason VARCHAR(50) NOT NULL, niu VARCHAR(50) DEFAULT NULL, rccm VARCHAR(50) DEFAULT NULL, address VARCHAR(80) DEFAULT NULL, phone_number VARCHAR(15) NOT NULL, email VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, logo VARCHAR(255) DEFAULT NULL, country VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, enterprise_id INT NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(100) NOT NULL, power_subscribed DOUBLE PRECISION NOT NULL, fuel_price DOUBLE PRECISION DEFAULT NULL, currency VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_694309E4A97D1AC3 (enterprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_user (site_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B6096BB0F6BD1646 (site_id), INDEX IDX_B6096BB0A76ED395 (user_id), PRIMARY KEY(site_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE smart_mod (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, name VARCHAR(50) NOT NULL, module_id VARCHAR(20) NOT NULL, mod_type VARCHAR(20) NOT NULL, INDEX IDX_786B66EEF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, enterprise_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, first_name VARCHAR(80) NOT NULL, last_name VARCHAR(80) NOT NULL, phone_number VARCHAR(50) NOT NULL, country_code VARCHAR(20) DEFAULT NULL, verification_code VARCHAR(20) DEFAULT NULL, verified TINYINT(1) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649A97D1AC3 (enterprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE data_mod ADD CONSTRAINT FK_5378B2FD2CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id)');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4A97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES enterprise (id)');
        $this->addSql('ALTER TABLE site_user ADD CONSTRAINT FK_B6096BB0F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site_user ADD CONSTRAINT FK_B6096BB0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE smart_mod ADD CONSTRAINT FK_786B66EEF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES enterprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4A97D1AC3');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A97D1AC3');
        $this->addSql('ALTER TABLE site_user DROP FOREIGN KEY FK_B6096BB0F6BD1646');
        $this->addSql('ALTER TABLE smart_mod DROP FOREIGN KEY FK_786B66EEF6BD1646');
        $this->addSql('ALTER TABLE data_mod DROP FOREIGN KEY FK_5378B2FD2CFA4C13');
        $this->addSql('ALTER TABLE site_user DROP FOREIGN KEY FK_B6096BB0A76ED395');
        $this->addSql('DROP TABLE data_mod');
        $this->addSql('DROP TABLE enterprise');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE site_user');
        $this->addSql('DROP TABLE smart_mod');
        $this->addSql('DROP TABLE user');
    }
}
