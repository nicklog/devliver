<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201226154212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add roles to user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
                ALTER TABLE user 
                    ADD roles LONGTEXT NOT NULL COMMENT '(DC2Type:json)',
                    ALGORITHM=INPLACE, LOCK=NONE;
            SQL
        );
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
