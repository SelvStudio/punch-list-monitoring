<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PunchList;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $query = PunchList::query()->forUser($user);

        // Optional filter by project in dashboard
        $projectId = $request->input('project_id');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        // Summary Metrics
        $totalPunchList = (clone $query)->count();
        $totalBaru = (clone $query)->where('status', PunchList::STATUS_BARU)->count();
        $totalDitugaskan = (clone $query)->where('status', PunchList::STATUS_DITUGASKAN)->count();
        $totalDalamProses = (clone $query)->where('status', PunchList::STATUS_DALAM_PROSES)->count();
        $totalMenungguVerifikasi = (clone $query)->where('status', PunchList::STATUS_MENUNGGU_VERIFIKASI)->count();
        $totalSelesai = (clone $query)->where('status', PunchList::STATUS_SELESAI)->count();
        $totalDitolak = (clone $query)->where('status', PunchList::STATUS_DITOLAK)->count();

        // Active Overdue (due_date < today AND status != Selesai)
        $totalTerlambat = (clone $query)
            ->where('status', '!=', PunchList::STATUS_SELESAI)
            ->whereDate('due_date', '<', $today)
            ->count();

        // Priority High & Critical
        $totalHighCritical = (clone $query)
            ->whereIn('priority', [PunchList::PRIORITY_TINGGI, PunchList::PRIORITY_KRITIS])
            ->where('status', '!=', PunchList::STATUS_SELESAI)
            ->count();

        // Total Outstanding
        $totalBelumSelesai = $totalPunchList - $totalSelesai;

        // Completion Percentage: (Selesai / Total) * 100
        $persentasePenyelesaian = $totalPunchList > 0
            ? round(($totalSelesai / $totalPunchList) * 100, 1)
            : 0.0;

        // Status Distribution for Chart
        $statusLabels = [
            PunchList::STATUS_BARU,
            PunchList::STATUS_DITUGASKAN,
            PunchList::STATUS_DALAM_PROSES,
            PunchList::STATUS_MENUNGGU_VERIFIKASI,
            PunchList::STATUS_SELESAI,
            PunchList::STATUS_DITOLAK,
        ];
        $statusCounts = [];
        foreach ($statusLabels as $st) {
            $statusCounts[] = (clone $query)->where('status', $st)->count();
        }

        // Priority Distribution for Chart
        $priorityLabels = [
            PunchList::PRIORITY_RENDAH,
            PunchList::PRIORITY_SEDANG,
            PunchList::PRIORITY_TINGGI,
            PunchList::PRIORITY_KRITIS,
        ];
        $priorityCounts = [];
        foreach ($priorityLabels as $pr) {
            $priorityCounts[] = (clone $query)->where('priority', $pr)->count();
        }

        // Monthly Trend: finding vs completion for current year
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $findingsPerMonth = array_fill(0, 12, 0);
        $completionsPerMonth = array_fill(0, 12, 0);

        $findingsData = (clone $query)
            ->selectRaw("strftime('%m', finding_date) as month, count(*) as count")
            ->groupBy('month')
            ->get();

        foreach ($findingsData as $row) {
            $m = intval($row->month) - 1;
            if ($m >= 0 && $m < 12) {
                $findingsPerMonth[$m] = $row->count;
            }
        }

        $completionsData = (clone $query)
            ->whereNotNull('completed_at')
            ->selectRaw("strftime('%m', completed_at) as month, count(*) as count")
            ->groupBy('month')
            ->get();

        foreach ($completionsData as $row) {
            $m = intval($row->month) - 1;
            if ($m >= 0 && $m < 12) {
                $completionsPerMonth[$m] = $row->count;
            }
        }

        // Most Urgent / Overdue Items (Limit 5)
        $urgentPunchLists = (clone $query)
            ->where('status', '!=', PunchList::STATUS_SELESAI)
            ->with(['pic', 'project'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        // Recent Punch Lists (Limit 5)
        $recentPunchLists = (clone $query)
            ->with(['pic', 'project'])
            ->latest()
            ->limit(5)
            ->get();

        $projects = Project::orderBy('project_name')->get();
        $warningDays = SystemSetting::getDeadlineWarningDays();

        return view('dashboard.index', compact(
            'totalPunchList',
            'totalBaru',
            'totalDitugaskan',
            'totalDalamProses',
            'totalMenungguVerifikasi',
            'totalSelesai',
            'totalDitolak',
            'totalTerlambat',
            'totalHighCritical',
            'totalBelumSelesai',
            'persentasePenyelesaian',
            'statusLabels',
            'statusCounts',
            'priorityLabels',
            'priorityCounts',
            'months',
            'findingsPerMonth',
            'completionsPerMonth',
            'urgentPunchLists',
            'recentPunchLists',
            'projects',
            'projectId',
            'warningDays'
        ));
    }
}
