<?php

namespace App\Enums;

enum ProductType: string
{
    case Physical = 'physical';
    case Pulsa = 'pulsa';
    case Token = 'token';

    public function label(): string
    {
        return match ($this) {
            self::Physical => 'Barang Fisik',
            self::Pulsa => 'Pulsa',
            self::Token => 'Token Listrik',
        };
    }

    public function isPhysical(): bool
    {
        return $this === self::Physical;
    }

    public function isDigital(): bool
    {
        return ! $this->isPhysical();
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
