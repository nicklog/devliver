<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201229113210 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create clients table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
            <<<'SQL'
                CREATE TABLE client 
                (
                    id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                    name VARCHAR(255) NOT NULL, 
                    token VARCHAR(255) DEFAULT NULL, 
                    enable TINYINT(1) DEFAULT 1 NOT NULL, 
                    created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    updated DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    UNIQUE INDEX UNIQ_C74404555F37A13B (token), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL
        );
        $this->addSql('DROP INDEX UNIQ_8D93D649F155E556 ON user');
        $this->addSql(
            <<<'SQL'
                ALTER TABLE user 
                    ADD enable TINYINT(1) DEFAULT 1 NOT NULL, 
                    DROP password_reset_token, 
                    DROP password_reset_token_expire_at, 
                    DROP repository_token
            SQL
        );
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
