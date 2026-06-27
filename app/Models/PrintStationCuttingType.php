<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['print_station_id', 'cutting_type_id', 'rate'])]
class PrintStationCuttingType extends Model
{
    protected $table = 'print_station_cutting_type';

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

    public function cuttingType(): BelongsTo
    {
        return $this->belongsTo(CuttingType::class);
    }
}
