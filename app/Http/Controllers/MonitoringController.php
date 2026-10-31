<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PunchList;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        $selectedProjectId = $request->input('project_id');
        $projects = Project::withCount('punchLists')->get();

        $currentProject = $selectedProjectId
            ? Project::find($selectedProjectId)
            : $projects->first();

        // Maintenance Period Metrics
        $maintenanceStats = null;
        if ($currentProject) {
            $mStart = Carbon::parse($currentProject->maintenance_start_date, 'Asia/Jakarta')->startOfDay();
            $mEnd = Carbon::parse($currentProject->maintenance_end_date, 'Asia/Jakarta')->startOfDay();

            $totalMaintenanceDays = max(1, $mStart->diffInDays($mEnd));
            $daysElapsed = $today->gt($mStart) ? min($totalMaintenanceDays, $mStart->diffInDays($today)) : 0;
            $daysRemaining = $today->lt($mEnd) ? (int) $today->diffInDays($mEnd) : 0;
            $maintenancePercent = round(($daysElapsed / $totalMaintenanceDays) * 100, 1);

            $maintenanceStats = [
                'start' => $mStart->format('d M Y'),
                'end' => $mEnd->format('d M Y'),
                'total_days' => $totalMaintenanceDays,
                'days_elapsed' => $daysElapsed,
                'days_remaining' => $daysRemaining,
                'percent' => $maintenancePercent,
                'is_ended' => $today->gt($mEnd),
            ];
        }

        // Query punch lists for monitoring
        $query = PunchList::query()->forUser($user);
        if ($currentProject) {
            $query->where('project_id', $currentProject->id);
        }

        $allPunchLists = (clone $query)->with(['pic', 'project'])->get();

        // Categorize into groups
        $overdueItems = $allPunchLists->filter(fn ($item) => $item->deadline_status['is_overdue'])->values();
        $approachingItems = $allPunchLists->filter(fn ($item) => $item->deadline_status['status'] === 'Mendekati Tenggat')->values();
        $inVerificationItems = $allPunchLists->filter(fn ($item) => $item->status === PunchList::STATUS_MENUNGGU_VERIFIKASI)->values();
        $completedItems = $allPunchLists->filter(fn ($item) => $item->status === PunchList::STATUS_SELESAI)->values();

        // Category breakdown
        $categoryBreakdown = $allPunchLists->groupBy('category')->map(function ($items, $cat) {
            $total = $items->count();
            $completed = $items->where('status', PunchList::STATUS_SELESAI)->count();
            $overdue = $items->filter(fn ($i) => $i->deadline_status['is_overdue'])->count();
            $percent = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

            return [
                'category' => $cat,
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $total - $completed,
                'overdue' => $overdue,
                'percent' => $percent,
            ];
        });

        // PIC Performance matrix (for Admin and Management)
        $picPerformance = collect();
        if ($user->hasRole(['admin', 'management'])) {
            $picUsers = User::where('role', 'field_team')->get();
            $picPerformance = $picUsers->map(function ($pic) use ($allPunchLists) {
                $picItems = $allPunchLists->where('pic_user_id', $pic->id);
                $total = $picItems->count();
                $completed = $picItems->where('status', PunchList::STATUS_SELESAI)->count();
                $overdue = $picItems->filter(fn ($i) => $i->deadline_status['is_overdue'])->count();
                $percent = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

                return [
                    'pic' => $pic,
                    'total' => $total,
                    'completed' => $completed,
                    'pending' => $total - $completed,
                    'overdue' => $overdue,
                    'rate' => $percent,
                ];
            })->filter(fn ($item) => $item['total'] > 0)->sortByDesc('pending')->values();
        }

        return view('monitoring.index', compact(
            'projects',
            'currentProject',
            'maintenanceStats',
            'overdueItems',
            'approachingItems',
            'inVerificationItems',
            'completedItems',
            'categoryBreakdown',
            'picPerformance'
        ));
    }
}
