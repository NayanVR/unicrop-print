<?php

namespace App\Enums;

enum JobStatus: string
{
    case Pending = 'pending';
    case Cutting = 'cutting';
    case Dispatch = 'dispatch';
    case Completed = 'completed';
}
