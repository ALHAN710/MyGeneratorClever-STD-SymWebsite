<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220610175136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE datetime_data CHANGE p p DOUBLE PRECISION DEFAULT NULL, CHANGE q q DOUBLE PRECISION DEFAULT NULL, CHANGE s s DOUBLE PRECISION DEFAULT NULL, CHANGE cosfi cosfi DOUBLE PRECISION DEFAULT NULL, CHANGE total_running_hours total_running_hours INT DEFAULT NULL, CHANGE fuel_inst_consumption fuel_inst_consumption DOUBLE PRECISION DEFAULT NULL, CHANGE total_energy total_energy INT DEFAULT NULL, CHANGE nb_performed_start_ups nb_performed_start_ups INT DEFAULT NULL, CHANGE nb_mains_interruption nb_mains_interruption INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE datetime_data CHANGE p p DOUBLE PRECISION NOT NULL, CHANGE q q DOUBLE PRECISION NOT NULL, CHANGE s s DOUBLE PRECISION NOT NULL, CHANGE cosfi cosfi DOUBLE PRECISION NOT NULL, CHANGE total_running_hours total_running_hours INT NOT NULL, CHANGE fuel_inst_consumption fuel_inst_consumption DOUBLE PRECISION NOT NULL, CHANGE total_energy total_energy INT NOT NULL, CHANGE nb_performed_start_ups nb_performed_start_ups INT NOT NULL, CHANGE nb_mains_interruption nb_mains_interruption INT NOT NULL');
    }
}
