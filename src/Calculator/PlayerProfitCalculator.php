<?php

declare(strict_types=1);

namespace App\Calculator;

class PlayerProfitCalculator
{
    private const EA_TAX = 0.05;

    private float $averageTax;
    private float $averageWithoutTax;

    public function __construct(
        private float $currentPrice,
        private float $average,
    ) {
        $this->averageTax = $this->average * self::EA_TAX;
        $this->averageWithoutTax = $this->average - $this->averageTax;
    }

    public function getCurrentPrice(): float
    {
        return $this->currentPrice;
    }

    public function getAverage(): float
    {
        return $this->average;
    }

    public function getAverageTax(): float
    {
        return $this->averageTax;
    }

    public function getAverageWithoutTax(): float
    {
        return $this->averageWithoutTax;
    }

    public function isDiscount(): bool
    {
        return $this->averageWithoutTax > $this->currentPrice;
    }

    public function getExpectedProfit(): float
    {
        return $this->averageWithoutTax - $this->currentPrice;
    }
}
