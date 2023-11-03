<?php

declare(strict_types=1);

namespace App\Entity\Notifier;

use App\Entity\Concerns\Activable\ActivableEntityInterface;
use App\Entity\Concerns\Activable\ActivableEntityTrait;
use App\Entity\Concerns\Editable\EditableEntityTrait;
use App\Entity\Concerns\Removable\RemovableEntityTrait;
use App\Entity\Concerns\Timestampable\TimestampableEntityTrait;
use App\Repository\Notifier\MessageContextRepository;
use Doctrine\ORM\Mapping as ORM;
use XOne\Bundle\NotifierBundle\Entity\MessageContext as BaseMessageContext;

#[ORM\Entity(repositoryClass: MessageContextRepository::class)]
#[ORM\Table(name: 'notifier_message_context')]
class MessageContext extends BaseMessageContext implements ActivableEntityInterface
{
    use TimestampableEntityTrait;
    use ActivableEntityTrait;
    use EditableEntityTrait;
    use RemovableEntityTrait;

    public const PROFIT_CARD = 'profit_card';
}
