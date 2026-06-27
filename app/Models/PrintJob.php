<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

#[Fillable([
    'uploaded_by', 'print_station_id', 'note', 'file_path', 'file_name', 'file_size', 'mime_type',
    'size_id', 'rate', 'sheets', 'print_total', 'cutting_jobs', 'cutting_rate', 'cutting_total',
    'total_amount', 'status', 'printed_at', 'cut_at',
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

    public function printStation(): BelongsTo
    {
        return $this->belongsTo(PrintStation::class);
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? route('jobs.file', $this) : null;
    }

    public function downloadUrl(): ?string
    {
        return $this->file_path ? route('jobs.file', ['printJob' => $this, 'download' => 1]) : null;
    }

    public function publicShareUrl(): ?string
    {
        return $this->file_path ? URL::signedRoute('jobs.public-file', ['printJob' => $this]) : null;
    }

    public function formattedFileSize(): ?string
    {
        if ($this->file_size === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->file_size;

        foreach ($units as $unit) {
            if ($size < 1024 || $unit === 'GB') {
                return round($size, $size < 10 && $unit !== 'B' ? 1 : 0).' '.$unit;
            }

            $size /= 1024;
        }
    }
}
