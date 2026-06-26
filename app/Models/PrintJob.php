<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'uploaded_by', 'note', 'file_path', 'file_name', 'size_id', 'rate', 'sheets',
    'print_total', 'cutting_jobs', 'cutting_rate', 'cutting_total', 'total_amount',
    'status', 'printed_at', 'cut_at',
])]
class PrintJob extends Model
{
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'print_total' => 'decimal:2',
            'cutting_rate' => 'decimal:2',
            'cutting_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'status' => JobStatus::class,
            'printed_at' => 'datetime',
            'cut_at' => 'datetime',
        ];
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? Storage::disk('s3')->url($this->file_path) : null;
    }
}
