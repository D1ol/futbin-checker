<?php

declare(strict_types=1);

namespace App\Model\Player\Prices;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

class Price
{
    private const DEFAULT_UPDATE_INTERVAL = 'PT5M';

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

    #[ORM\Column(type: 'string')]
    #[SerializedName('updated')]
    private string $lastUpdate;

    /**
     * @param string $firstPrice
     * @param string $secondPrice
     * @param string $thirdPrice
     * @param string $fourthPrice
     * @param string $fifthPrice
     * @param string $lastUpdate
     */
    public function __construct(
        int    $firstPrice,
        array  $secondPrice,
        string $thirdPrice,
        string $fourthPrice, string $fifthPrice, string $lastUpdate)
    {
        $this->firstPrice = $firstPrice;
        $this->secondPrice = $secondPrice;
        $this->thirdPrice = $thirdPrice;
        $this->fourthPrice = $fourthPrice;
        $this->fifthPrice = $fifthPrice;
        $this->lastUpdate = $lastUpdate;
    }


    public function getFirstPrice(): string
    {
        return $this->firstPrice;
    }

    public function getFirstPriceFloat(): float
    {
        return (int)$this->firstPrice * 1000;
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

    public function getLastUpdate(): string
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(string $lastUpdate): Price
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Last updated date + 5min like default time when card was updated.
     */
    public function getNextCheckDate(\DateInterval $interval = new \DateInterval(self::DEFAULT_UPDATE_INTERVAL)): \DateTimeImmutable
    {
        try {
            $dateStamp = new \DateTimeImmutable($this->lastUpdate);

            return $dateStamp->add($interval);
        } catch (\Throwable $e) {
            return new \DateTimeImmutable();
        }
    }
}
