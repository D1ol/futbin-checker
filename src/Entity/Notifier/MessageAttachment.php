<?php

declare(strict_types=1);

namespace App\Entity\Notifier;

use App\Entity\Concerns\Timestampable\TimestampableEntityTrait;
use App\Repository\Notifier\MessageAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;
use XOne\Bundle\NotifierBundle\Entity\MessageAttachment as BaseMessageAttachment;

#[ORM\Entity(repositoryClass: MessageAttachmentRepository::class)]
#[ORM\Table(name: 'notifier_message_attachment')]
class MessageAttachment extends BaseMessageAttachment
{
    use TimestampableEntityTrait;
}
