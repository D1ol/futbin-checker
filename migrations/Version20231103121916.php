<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103121916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE notifier_message_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notifier_message_attachment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notifier_message_context_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notifier_message_template_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notifier_message_transaction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notifier_message (id INT NOT NULL, subject TEXT DEFAULT NULL, content TEXT DEFAULT NULL, channel VARCHAR(255) NOT NULL, transport VARCHAR(255) DEFAULT NULL, "from" JSON NOT NULL, "to" JSON NOT NULL, cc JSON NOT NULL, bcc JSON NOT NULL, reply_to JSON NOT NULL, priority INT DEFAULT NULL, options JSON NOT NULL, recipient_id VARCHAR(255) DEFAULT NULL, sender_address VARCHAR(255) DEFAULT NULL, sender_name VARCHAR(255) DEFAULT NULL, return_path_address VARCHAR(255) DEFAULT NULL, return_path_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN notifier_message."from" IS \'(DC2Type:x_one_notifier_address_json)\'');
        $this->addSql('COMMENT ON COLUMN notifier_message."to" IS \'(DC2Type:x_one_notifier_address_json)\'');
        $this->addSql('COMMENT ON COLUMN notifier_message.cc IS \'(DC2Type:x_one_notifier_address_json)\'');
        $this->addSql('COMMENT ON COLUMN notifier_message.bcc IS \'(DC2Type:x_one_notifier_address_json)\'');
        $this->addSql('COMMENT ON COLUMN notifier_message.reply_to IS \'(DC2Type:x_one_notifier_address_json)\'');
        $this->addSql('CREATE TABLE notifier_message_attachment (id INT NOT NULL, message_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, content_type VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BE9D6D84537A1329 ON notifier_message_attachment (message_id)');
        $this->addSql('COMMENT ON COLUMN notifier_message_attachment.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN notifier_message_attachment.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE notifier_message_context (id INT NOT NULL, symbol VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, variables JSON NOT NULL, channels JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, active BOOLEAN DEFAULT true NOT NULL, editable BOOLEAN DEFAULT true NOT NULL, removable BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_48B66A44ECC836F9 ON notifier_message_context (symbol)');
        $this->addSql('COMMENT ON COLUMN notifier_message_context.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN notifier_message_context.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE notifier_message_template (id INT NOT NULL, message_context_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, subject TEXT DEFAULT NULL, content TEXT DEFAULT NULL, variables JSON NOT NULL, channels JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, active BOOLEAN DEFAULT true NOT NULL, editable BOOLEAN DEFAULT true NOT NULL, removable BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_51C62DDEA4D0ED50 ON notifier_message_template (message_context_id)');
        $this->addSql('COMMENT ON COLUMN notifier_message_template.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN notifier_message_template.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE notifier_message_transaction (id INT NOT NULL, message_id INT DEFAULT NULL, successful BOOLEAN NOT NULL, transport_message_id VARCHAR(255) DEFAULT NULL, exception TEXT DEFAULT NULL, debug TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C496EA58537A1329 ON notifier_message_transaction (message_id)');
        $this->addSql('COMMENT ON COLUMN notifier_message_transaction.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN notifier_message_transaction.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE notifier_message_attachment ADD CONSTRAINT FK_BE9D6D84537A1329 FOREIGN KEY (message_id) REFERENCES notifier_message (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notifier_message_template ADD CONSTRAINT FK_51C62DDEA4D0ED50 FOREIGN KEY (message_context_id) REFERENCES notifier_message_context (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notifier_message_transaction ADD CONSTRAINT FK_C496EA58537A1329 FOREIGN KEY (message_id) REFERENCES notifier_message (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE notifier_message_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notifier_message_attachment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notifier_message_context_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notifier_message_template_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notifier_message_transaction_id_seq CASCADE');
        $this->addSql('ALTER TABLE notifier_message_attachment DROP CONSTRAINT FK_BE9D6D84537A1329');
        $this->addSql('ALTER TABLE notifier_message_template DROP CONSTRAINT FK_51C62DDEA4D0ED50');
        $this->addSql('ALTER TABLE notifier_message_transaction DROP CONSTRAINT FK_C496EA58537A1329');
        $this->addSql('DROP TABLE notifier_message');
        $this->addSql('DROP TABLE notifier_message_attachment');
        $this->addSql('DROP TABLE notifier_message_context');
        $this->addSql('DROP TABLE notifier_message_template');
        $this->addSql('DROP TABLE notifier_message_transaction');
    }
}
