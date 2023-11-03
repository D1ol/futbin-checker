<?php

declare(strict_types=1);

namespace App\Model\Player\Sales;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Sale
{
    #[SerializedName('Price')]
    private float $price;

    #[SerializedName('BIN')]
    private int $BIN;

    #[SerializedName('status')]
    private string $status;

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): Sale
    {
        $this->price = $price;
        return $this;
    }

    public function getBIN(): int
    {
        return $this->BIN;
    }

    public function setBIN(int $BIN): Sale
    {
        $this->BIN = $BIN;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): Sale
    {
        $this->status = $status;

        return $this;
    }

    public function isClosed(): bool
    {
        return 'closed' === $this->status;
    }
}
