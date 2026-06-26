<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Uploader = 'uploader';
    case Printer = 'printer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Uploader => 'Uploader',
            self::Printer => 'Printer',
        };
    }
}
