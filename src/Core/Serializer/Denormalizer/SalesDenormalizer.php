<?php

declare(strict_types=1);

namespace App\Core\Serializer\Denormalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SalesDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    public const SALES_RESPONSE = 'sales_response';

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if (true === ($context[self::SALES_RESPONSE] ?? false)) {
            unset($context[self::SALES_RESPONSE]);

            $data = array_slice($data, 0, 10);
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return isset($context[self::SALES_RESPONSE]);
    }
}
