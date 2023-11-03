<?php

declare(strict_types=1);

namespace App\Repository\Notifier;

use App\Entity\Notifier\Message;
use Doctrine\Persistence\ManagerRegistry;
use XOne\Bundle\NotifierBundle\Repository\MessageRepository as BaseMessageRepository;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends BaseMessageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }
}
