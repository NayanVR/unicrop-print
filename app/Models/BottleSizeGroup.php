<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class BottleSizeGroup extends Model
{
    public function bottleSizes(): HasMany
    {
        return $this->hasMany(BottleSize::class, 'group_id')->orderBy('name');
    }
}
