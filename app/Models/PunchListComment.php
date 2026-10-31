<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PunchListComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'punch_list_id',
        'user_id',
        'comment',
    ];

    public function punchList(): BelongsTo
    {
        return $this->belongsTo(PunchList::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
