<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180704160255 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE body_bmi (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, measurement DOUBLE PRECISION DEFAULT NULL, date_time DATE DEFAULT NULL, INDEX IDX_D1B547FD6B899279 (patient_id), UNIQUE INDEX DateReading (date_time), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE body_fat (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, unit INT DEFAULT NULL, part_of_day INT DEFAULT NULL, measurement DOUBLE PRECISION DEFAULT NULL, date_time DATE DEFAULT NULL, INDEX IDX_190FCCF46B899279 (patient_id), INDEX IDX_190FCCF4DCBB0C53 (unit), INDEX IDX_190FCCF4EE0DB122 (part_of_day), UNIQUE INDEX DateReading (date_time, patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE body_weight (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, unit INT DEFAULT NULL, part_of_day INT DEFAULT NULL, measurement DOUBLE PRECISION DEFAULT NULL, date_time DATE DEFAULT NULL, INDEX IDX_92D1F6476B899279 (patient_id), INDEX IDX_92D1F647DCBB0C53 (unit), INDEX IDX_92D1F647EE0DB122 (part_of_day), UNIQUE INDEX DateReading (date_time, patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE count_daily_floor (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, date_time DATE DEFAULT NULL, value INT DEFAULT NULL, goal INT DEFAULT NULL, INDEX IDX_4DE77C7D6B899279 (patient_id), UNIQUE INDEX DateReading (patient_id, date_time), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE count_daily_step (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, date_time DATE DEFAULT NULL, value INT DEFAULT NULL, goal INT DEFAULT NULL, INDEX IDX_5862E52E6B899279 (patient_id), UNIQUE INDEX DateReading (patient_id, date_time), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE part_of_day (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(9) NOT NULL, UNIQUE INDEX UNIQ_EE0DB1225E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, fname VARCHAR(255) DEFAULT NULL, lname VARCHAR(255) DEFAULT NULL, birthday DATETIME DEFAULT NULL, height INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, gender VARCHAR(6) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_relations (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, username VARCHAR(30) DEFAULT NULL, INDEX IDX_23BD673D6B899279 (patient_id), UNIQUE INDEX RemoteUserName (patient_id, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unit_of_measurement (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_DC68A6045E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE body_bmi ADD CONSTRAINT FK_D1B547FD6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE body_fat ADD CONSTRAINT FK_190FCCF46B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE body_fat ADD CONSTRAINT FK_190FCCF4DCBB0C53 FOREIGN KEY (unit) REFERENCES unit_of_measurement (id)');
        $this->addSql('ALTER TABLE body_fat ADD CONSTRAINT FK_190FCCF4EE0DB122 FOREIGN KEY (part_of_day) REFERENCES part_of_day (id)');
        $this->addSql('ALTER TABLE body_weight ADD CONSTRAINT FK_92D1F6476B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE body_weight ADD CONSTRAINT FK_92D1F647DCBB0C53 FOREIGN KEY (unit) REFERENCES unit_of_measurement (id)');
        $this->addSql('ALTER TABLE body_weight ADD CONSTRAINT FK_92D1F647EE0DB122 FOREIGN KEY (part_of_day) REFERENCES part_of_day (id)');
        $this->addSql('ALTER TABLE count_daily_floor ADD CONSTRAINT FK_4DE77C7D6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE count_daily_step ADD CONSTRAINT FK_5862E52E6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE third_party_relations ADD CONSTRAINT FK_23BD673D6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');

        $this->addSql("INSERT INTO part_of_day (name) VALUES ('morning')");
        $this->addSql("INSERT INTO part_of_day (name) VALUES ('afternoon')");
        $this->addSql("INSERT INTO part_of_day (name) VALUES ('evening')");
        $this->addSql("INSERT INTO part_of_day (name) VALUES ('night')");

        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('fg')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('pg')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('ng')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('ug')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('mg')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('g')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('kg')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('Metric Ton')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('gr')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('oz')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('lb')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('Ton')");
        $this->addSql("INSERT INTO unit_of_measurement (name) VALUES ('%')");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE body_fat DROP FOREIGN KEY FK_190FCCF4EE0DB122');
        $this->addSql('ALTER TABLE body_weight DROP FOREIGN KEY FK_92D1F647EE0DB122');
        $this->addSql('ALTER TABLE body_bmi DROP FOREIGN KEY FK_D1B547FD6B899279');
        $this->addSql('ALTER TABLE body_fat DROP FOREIGN KEY FK_190FCCF46B899279');
        $this->addSql('ALTER TABLE body_weight DROP FOREIGN KEY FK_92D1F6476B899279');
        $this->addSql('ALTER TABLE count_daily_floor DROP FOREIGN KEY FK_4DE77C7D6B899279');
        $this->addSql('ALTER TABLE count_daily_step DROP FOREIGN KEY FK_5862E52E6B899279');
        $this->addSql('ALTER TABLE third_party_relations DROP FOREIGN KEY FK_23BD673D6B899279');
        $this->addSql('ALTER TABLE body_fat DROP FOREIGN KEY FK_190FCCF4DCBB0C53');
        $this->addSql('ALTER TABLE body_weight DROP FOREIGN KEY FK_92D1F647DCBB0C53');
        $this->addSql('DROP TABLE body_bmi');
        $this->addSql('DROP TABLE body_fat');
        $this->addSql('DROP TABLE body_weight');
        $this->addSql('DROP TABLE count_daily_floor');
        $this->addSql('DROP TABLE count_daily_step');
        $this->addSql('DROP TABLE part_of_day');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE third_party_relations');
        $this->addSql('DROP TABLE unit_of_measurement');
    }
}
