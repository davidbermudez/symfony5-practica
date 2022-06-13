<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220613154200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin DROP FOREIGN KEY admin_ibfk_1');
        $this->addSql('ALTER TABLE admin ADD CONSTRAINT FK_880E0D769C833003 FOREIGN KEY (grupo_id) REFERENCES grupo (id)');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD99C833003');
        $this->addSql('ALTER TABLE driver ADD grupo_id INT NOT NULL');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD99C833003 FOREIGN KEY (grupo_id) REFERENCES grupo (id)');
        $this->addSql('CREATE INDEX IDX_11667CD99C833003 ON driver (grupo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin DROP FOREIGN KEY FK_880E0D769C833003');
        $this->addSql('ALTER TABLE admin ADD CONSTRAINT admin_ibfk_1 FOREIGN KEY (grupo_id) REFERENCES grupo (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD99C833003');
        $this->addSql('DROP INDEX IDX_11667CD99C833003 ON driver');
        $this->addSql('ALTER TABLE driver DROP grupo_id');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD99C833003 FOREIGN KEY (id) REFERENCES grupo (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}
