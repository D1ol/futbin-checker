<?php

declare(strict_types=1);

namespace App\Command;

use App\Messenger\Player\CheckPlayerMessage;
use App\Repository\Player\PlayerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:players:player:price',
)]
class CheckPlayerPriceCommand extends Command
{

    public function __construct(
        private PlayerRepository $playerRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $players = $this->playerRepository->findAll();

        $this->messageBus->dispatch(new CheckPlayerMessage($players[0]->getBaseId()));

        return Command::SUCCESS;
    }
}
