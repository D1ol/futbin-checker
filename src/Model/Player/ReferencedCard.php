<?php

declare(strict_types=1);

namespace App\Model\Player;

use App\Model\Player\Prices\Prices;

class ReferencedCard
{
    private Prices $prices;

    public function getPrices(): Prices
    {
        return $this->prices;
    }

    public function setPrices(Prices $prices): ReferencedCard
    {
        $this->prices = $prices;

        return $this;
    }
}
