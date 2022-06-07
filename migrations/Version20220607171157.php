<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220607171157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE no_datetime_data CHANGE l12_g l12_g DOUBLE PRECISION DEFAULT NULL, CHANGE l13_g l13_g DOUBLE PRECISION DEFAULT NULL, CHANGE l23_g l23_g DOUBLE PRECISION DEFAULT NULL, CHANGE l1_n l1_n DOUBLE PRECISION DEFAULT NULL, CHANGE l2_n l2_n DOUBLE PRECISION DEFAULT NULL, CHANGE l3_n l3_n DOUBLE PRECISION DEFAULT NULL, CHANGE l12_m l12_m DOUBLE PRECISION DEFAULT NULL, CHANGE l13_m l13_m DOUBLE PRECISION DEFAULT NULL, CHANGE l23_m l23_m DOUBLE PRECISION DEFAULT NULL, CHANGE i1 i1 DOUBLE PRECISION DEFAULT NULL, CHANGE i2 i2 DOUBLE PRECISION DEFAULT NULL, CHANGE i3 i3 DOUBLE PRECISION DEFAULT NULL, CHANGE freq freq DOUBLE PRECISION DEFAULT NULL, CHANGE i_diff i_diff DOUBLE PRECISION DEFAULT NULL, CHANGE fuel_level fuel_level INT DEFAULT NULL, CHANGE water_level water_level INT DEFAULT NULL, CHANGE oil_level oil_level INT DEFAULT NULL, CHANGE air_pressure air_pressure DOUBLE PRECISION DEFAULT NULL, CHANGE oil_pressure oil_pressure DOUBLE PRECISION DEFAULT NULL, CHANGE water_temperature water_temperature DOUBLE PRECISION DEFAULT NULL, CHANGE cooler_temperature cooler_temperature DOUBLE PRECISION DEFAULT NULL, CHANGE engine_speed engine_speed INT DEFAULT NULL, CHANGE batt_voltage batt_voltage DOUBLE PRECISION DEFAULT NULL, CHANGE hours_to_maintenance hours_to_maintenance INT DEFAULT NULL, CHANGE genset_running genset_running INT DEFAULT NULL, CHANGE cg cg INT DEFAULT NULL, CHANGE mains_presence mains_presence INT DEFAULT NULL, CHANGE cr cr INT DEFAULT NULL, CHANGE maintenance_request maintenance_request INT DEFAULT NULL, CHANGE low_fuel low_fuel INT DEFAULT NULL, CHANGE presence_water_in_fuel presence_water_in_fuel INT DEFAULT NULL, CHANGE overspeed overspeed INT DEFAULT NULL, CHANGE max_freq max_freq INT DEFAULT NULL, CHANGE min_freq min_freq INT DEFAULT NULL, CHANGE max_volt max_volt INT DEFAULT NULL, CHANGE min_volt min_volt INT DEFAULT NULL, CHANGE max_batt_volt max_batt_volt INT DEFAULT NULL, CHANGE min_batt_volt min_batt_volt INT DEFAULT NULL, CHANGE overload overload INT DEFAULT NULL, CHANGE short_circuit short_circuit INT DEFAULT NULL, CHANGE mains_inc_seq mains_inc_seq INT DEFAULT NULL, CHANGE genset_inc_seq genset_inc_seq INT DEFAULT NULL, CHANGE differential_intervention differential_intervention INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE no_datetime_data CHANGE l12_g l12_g DOUBLE PRECISION NOT NULL, CHANGE l13_g l13_g DOUBLE PRECISION NOT NULL, CHANGE l23_g l23_g DOUBLE PRECISION NOT NULL, CHANGE l1_n l1_n DOUBLE PRECISION NOT NULL, CHANGE l2_n l2_n DOUBLE PRECISION NOT NULL, CHANGE l3_n l3_n DOUBLE PRECISION NOT NULL, CHANGE l12_m l12_m DOUBLE PRECISION NOT NULL, CHANGE l13_m l13_m DOUBLE PRECISION NOT NULL, CHANGE l23_m l23_m DOUBLE PRECISION NOT NULL, CHANGE i1 i1 DOUBLE PRECISION NOT NULL, CHANGE i2 i2 DOUBLE PRECISION NOT NULL, CHANGE i3 i3 DOUBLE PRECISION NOT NULL, CHANGE freq freq DOUBLE PRECISION NOT NULL, CHANGE i_diff i_diff DOUBLE PRECISION NOT NULL, CHANGE fuel_level fuel_level INT NOT NULL, CHANGE water_level water_level INT NOT NULL, CHANGE oil_level oil_level INT NOT NULL, CHANGE air_pressure air_pressure DOUBLE PRECISION NOT NULL, CHANGE oil_pressure oil_pressure DOUBLE PRECISION NOT NULL, CHANGE water_temperature water_temperature DOUBLE PRECISION NOT NULL, CHANGE cooler_temperature cooler_temperature DOUBLE PRECISION NOT NULL, CHANGE engine_speed engine_speed INT NOT NULL, CHANGE batt_voltage batt_voltage DOUBLE PRECISION NOT NULL, CHANGE hours_to_maintenance hours_to_maintenance INT NOT NULL, CHANGE genset_running genset_running INT NOT NULL, CHANGE cg cg INT NOT NULL, CHANGE mains_presence mains_presence INT NOT NULL, CHANGE cr cr INT NOT NULL, CHANGE maintenance_request maintenance_request INT NOT NULL, CHANGE low_fuel low_fuel INT NOT NULL, CHANGE presence_water_in_fuel presence_water_in_fuel INT NOT NULL, CHANGE overspeed overspeed INT NOT NULL, CHANGE max_freq max_freq INT NOT NULL, CHANGE min_freq min_freq INT NOT NULL, CHANGE max_volt max_volt INT NOT NULL, CHANGE min_volt min_volt INT NOT NULL, CHANGE max_batt_volt max_batt_volt INT NOT NULL, CHANGE min_batt_volt min_batt_volt INT NOT NULL, CHANGE overload overload INT NOT NULL, CHANGE short_circuit short_circuit INT NOT NULL, CHANGE mains_inc_seq mains_inc_seq INT NOT NULL, CHANGE genset_inc_seq genset_inc_seq INT NOT NULL, CHANGE differential_intervention differential_intervention INT NOT NULL');
    }
}
