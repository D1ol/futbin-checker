<?php

declare(strict_types=1);

namespace App\Repository\Notifier;

use App\Entity\Notifier\MessageTemplate;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use XOne\Bundle\NotifierBundle\Repository\MessageRepository as BaseMessageRepository;

/**
 * @method MessageTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageTemplate[]    findAll()
 * @method MessageTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageTemplateRepository extends BaseMessageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageTemplate::class);
    }

    public function findOneByMessageContextSymbolAndChannel(string $symbol, string $channel): ?MessageTemplate
    {
        return $this->createQueryBuilder('messageTemplate')
            ->innerJoin('messageTemplate.messageContext', 'messageContext')
            ->where('messageContext.symbol = :symbol')
            ->andWhere('JSONB_EXISTS(CAST(messageTemplate.channels AS jsonb), :channel) = true')
            ->setParameters([
                'symbol' => $symbol,
                'channel' => $channel,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('messageTemplate')
            ->addSelect('messageContext')
            ->innerJoin('messageTemplate.messageContext', 'messageContext')
        ;
    }

    public function addSearchCriteria(QueryBuilder $queryBuilder, string $search): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $criteria = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like("lower($rootAlias.name)", 'lower(:search)'),
            $queryBuilder->expr()->like("lower($rootAlias.subject)", 'lower(:search)'),
            $queryBuilder->expr()->like("lower($rootAlias.content)", 'lower(:search)'),
        );

        if (null !== $messageContextAlias = $this->getJoinedMessageContextAlias($queryBuilder)) {
            $criteria->add($queryBuilder->expr()->like("lower($messageContextAlias.name)", 'lower(:search)'));
        }

        $queryBuilder
            ->andWhere($criteria)
            ->setParameter('search', '%'.$search.'%');
    }

    private function getJoinedMessageContextAlias(QueryBuilder $queryBuilder): ?string
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        /** @var iterable<Join> $rootJoins */
        $rootJoins = $queryBuilder->getDQLPart('join')[$rootAlias] ?? [];

        foreach ($rootJoins as $join) {
            if ($join->getJoin() === "$rootAlias.message_context") {
                return $join->getAlias();
            }
        }

        return null;
    }
}
