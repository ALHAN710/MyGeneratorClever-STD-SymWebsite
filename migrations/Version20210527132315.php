<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210527132315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE load_data_energy (id INT AUTO_INCREMENT NOT NULL, date_time DATETIME NOT NULL, pamoy DOUBLE PRECISION DEFAULT NULL, pbmoy DOUBLE PRECISION DEFAULT NULL, pcmoy DOUBLE PRECISION DEFAULT NULL, pmoy DOUBLE PRECISION DEFAULT NULL, samoy DOUBLE PRECISION DEFAULT NULL, sbmoy DOUBLE PRECISION DEFAULT NULL, scmoy DOUBLE PRECISION DEFAULT NULL, smoy DOUBLE PRECISION DEFAULT NULL, cosfia DOUBLE PRECISION DEFAULT NULL, cosfib DOUBLE PRECISION DEFAULT NULL, cosfic DOUBLE PRECISION DEFAULT NULL, cosfi DOUBLE PRECISION DEFAULT NULL, eaa DOUBLE PRECISION DEFAULT NULL, eab DOUBLE PRECISION DEFAULT NULL, eac DOUBLE PRECISION DEFAULT NULL, ea DOUBLE PRECISION DEFAULT NULL, era DOUBLE PRECISION DEFAULT NULL, erb DOUBLE PRECISION DEFAULT NULL, erc DOUBLE PRECISION DEFAULT NULL, er DOUBLE PRECISION DEFAULT NULL, vamoy DOUBLE PRECISION DEFAULT NULL, vbmoy DOUBLE PRECISION DEFAULT NULL, vcmoy DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE load_data_energy');
    }
}
