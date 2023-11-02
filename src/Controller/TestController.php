<?php

declare(strict_types=1);

namespace vendor;

use App\Entity\Player\Player;
use App\Messenger\Player\CheckPlayerMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TestController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $futbinHttpClient,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/test', 'name')]
    public function test()
    {
        return $this->messageBus->dispatch(new CheckPlayerMessage(23));
    }

    #[Route('/players', name: 'players', methods: ['GET', 'POST'])]
    public function players(): JsonResponse
    {
        $pages = $this->getPages();
        $result = [];

        for ($page = 1; $page <= $pages; ++$page) {
            $request = $this->playersRequest($page);
            $crawler = new Crawler($request->getContent());

            $selector = 'table > tbody > tr';

            $crawler->filter($selector)->each(function (Crawler $node) use (&$result) {
                if (!is_null($node->attr('data-url'))) {
                    $name = $node->filter('.player_name_players_table')->text();
                    $cardId = $node->filter('.player_name_players_table')->attr('data-site-id');
                    $img = $node->filter('.player_img')->attr('data-original');
                    $baseId = strrev(explode('.', explode('/', strrev($img))[0])[1]);

                    $player = new Player();

                    $player
                        ->setName($name)
                        ->setCardId((int) $cardId)
                        ->setBaseId((int) $baseId);

                    $this->entityManager->persist($player);
                }
            });
        }

        $this->entityManager->flush();

        return $this->json($result);
    }

    public function getPages(): int
    {
        $maxPage = 1;
        $request = $this->playersRequest();

        $crawler = new Crawler($request->getContent());
        $selector = 'ul.pagination > li';

        $crawler->filter($selector)->each(function (Crawler $node) use (&$maxPage) {
            $page = (int) $node->filter('.page-item')->text();
            if ($page > $maxPage) {
                $maxPage = $page;
            }
        });

        return $maxPage;
    }

    public function playersRequest(int $page = 1): ResponseInterface
    {
        return $this->futbinHttpClient->request('GET', 'players', ['query' => [
            'version' => 'gold',
            'ps_price' => '5000-15000000',
            'page' => $page,
        ]]);
    }
}
