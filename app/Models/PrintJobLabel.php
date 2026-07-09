<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['print_job_id', 'label_name', 'pcs_per_sheet'])]
class PrintJobLabel extends Model
{
    public function printJob(): BelongsTo
    {
        return $this->belongsTo(PrintJob::class);
    }
}
