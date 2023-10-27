<?php

declare(strict_types=1);

namespace App\Model\Player\Prices;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Price
{
    #[SerializedName('LCPrice')]
    private string $firstPrice;

    #[SerializedName('LCPrice2')]
    private string $secondPrice;

    #[SerializedName('LCPrice3')]
    private string $thirdPrice;

    #[SerializedName('LCPrice4')]
    private string $fourthPrice;

    #[SerializedName('LCPrice5')]
    private string $fifthPrice;

    public function getFirstPrice(): string
    {
        return $this->firstPrice;
    }

    public function getFirstPriceFloat(): float
    {
        return (int) $this->firstPrice * 1000;
    }

    public function setFirstPrice(string $firstPrice): Price
    {
        $this->firstPrice = $firstPrice;

        return $this;
    }

    public function getSecondPrice(): string
    {
        return $this->secondPrice;
    }

    public function setSecondPrice(string $secondPrice): Price
    {
        $this->secondPrice = $secondPrice;

        return $this;
    }

    public function getThirdPrice(): string
    {
        return $this->thirdPrice;
    }

    public function setThirdPrice(string $thirdPrice): Price
    {
        $this->thirdPrice = $thirdPrice;

        return $this;
    }

    public function getFourthPrice(): string
    {
        return $this->fourthPrice;
    }

    public function setFourthPrice(string $fourthPrice): Price
    {
        $this->fourthPrice = $fourthPrice;

        return $this;
    }

    public function getFifthPrice(): string
    {
        return $this->fifthPrice;
    }

    public function setFifthPrice(string $fifthPrice): Price
    {
        $this->fifthPrice = $fifthPrice;

        return $this;
    }
}
