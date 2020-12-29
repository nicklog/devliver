<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201225173740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Setup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
                CREATE TABLE package 
                (
                    id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                    type VARCHAR(255) DEFAULT 'vcs' NOT NULL, 
                    url VARCHAR(255) NOT NULL, 
                    name VARCHAR(255) NOT NULL, 
                    enable TINYINT(1) DEFAULT 1 NOT NULL, 
                    abandoned TINYINT(1) DEFAULT 0 NOT NULL, 
                    replacement_package VARCHAR(255) DEFAULT NULL, 
                    readme LONGTEXT DEFAULT NULL, 
                    auto_update TINYINT(1) DEFAULT 0 NOT NULL, 
                    last_update DATETIME NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    updated DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE update_queue 
                (
                    id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                    package_id INT UNSIGNED NOT NULL, 
                    locked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    last_called_at DATETIME NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    updated DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    INDEX IDX_1F999DE1F44CABFF (package_id),
                    PRIMARY KEY(id),
                    CONSTRAINT FK_1F999DE1F44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE user 
                (
                    id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                    email VARCHAR(255) NOT NULL, 
                    password VARCHAR(255) DEFAULT NULL, 
                    password_reset_token VARCHAR(255) DEFAULT NULL, 
                    password_reset_token_expire_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    repository_token VARCHAR(255) DEFAULT NULL, 
                    created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    updated DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    UNIQUE INDEX UNIQ_8D93D649F155E556 (repository_token), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE version 
                (
                    id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                    package_id INT UNSIGNED NOT NULL, 
                    name VARCHAR(255) NOT NULL, 
                    data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                    created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    updated DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    INDEX IDX_BF1CD3C3F44CABFF (package_id), 
                    INDEX IDX_BF1CD3C3F44CABFF5E237E06 (package_id, name), 
                    PRIMARY KEY(id),
                    CONSTRAINT FK_BF1CD3C3F44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE download 
                (
                    id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                    package_id INT UNSIGNED NOT NULL, 
                    version_id INT UNSIGNED DEFAULT NULL, 
                    version_name VARCHAR(255) DEFAULT NULL, 
                    created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    updated DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    INDEX IDX_781A8270F44CABFF (package_id), 
                    INDEX IDX_781A82704BBC2705 (version_id), 
                    PRIMARY KEY(id),
                    CONSTRAINT FK_781A8270F44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE,
                    CONSTRAINT FK_781A82704BBC2705 FOREIGN KEY (version_id) REFERENCES version (id) ON DELETE SET NULL
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE messenger_messages 
                (
                    id BIGINT AUTO_INCREMENT NOT NULL, 
                    body LONGTEXT NOT NULL, 
                    headers LONGTEXT NOT NULL, 
                    queue_name VARCHAR(190) NOT NULL, 
                    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    available_at DATETIME NOT NULL COMMENT '(DC2Type:datetimeutc)', 
                    delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimeutc)', 
                    INDEX IDX_75EA56E0FB7336F0 (queue_name), 
                    INDEX IDX_75EA56E0E3BD61CE (available_at), 
                    INDEX IDX_75EA56E016BA31DB (delivered_at), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL
        );
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
