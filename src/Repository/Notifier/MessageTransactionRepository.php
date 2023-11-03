<?php

declare(strict_types=1);

namespace App\Repository\Notifier;

use App\Entity\Notifier\MessageTransaction;
use Doctrine\Persistence\ManagerRegistry;
use XOne\Bundle\NotifierBundle\Repository\MessageRepository as BaseMessageRepository;

/**
 * @method MessageTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageTransaction[]    findAll()
 * @method MessageTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageTransactionRepository extends BaseMessageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageTransaction::class);
    }
}
