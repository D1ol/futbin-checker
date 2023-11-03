<?php

declare(strict_types=1);

namespace App\Repository\Notifier;

use App\Entity\Notifier\MessageContext;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use XOne\Bundle\NotifierBundle\Repository\MessageRepository as BaseMessageRepository;

/**
 * @method MessageContext|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageContext|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageContext[]    findAll()
 * @method MessageContext[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageContextRepository extends BaseMessageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageContext::class);
    }

    public function addSearchCriteria(QueryBuilder $queryBuilder, string $search): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $criteria = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like("lower($rootAlias.symbol)", 'lower(:search)'),
            $queryBuilder->expr()->like("lower($rootAlias.name)", 'lower(:search)'),
        );

        $queryBuilder
            ->andWhere($criteria)
            ->setParameter('search', '%'.$search.'%');
    }
}
