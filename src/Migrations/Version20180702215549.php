<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180702215549 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE body_fat (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, measurement DOUBLE PRECISION NOT NULL, date_time DATETIME NOT NULL, part_of_day enum(\'morning\', \'afternoon\', \'evening\', \'night\'), descriptive_statistic enum(\'average\', \'maximum\', \'minimum\', \'standard deviation\', \'variance\', \'sum\', \'median\'), INDEX IDX_190FCCF46B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE body_fat ADD CONSTRAINT FK_190FCCF46B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE body_weight CHANGE unit unit enum(\'fg\', \'pg\', \'ng\', \'ug\', \'mg\', \'g\', \'kg\', \'Metric Ton\', \'gr\', \'oz\', \'lb\', \'Ton\'), CHANGE part_of_day part_of_day enum(\'morning\', \'afternoon\', \'evening\', \'night\'), CHANGE descriptive_statistic descriptive_statistic enum(\'average\', \'maximum\', \'minimum\', \'standard deviation\', \'variance\', \'sum\', \'median\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE body_fat');
        $this->addSql('ALTER TABLE body_weight CHANGE unit unit VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE part_of_day part_of_day VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE descriptive_statistic descriptive_statistic VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
