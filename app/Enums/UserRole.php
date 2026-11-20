<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Kasir = 'kasir';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Kasir => 'Kasir',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}
