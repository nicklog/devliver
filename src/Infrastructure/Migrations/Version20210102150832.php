<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210102150832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password field to client';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
                ALTER TABLE client 
                    ADD password VARCHAR(255) NOT NULL AFTER token,
                    ALGORITHM=INPLACE, LOCK=NONE;
            SQL
        );
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
