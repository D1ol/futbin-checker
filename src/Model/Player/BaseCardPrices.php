<?php

declare(strict_types=1);

namespace App\Model\Player;

use Doctrine\Common\Collections\ArrayCollection;

class BaseCardPrices
{
    private ?ArrayCollection $referencedCards = null;

    /**
     * @param ReferencedCard[] $referencedCards
     */
    public function __construct(array $referencedCards)
    {
        $this->referencedCards = new ArrayCollection($referencedCards);
    }

    public function getReferencedCards(): ?ArrayCollection
    {
        return $this->referencedCards;
    }

    public function getMainCard(): ReferencedCard
    {
        return $this->referencedCards->first();
    }
}
