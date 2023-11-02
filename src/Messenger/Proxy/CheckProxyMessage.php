<?php
/**
 * Created by PhpStorm.
 * User: yevhenartiukh
 * Date: 02/11/2023
 * Time: 20:16
 */

namespace App\Messenger\Proxy;

class CheckProxyMessage
{
    public function __construct(
        private string $ip
    )
    {
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }
}