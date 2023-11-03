<?php

declare(strict_types=1);

namespace App\Entity\Concerns\Editable;

use Doctrine\ORM\Mapping as ORM;

trait EditableEntityTrait
{
    #[ORM\Column(options: ['default' => true])]
    private bool $editable = true;

    public function isEditable(): bool
    {
        return $this->editable;
    }

    public function setEditable(bool $editable): static
    {
        $this->editable = $editable;

        return $this;
    }
}
