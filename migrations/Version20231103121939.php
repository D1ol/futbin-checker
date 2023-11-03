<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103121939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds registration and password reset message templates';
    }

    public function up(Schema $schema): void
    {
        $this->createMessageContext(
            symbol: 'profit_card',
            name: 'Profit on card',
            variables: ['name', 'profit'],
            channels: ['chat'],
        );


        $this->createMessageTemplate(
            name: 'Profit on card',
            subject: $this->getProfitMessageTemplateContent(),
            content: $this->getProfitMessageTemplateContent(),
            messageContextSymbol: 'profit_card',
            variables: ['name', 'profit'],
            channels: ['chat'],
        );
    }

    public function down(Schema $schema): void
    {
        $this->removeMessageContextWithTemplatesBySymbol('profit_card');
    }

    private function getProfitMessageTemplateContent(): string
    {
        return <<<STRING
            Profit on player: {{name}}. Expected {{profit}} coins
        STRING;
    }


    private function createMessageContext(
        string $symbol,
        string $name,
        array $variables = [],
        array $channels = [],
        bool $editable = true,
        bool $removable = true
    ): void {
        $variables = json_encode($variables);
        $channels = json_encode($channels);

        $this->addSql(<<<SQL
            INSERT INTO notifier_message_context (id, symbol, name, variables, channels, editable, removable, created_at)
            VALUES (nextval('notifier_message_context_id_seq'), :symbol, :name, :variables, :channels, :editable, :removable, now())
        SQL, [
            'symbol' => $symbol,
            'name' => $name,
            'variables' => $variables,
            'channels' => $channels,
            'editable' => $editable,
            'removable' => $removable,
        ], [
            'editable' => ParameterType::BOOLEAN,
            'removable' => ParameterType::BOOLEAN,
        ]);
    }

    private function createMessageTemplate(
        string $name,
        string $subject,
        string $content = null,
        string $messageContextSymbol = null,
        array $variables = [],
        array $channels = [],
        bool $editable = true,
        bool $removable = true
    ): void {
        $variables = json_encode($variables);
        $channels = json_encode($channels);

        $this->addSql(<<<SQL
            INSERT INTO notifier_message_template (id, message_context_id, name, subject, content, variables, channels, editable, removable, created_at)
            VALUES (
                nextval('notifier_message_template_id_seq'),
                (SELECT id FROM notifier_message_context WHERE symbol = :message_context_symbol),
                :name,
                :subject,
                :content,
                :variables,
                :channels,
                :editable,
                :removable,
                now()
            )
        SQL, [
            'message_context_symbol' => $messageContextSymbol,
            'name' => $name,
            'subject' => $subject,
            'content' => $content,
            'variables' => $variables,
            'channels' => $channels,
            'editable' => $editable,
            'removable' => $removable,
        ], [
            'editable' => ParameterType::BOOLEAN,
            'removable' => ParameterType::BOOLEAN,
        ]);
    }

    private function removeMessageContextWithTemplatesBySymbol(string $symbol): void
    {
        $this->addSql('DELETE FROM notifier_message_template WHERE message_context_id = (SELECT id FROM notifier_message_context WHERE symbol = :symbol)', [
            'symbol' => $symbol,
        ]);

        $this->addSql('DELETE FROM notifier_message_context WHERE symbol = :symbol', [
            'symbol' => $symbol,
        ]);
    }
}
