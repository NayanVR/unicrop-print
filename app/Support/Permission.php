<?php

namespace App\Support;

class Permission
{
    public const UPLOAD_DESIGN = 'upload_design';

    public const PRINT_STATION = 'print_station';

    public const CUTTING_STATION = 'cutting_station';

    public const BILLING_LOGS = 'billing_logs';

    public const SYSTEM_SETTINGS = 'system_settings';

    public const ALL = [
        self::UPLOAD_DESIGN => 'Upload Design',
        self::PRINT_STATION => 'Print Station',
        self::CUTTING_STATION => 'Cutting Station',
        self::BILLING_LOGS => 'Billing Logs',
        self::SYSTEM_SETTINGS => 'System Settings',
    ];
}
