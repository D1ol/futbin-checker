<?php

declare(strict_types=1);

namespace App\Entity\Concerns\Activable;

use Doctrine\ORM\Mapping as ORM;

trait ActivableEntityTrait
{
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected bool $active = true;

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function activate(): static
    {
        return $this->setActive(true);
    }

    public function deactivate(): static
    {
        return $this->setActive(false);
    }
}
