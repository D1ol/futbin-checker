<?php

declare(strict_types=1);

namespace App\Notifier\Factory;

use App\Entity\Notifier\MessageContext;
use App\Repository\Notifier\MessageTemplateRepository;
use XOne\Bundle\NotifierBundle\Factory\MessageFactoryInterface;
use XOne\Bundle\NotifierBundle\Model\MessageInterface;

class ProfitMessageFactory
{
    public function __construct(
        private readonly MessageFactoryInterface $messageFactory,
        private readonly MessageTemplateRepository $messageTemplateRepository,
    ) {
    }

    public function createProfitMessage(string $name, float $profit, ?string $transport = null): MessageInterface
    {
        $messageTemplate = $this->messageTemplateRepository->findOneByMessageContextSymbolAndChannel(
            symbol: MessageContext::PROFIT_CARD,
            channel: 'chat',
        );

        if (null === $messageTemplate) {
            throw new \LogicException('There are no message template for registration');
        }

        $chatMessage = $this->messageFactory->createTemplatedChat($messageTemplate, [
            'name' => $name,
            'profit' => $profit
        ]);

        $chatMessage->setTransport($transport);

        return $chatMessage;
    }
}