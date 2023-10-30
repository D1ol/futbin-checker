<?php

declare(strict_types=1);

namespace App\Scheduler;

use App\Messenger\Player\CheckPlayerMessage;
use App\Repository\Player\PlayerRepository;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
class DefaultScheduleProvider implements ScheduleProviderInterface
{
    public function __construct(
        private PlayerRepository $playerRepository,
    ) {
    }

    public function getSchedule(): Schedule
    {
        $players = $this->playerRepository->findAll();

        $message = new CheckPlayerMessage($players[0]->getBaseId());

        return (new Schedule())->add(
            RecurringMessage::every('10 seconds', $message),
        );
    }
}
