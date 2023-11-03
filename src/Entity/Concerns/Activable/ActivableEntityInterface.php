<?php

declare(strict_types=1);

namespace App\Entity\Concerns\Activable;

interface ActivableEntityInterface
{
    public function isActive(): bool;

    public function setActive(bool $active): static;

    public function activate(): static;

    public function deactivate(): static;
}
