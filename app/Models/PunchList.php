<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PunchList extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_BARU = 'Baru';
    public const STATUS_DITUGASKAN = 'Ditugaskan';
    public const STATUS_DALAM_PROSES = 'Dalam Proses';
    public const STATUS_MENUNGGU_VERIFIKASI = 'Menunggu Verifikasi';
    public const STATUS_SELESAI = 'Selesai';
    public const STATUS_DITOLAK = 'Ditolak Saat Verifikasi';

    public const PRIORITY_RENDAH = 'Rendah';
    public const PRIORITY_SEDANG = 'Sedang';
    public const PRIORITY_TINGGI = 'Tinggi';
    public const PRIORITY_KRITIS = 'Kritis';

    protected $fillable = [
        'punch_code',
        'project_id',
        'title',
        'description',
        'category',
        'location_detail',
        'priority',
        'status',
        'pic_user_id',
        'finding_date',
        'due_date',
        'completed_at',
        'verified_at',
        'verified_by',
        'resolution_notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'finding_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PunchListDocument::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PunchListComment::class)->latest();
    }

    /**
     * Determine deadline status based on Asia/Jakarta timezone.
     */
    public function getDeadlineStatusAttribute(): array
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $dueDate = Carbon::parse($this->due_date, 'Asia/Jakarta')->startOfDay();

        if ($this->status === self::STATUS_SELESAI) {
            $isCompletedLate = false;
            $overdueDays = 0;
            if ($this->completed_at) {
                $completedDate = Carbon::parse($this->completed_at, 'Asia/Jakarta')->startOfDay();
                if ($completedDate->gt($dueDate)) {
                    $isCompletedLate = true;
                    $overdueDays = (int) $dueDate->diffInDays($completedDate);
                }
            }

            return [
                'status' => 'Selesai',
                'badge' => 'badge-success',
                'badge_class' => 'success',
                'label' => $isCompletedLate ? "Selesai (Terlambat {$overdueDays} hari)" : 'Selesai Tepat Waktu',
                'days' => 0,
                'is_overdue' => false,
                'was_late' => $isCompletedLate,
            ];
        }

        if ($this->status === self::STATUS_MENUNGGU_VERIFIKASI) {
            $isOverdue = $today->gt($dueDate);
            $diff = (int) $today->diffInDays($dueDate, false);
            return [
                'status' => 'Menunggu Verifikasi',
                'badge' => 'badge-info',
                'badge_class' => 'info',
                'label' => $isOverdue ? 'Menunggu Verifikasi (Lewat Tenggat)' : 'Menunggu Verifikasi',
                'days' => $diff,
                'is_overdue' => $isOverdue,
                'was_late' => false,
            ];
        }

        // Check if overdue
        if ($today->gt($dueDate)) {
            $daysOverdue = (int) $dueDate->diffInDays($today);
            return [
                'status' => 'Terlambat',
                'badge' => 'badge-danger',
                'badge_class' => 'danger',
                'label' => "Terlambat {$daysOverdue} hari",
                'days' => -$daysOverdue,
                'is_overdue' => true,
                'was_late' => false,
            ];
        }

        $daysRemaining = (int) $today->diffInDays($dueDate);
        $threshold = SystemSetting::getDeadlineWarningDays();

        if ($daysRemaining <= $threshold) {
            $label = $daysRemaining === 0 ? 'Tenggat Hari Ini' : "Mendekati Tenggat ({$daysRemaining} hari lagi)";
            return [
                'status' => 'Mendekati Tenggat',
                'badge' => 'badge-warning text-dark',
                'badge_class' => 'warning',
                'label' => $label,
                'days' => $daysRemaining,
                'is_overdue' => false,
                'was_late' => false,
            ];
        }

        return [
            'status' => 'Aman',
            'badge' => 'badge-primary',
            'badge_class' => 'primary',
            'label' => "{$daysRemaining} hari lagi",
            'days' => $daysRemaining,
            'is_overdue' => false,
            'was_late' => false,
        ];
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_KRITIS => 'badge-danger',
            self::PRIORITY_TINGGI => 'badge-warning text-dark',
            self::PRIORITY_SEDANG => 'badge-info',
            default => 'badge-secondary',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_BARU => 'badge-secondary',
            self::STATUS_DITUGASKAN => 'badge-primary',
            self::STATUS_DALAM_PROSES => 'badge-warning text-dark',
            self::STATUS_MENUNGGU_VERIFIKASI => 'badge-info',
            self::STATUS_SELESAI => 'badge-success',
            self::STATUS_DITOLAK => 'badge-danger',
            default => 'badge-dark',
        };
    }

    /**
     * Scope query to find active overdue items
     */
    public function scopeOverdue(Builder $query): Builder
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        return $query->where('status', '!=', self::STATUS_SELESAI)
            ->whereDate('due_date', '<', $today);
    }

    /**
     * Scope query to find urgent/approaching items
     */
    public function scopeApproachingDeadline(Builder $query, ?int $days = null): Builder
    {
        $days = $days ?? SystemSetting::getDeadlineWarningDays();
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $limitDate = Carbon::now('Asia/Jakarta')->addDays($days)->toDateString();

        return $query->whereNotIn('status', [self::STATUS_SELESAI])
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $limitDate);
    }

    /**
     * Scope query by role authorization:
     * - Field team only sees assigned punch lists
     * - Admin and Management see all
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isFieldTeam()) {
            return $query->where('pic_user_id', $user->id);
        }

        return $query;
    }

    /**
     * Generate next unique punch code
     */
    public static function generatePunchCode(): string
    {
        $year = Carbon::now('Asia/Jakarta')->format('Y');
        $prefix = "PL-{$year}-";

        $lastRecord = self::withTrashed()
            ->where('punch_code', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord && preg_match('/PL-\d{4}-(\d+)/', $lastRecord->punch_code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }
}
