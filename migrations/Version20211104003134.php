<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211104003134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE air_conditioner_data (id INT AUTO_INCREMENT NOT NULL, return_air_temp DOUBLE PRECISION DEFAULT NULL, return_air_hum DOUBLE PRECISION DEFAULT NULL, fan_speed1 DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        //$this->addSql('DROP TABLE datetime_data.temp');
        //$this->addSql('DROP TABLE load_data_energy.temp');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE datetime_data.temp (id INT AUTO_INCREMENT NOT NULL, smart_mod_id INT NOT NULL, p DOUBLE PRECISION NOT NULL, q DOUBLE PRECISION NOT NULL, s DOUBLE PRECISION NOT NULL, cosfi DOUBLE PRECISION NOT NULL, total_running_hours INT NOT NULL, fuel_inst_consumption DOUBLE PRECISION NOT NULL, total_energy INT NOT NULL, nb_performed_start_ups INT NOT NULL, nb_mains_interruption INT NOT NULL, date_time DATETIME NOT NULL, INDEX IDX_EDBBD6882CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE load_data_energy.temp (id INT AUTO_INCREMENT NOT NULL, date_time DATETIME NOT NULL, pamoy DOUBLE PRECISION DEFAULT NULL, pbmoy DOUBLE PRECISION DEFAULT NULL, pcmoy DOUBLE PRECISION DEFAULT NULL, pmoy DOUBLE PRECISION DEFAULT NULL, samoy DOUBLE PRECISION DEFAULT NULL, sbmoy DOUBLE PRECISION DEFAULT NULL, scmoy DOUBLE PRECISION DEFAULT NULL, smoy DOUBLE PRECISION DEFAULT NULL, cosfia DOUBLE PRECISION DEFAULT NULL, cosfib DOUBLE PRECISION DEFAULT NULL, cosfic DOUBLE PRECISION DEFAULT NULL, cosfi DOUBLE PRECISION DEFAULT NULL, eaa DOUBLE PRECISION DEFAULT NULL, eab DOUBLE PRECISION DEFAULT NULL, eac DOUBLE PRECISION DEFAULT NULL, ea DOUBLE PRECISION DEFAULT NULL, era DOUBLE PRECISION DEFAULT NULL, erb DOUBLE PRECISION DEFAULT NULL, erc DOUBLE PRECISION DEFAULT NULL, er DOUBLE PRECISION DEFAULT NULL, vamoy DOUBLE PRECISION DEFAULT NULL, vbmoy DOUBLE PRECISION DEFAULT NULL, vcmoy DOUBLE PRECISION DEFAULT NULL, smart_mod_id INT NOT NULL, INDEX IDX_697171EA2CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE air_conditioner_data');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
