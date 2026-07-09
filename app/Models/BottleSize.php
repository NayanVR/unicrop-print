<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['group_id', 'name', 'label_width_mm', 'label_height_mm'])]
class BottleSize extends Model
{
    protected function casts(): array
    {
        return [
            'label_width_mm' => 'decimal:2',
            'label_height_mm' => 'decimal:2',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(BottleSizeGroup::class, 'group_id');
    }
}
