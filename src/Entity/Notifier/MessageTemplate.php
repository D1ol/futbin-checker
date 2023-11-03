<?php

declare(strict_types=1);

namespace App\Entity\Notifier;

use App\Entity\Concerns\Activable\ActivableEntityInterface;
use App\Entity\Concerns\Activable\ActivableEntityTrait;
use App\Entity\Concerns\Editable\EditableEntityTrait;
use App\Entity\Concerns\Removable\RemovableEntityTrait;
use App\Entity\Concerns\Timestampable\TimestampableEntityTrait;
use App\Repository\Notifier\MessageTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use XOne\Bundle\NotifierBundle\Entity\MessageTemplate as BaseMessageTemplate;

#[ORM\Entity(repositoryClass: MessageTemplateRepository::class)]
#[ORM\Table(name: 'notifier_message_template')]
class MessageTemplate extends BaseMessageTemplate implements ActivableEntityInterface
{
    use TimestampableEntityTrait;
    use ActivableEntityTrait;
    use EditableEntityTrait;
    use RemovableEntityTrait;
}
