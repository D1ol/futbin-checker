<?php

declare(strict_types=1);

namespace App\Entity\Concerns\Removable;

use Doctrine\ORM\Mapping as ORM;

trait RemovableEntityTrait
{
    #[ORM\Column(options: ['default' => true])]
    private bool $removable = true;

    public function isRemovable(): bool
    {
        return $this->removable;
    }

    public function setRemovable(bool $removable): static
    {
        $this->removable = $removable;

        return $this;
    }
}
