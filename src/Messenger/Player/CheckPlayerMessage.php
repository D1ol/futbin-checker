<?php

declare(strict_types=1);

namespace App\Messenger\Player;

class CheckPlayerMessage
{
    public function __construct(
        private int $baseId,
    ) {
    }

    public function getBaseId(): int
    {
        return $this->baseId;
    }

    public function setBaseId(int $baseId): CheckPlayerMessage
    {
        $this->baseId = $baseId;

        return $this;
    }
}
