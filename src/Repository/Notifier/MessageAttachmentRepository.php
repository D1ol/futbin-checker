<?php

declare(strict_types=1);

namespace App\Repository\Notifier;

use App\Entity\Notifier\MessageAttachment;
use Doctrine\Persistence\ManagerRegistry;
use XOne\Bundle\NotifierBundle\Repository\MessageRepository as BaseMessageRepository;

/**
 * @method MessageAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageAttachment[]    findAll()
 * @method MessageAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageAttachmentRepository extends BaseMessageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageAttachment::class);
    }
}
