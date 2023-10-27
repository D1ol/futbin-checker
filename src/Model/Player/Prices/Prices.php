<?php

declare(strict_types=1);

namespace App\Model\Player\Prices;

class Prices
{
    private Price $ps;

    private Price $pc;

    public function getPs(): Price
    {
        return $this->ps;
    }

    public function setPs(Price $ps): Prices
    {
        $this->ps = $ps;

        return $this;
    }

    public function getPc(): Price
    {
        return $this->pc;
    }

    public function setPc(Price $pc): Prices
    {
        $this->pc = $pc;

        return $this;
    }
}
