<?php

declare(strict_types=1);

namespace App\Model\Player;

use App\Model\Player\Sales\Sale;
use Doctrine\Common\Collections\ArrayCollection;

class BaseCardSales
{
    private ?ArrayCollection $sales = null;

    /**
     * @param Sale[] $sales
     */
    public function __construct(array $sales)
    {
        $this->sales = new ArrayCollection($sales);
    }

    public function getSales(): ?ArrayCollection
    {
        return $this->sales;
    }

    public function getAverage(): float
    {
        $filtered = $this->sales->filter(function (Sale $value) {
            return $value->isClosed();
        });

        $prices = $filtered->map(function (Sale $value) {
            return $value->getPrice();
        });

        return array_sum($prices->toArray()) / $prices->count();
    }
}
