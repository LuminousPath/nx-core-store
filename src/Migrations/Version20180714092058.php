<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180714092058 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE life_tracker ADD scoring INT DEFAULT NULL');
        $this->addSql('ALTER TABLE life_tracker ADD CONSTRAINT FK_A10EB3C7B76A2CA FOREIGN KEY (scoring) REFERENCES life_tracker_score (id)');
        $this->addSql('CREATE INDEX IDX_A10EB3C7B76A2CA ON life_tracker (scoring)');
        $this->addSql('ALTER TABLE life_tracker_score DROP FOREIGN KEY FK_C993389CA10EB3C');
        $this->addSql('DROP INDEX IDX_C993389CA10EB3C ON life_tracker_score');
        $this->addSql('ALTER TABLE life_tracker_score DROP life_tracker');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE life_tracker DROP FOREIGN KEY FK_A10EB3C7B76A2CA');
        $this->addSql('DROP INDEX IDX_A10EB3C7B76A2CA ON life_tracker');
        $this->addSql('ALTER TABLE life_tracker DROP scoring');
        $this->addSql('ALTER TABLE life_tracker_score ADD life_tracker INT DEFAULT NULL');
        $this->addSql('ALTER TABLE life_tracker_score ADD CONSTRAINT FK_C993389CA10EB3C FOREIGN KEY (life_tracker) REFERENCES life_tracker (id)');
        $this->addSql('CREATE INDEX IDX_C993389CA10EB3C ON life_tracker_score (life_tracker)');
    }
}
