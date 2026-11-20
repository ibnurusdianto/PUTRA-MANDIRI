<?php

namespace App\Enums;

enum DigitalServiceStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Belum Selesai',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'text-bg-warning',
            self::Completed => 'text-bg-success',
            self::Failed => 'text-bg-danger',
        };
    }

    public function isFinal(): bool
    {
        return $this !== self::Pending;
    }
}
