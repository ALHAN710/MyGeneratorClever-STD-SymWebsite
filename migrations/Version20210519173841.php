<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210519173841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alarm_status (id INT AUTO_INCREMENT NOT NULL, smart_mod_id INT NOT NULL, genset_running INT NOT NULL, cg INT NOT NULL, mains_presence INT NOT NULL, cr INT NOT NULL, maintenance_request INT NOT NULL, low_fuel INT NOT NULL, presence_water_in_fuel INT NOT NULL, overspeed INT NOT NULL, max_freq INT NOT NULL, min_freq INT NOT NULL, max_volt INT NOT NULL, min_volt INT NOT NULL, max_batt_volt INT NOT NULL, min_batt_volt INT NOT NULL, overload INT NOT NULL, short_circuit INT NOT NULL, mains_inc_seq INT NOT NULL, genset_inc_seq INT NOT NULL, differential_intervention INT NOT NULL, UNIQUE INDEX UNIQ_262D8E612CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE datetime_data (id INT AUTO_INCREMENT NOT NULL, smart_mod_id INT NOT NULL, p DOUBLE PRECISION NOT NULL, q DOUBLE PRECISION NOT NULL, s DOUBLE PRECISION NOT NULL, cosfi DOUBLE PRECISION NOT NULL, total_running_hours INT NOT NULL, fuel_inst_comsumption DOUBLE PRECISION NOT NULL, total_energy INT NOT NULL, nb_perfomed_start_ups INT NOT NULL, nb_mains_interrruption INT NOT NULL, INDEX IDX_EDBBD6882CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE no_datetime_data (id INT AUTO_INCREMENT NOT NULL, smart_mod_id INT NOT NULL, l12_g DOUBLE PRECISION NOT NULL, l13_g DOUBLE PRECISION NOT NULL, l23_g DOUBLE PRECISION NOT NULL, l1_n DOUBLE PRECISION NOT NULL, l2_n DOUBLE PRECISION NOT NULL, l3_n DOUBLE PRECISION NOT NULL, l12_m DOUBLE PRECISION NOT NULL, l13_m DOUBLE PRECISION NOT NULL, l23_m DOUBLE PRECISION NOT NULL, i1 DOUBLE PRECISION NOT NULL, i2 DOUBLE PRECISION NOT NULL, i3 DOUBLE PRECISION NOT NULL, freq DOUBLE PRECISION NOT NULL, i_diff DOUBLE PRECISION NOT NULL, fuel_level INT NOT NULL, water_level INT NOT NULL, oil_level INT NOT NULL, air_pressure DOUBLE PRECISION NOT NULL, oil_pressure DOUBLE PRECISION NOT NULL, water_temperature DOUBLE PRECISION NOT NULL, cooler_temperature DOUBLE PRECISION NOT NULL, engine_speed INT NOT NULL, batt_voltage DOUBLE PRECISION NOT NULL, hours_to_maintenance INT NOT NULL, UNIQUE INDEX UNIQ_709E59662CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE alarm_status ADD CONSTRAINT FK_262D8E612CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id)');
        $this->addSql('ALTER TABLE datetime_data ADD CONSTRAINT FK_EDBBD6882CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id)');
        $this->addSql('ALTER TABLE no_datetime_data ADD CONSTRAINT FK_709E59662CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id)');
        $this->addSql('ALTER TABLE site ADD mains_interrupt_day_limit INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE alarm_status');
        $this->addSql('DROP TABLE datetime_data');
        $this->addSql('DROP TABLE no_datetime_data');
        $this->addSql('ALTER TABLE site DROP mains_interrupt_day_limit');
    }
}
