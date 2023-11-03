<?php

declare(strict_types=1);

namespace App\Entity\Notifier;

use App\Entity\Concerns\Timestampable\TimestampableEntityTrait;
use App\Repository\Notifier\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use XOne\Bundle\NotifierBundle\Entity\Message as BaseMessage;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'notifier_message')]
class Message extends BaseMessage
{
    public function getOptions(): MessageOptionsInterface
    {
        if('telegram' === $this->transport)
        {
            return new TelegramOptions($this->options);
        }
        return parent::getOptions();
    }
}
