<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211104041210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE air_conditioner_data ADD smart_mod_id INT DEFAULT NULL, ADD date_time DATETIME NOT NULL');
        $this->addSql('ALTER TABLE air_conditioner_data ADD CONSTRAINT FK_6BAAD892CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id)');
        $this->addSql('CREATE INDEX IDX_6BAAD892CFA4C13 ON air_conditioner_data (smart_mod_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE datetime_data.temp (id INT AUTO_INCREMENT NOT NULL, smart_mod_id INT NOT NULL, p DOUBLE PRECISION NOT NULL, q DOUBLE PRECISION NOT NULL, s DOUBLE PRECISION NOT NULL, cosfi DOUBLE PRECISION NOT NULL, total_running_hours INT NOT NULL, fuel_inst_consumption DOUBLE PRECISION NOT NULL, total_energy INT NOT NULL, nb_performed_start_ups INT NOT NULL, nb_mains_interruption INT NOT NULL, date_time DATETIME NOT NULL, INDEX IDX_EDBBD6882CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE load_data_energy.temp (id INT AUTO_INCREMENT NOT NULL, date_time DATETIME NOT NULL, pamoy DOUBLE PRECISION DEFAULT NULL, pbmoy DOUBLE PRECISION DEFAULT NULL, pcmoy DOUBLE PRECISION DEFAULT NULL, pmoy DOUBLE PRECISION DEFAULT NULL, samoy DOUBLE PRECISION DEFAULT NULL, sbmoy DOUBLE PRECISION DEFAULT NULL, scmoy DOUBLE PRECISION DEFAULT NULL, smoy DOUBLE PRECISION DEFAULT NULL, cosfia DOUBLE PRECISION DEFAULT NULL, cosfib DOUBLE PRECISION DEFAULT NULL, cosfic DOUBLE PRECISION DEFAULT NULL, cosfi DOUBLE PRECISION DEFAULT NULL, eaa DOUBLE PRECISION DEFAULT NULL, eab DOUBLE PRECISION DEFAULT NULL, eac DOUBLE PRECISION DEFAULT NULL, ea DOUBLE PRECISION DEFAULT NULL, era DOUBLE PRECISION DEFAULT NULL, erb DOUBLE PRECISION DEFAULT NULL, erc DOUBLE PRECISION DEFAULT NULL, er DOUBLE PRECISION DEFAULT NULL, vamoy DOUBLE PRECISION DEFAULT NULL, vbmoy DOUBLE PRECISION DEFAULT NULL, vcmoy DOUBLE PRECISION DEFAULT NULL, smart_mod_id INT NOT NULL, INDEX IDX_697171EA2CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE air_conditioner_data DROP FOREIGN KEY FK_6BAAD892CFA4C13');
        $this->addSql('DROP INDEX IDX_6BAAD892CFA4C13 ON air_conditioner_data');
        $this->addSql('ALTER TABLE air_conditioner_data DROP smart_mod_id, DROP date_time');
    }
}
