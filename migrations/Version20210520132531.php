<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210520132531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE alarm_status');
        $this->addSql('ALTER TABLE no_datetime_data ADD genset_running INT NOT NULL, ADD cg INT NOT NULL, ADD mains_presence INT NOT NULL, ADD cr INT NOT NULL, ADD maintenance_request INT NOT NULL, ADD low_fuel INT NOT NULL, ADD presence_water_in_fuel INT NOT NULL, ADD overspeed INT NOT NULL, ADD max_freq INT NOT NULL, ADD min_freq INT NOT NULL, ADD max_volt INT NOT NULL, ADD min_volt INT NOT NULL, ADD max_batt_volt INT NOT NULL, ADD min_batt_volt INT NOT NULL, ADD overload INT NOT NULL, ADD short_circuit INT NOT NULL, ADD mains_inc_seq INT NOT NULL, ADD genset_inc_seq INT NOT NULL, ADD differential_intervention INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alarm_status (id INT AUTO_INCREMENT NOT NULL, smart_mod_id INT NOT NULL, genset_running INT NOT NULL, cg INT NOT NULL, mains_presence INT NOT NULL, cr INT NOT NULL, maintenance_request INT NOT NULL, low_fuel INT NOT NULL, presence_water_in_fuel INT NOT NULL, overspeed INT NOT NULL, max_freq INT NOT NULL, min_freq INT NOT NULL, max_volt INT NOT NULL, min_volt INT NOT NULL, max_batt_volt INT NOT NULL, min_batt_volt INT NOT NULL, overload INT NOT NULL, short_circuit INT NOT NULL, mains_inc_seq INT NOT NULL, genset_inc_seq INT NOT NULL, differential_intervention INT NOT NULL, UNIQUE INDEX UNIQ_262D8E612CFA4C13 (smart_mod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE alarm_status ADD CONSTRAINT FK_262D8E612CFA4C13 FOREIGN KEY (smart_mod_id) REFERENCES smart_mod (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE no_datetime_data DROP genset_running, DROP cg, DROP mains_presence, DROP cr, DROP maintenance_request, DROP low_fuel, DROP presence_water_in_fuel, DROP overspeed, DROP max_freq, DROP min_freq, DROP max_volt, DROP min_volt, DROP max_batt_volt, DROP min_batt_volt, DROP overload, DROP short_circuit, DROP mains_inc_seq, DROP genset_inc_seq, DROP differential_intervention');
    }
}
