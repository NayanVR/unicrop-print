<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['print_station_id', 'size_id', 'rate'])]
class PrintStationSize extends Model
{
    protected $table = 'print_station_size';

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
        ];
    }

    public function printStation(): BelongsTo
    {
        return $this->belongsTo(PrintStation::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }
}
