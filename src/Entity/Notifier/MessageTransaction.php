<?php

declare(strict_types=1);

namespace App\Entity\Notifier;

use App\Entity\Concerns\Timestampable\TimestampableEntityTrait;
use App\Repository\Notifier\MessageTransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use XOne\Bundle\NotifierBundle\Entity\MessageTransaction as BaseMessageTransaction;

#[ORM\Entity(repositoryClass: MessageTransactionRepository::class)]
#[ORM\Table(name: 'notifier_message_transaction')]
class MessageTransaction extends BaseMessageTransaction
{
    use TimestampableEntityTrait;
}
