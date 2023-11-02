<?php
/**
 * Created by PhpStorm.
 * User: yevhenartiukh
 * Date: 02/11/2023
 * Time: 20:17
 */

namespace App\Messenger\Proxy;

use App\Entity\Proxy\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CheckProxyHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    )
    {
    }

    public function __invoke(CheckProxyMessage $message): void
    {
        if ($this->checkSockOpen($message->getIp())) {
            $proxy = new Proxy();
            $proxy
                ->setIp($message->getIp())
                ->setAddedAt(new \DateTimeImmutable());
            $this->entityManager->persist($proxy);
            $this->entityManager->flush();
            $this->logger->notice(sprintf('WORKING IP: %s', $message->getIp()));
        } else {
            $this->logger->error(sprintf('IP NOT WORKING: %s', $message->getIp()));
        }
    }

    private function checkSockOpen(string $ip)
    {
        $splited = explode(':', $ip);

        if (count($splited) !== 2) {
            return false;
        }

        return @fsockopen($splited[0], $splited[1], $eroare, $eroare_str, 3);
    }
}