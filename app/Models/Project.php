<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'project_name',
        'location',
        'contractor_name',
        'maintenance_start_date',
        'maintenance_end_date',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_start_date' => 'date',
            'maintenance_end_date' => 'date',
        ];
    }

    public function punchLists(): HasMany
    {
        return $this->hasMany(PunchList::class);
    }

    public function getCompletionPercentageAttribute(): float
    {
        $total = $this->punchLists()->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->punchLists()->where('status', 'Selesai')->count();

        return round(($completed / $total) * 100, 1);
    }
}
