<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PunchListDocument extends Model
{
    use HasFactory;

    public const TYPE_BEFORE = 'Foto Sebelum Perbaikan';
    public const TYPE_DURING = 'Foto Selama Perbaikan';
    public const TYPE_AFTER = 'Foto Setelah Perbaikan';
    public const TYPE_SUPPORTING = 'Dokumen Pendukung';

    protected $fillable = [
        'punch_list_id',
        'uploaded_by',
        'document_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'caption',
    ];

    public function punchList(): BelongsTo
    {
        return $this->belongsTo(PunchList::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
