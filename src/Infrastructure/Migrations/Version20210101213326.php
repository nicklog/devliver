<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210101213326 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Make name of client unique';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE client MODIFY token VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404555E237E06 ON client (name)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
