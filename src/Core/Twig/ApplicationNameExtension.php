<?php

declare(strict_types=1);

namespace App\Core\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class ApplicationNameExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private string $applicationName,
        private string $applicationShortName,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'application_name' => $this->applicationName,
            'application_short_name' => $this->applicationShortName,
        ];
    }
}
