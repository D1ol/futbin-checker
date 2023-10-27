<?php

declare(strict_types=1);

namespace App\Command;

use App\Core\Messenger\HandlerResult;
use App\Messenger\Player\CheckPlayerMessage;
use App\Repository\Player\PlayerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:players:single-check',
)]
class SingleCheckPlayersCommand extends Command
{

    public function __construct(
        private PlayerRepository $playerRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $players = $this->playerRepository->findAll();

        foreach ($players as $player) {
            $this->messageBus->dispatch(new CheckPlayerMessage($player->getBaseId()));
            /* @var HandlerResult $result */
        }

        return Command::SUCCESS;
    }
}
