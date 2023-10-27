<?php

declare(strict_types=1);

namespace App\Core\Messenger;

use Symfony\Component\Translation\TranslatableMessage;

class HandlerResult
{
    public const SUCCESS = 'success';
    public const ERROR = 'error';

    public function __construct(
        private null|string|TranslatableMessage $message,
        private bool $success = true,
        private mixed $data = null,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string|TranslatableMessage|null
    {
        return $this->message;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
