<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PunchList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = $this->buildFilteredQuery($request, $user);

        $punchLists = $query->paginate(15)->withQueryString();

        $projects = Project::orderBy('project_name')->get();
        $picUsers = User::where('role', 'field_team')->where('is_active', true)->orderBy('name')->get();

        // Summary of current filter
        $totalCount = (clone $query)->count();
        $completedCount = (clone $query)->where('status', PunchList::STATUS_SELESAI)->count();
        $overdueCount = (clone $query)->where('status', '!=', PunchList::STATUS_SELESAI)
            ->whereDate('due_date', '<', Carbon::now('Asia/Jakarta')->toDateString())
            ->count();

        return view('reports.index', compact('punchLists', 'projects', 'picUsers', 'totalCount', 'completedCount', 'overdueCount'));
    }

    public function printView(Request $request): View
    {
        $user = Auth::user();
        $punchLists = $this->buildFilteredQuery($request, $user)->get();

        $totalCount = $punchLists->count();
        $completedCount = $punchLists->where('status', PunchList::STATUS_SELESAI)->count();
        $overdueCount = $punchLists->filter(fn ($i) => $i->deadline_status['is_overdue'])->count();
        $rate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0;

        return view('reports.print', compact('punchLists', 'totalCount', 'completedCount', 'overdueCount', 'rate'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $punchLists = $this->buildFilteredQuery($request, $user)->get();

        $filename = 'Laporan_Punch_List_' . Carbon::now('Asia/Jakarta')->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($punchLists) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header Row
            fputcsv($handle, [
                'No.',
                'Nomor Punch List',
                'Judul Catatan',
                'Deskripsi Singkat',
                'Proyek',
                'Lokasi Detail',
                'Kategori',
                'PIC',
                'Prioritas',
                'Tanggal Temuan',
                'Tenggat Penyelesaian',
                'Status',
                'Tanggal Selesai',
                'Durasi Penyelesaian (Hari)',
                'Keterangan Keterlambatan',
            ]);

            $index = 1;
            foreach ($punchLists as $item) {
                // Calculation of duration
                $duration = '-';
                if ($item->completed_at) {
                    $finding = Carbon::parse($item->finding_date);
                    $completed = Carbon::parse($item->completed_at);
                    $duration = $finding->diffInDays($completed) . ' hari';
                }

                $deadlineInfo = $item->deadline_status;
                $delayNote = $deadlineInfo['label'];

                // CSV injection prevention for Excel
                $clean = fn ($val) => $this->sanitizeForCsv((string) $val);

                fputcsv($handle, [
                    $index++,
                    $clean($item->punch_code),
                    $clean($item->title),
                    $clean($item->description),
                    $clean($item->project->project_name ?? '-'),
                    $clean($item->location_detail ?? '-'),
                    $clean($item->category),
                    $clean($item->pic?->name ?? 'Belum Ditugaskan'),
                    $clean($item->priority),
                    $item->finding_date->format('d/m/Y'),
                    $item->due_date->format('d/m/Y'),
                    $clean($item->status),
                    $item->completed_at ? Carbon::parse($item->completed_at)->format('d/m/Y H:i') : '-',
                    $clean($duration),
                    $clean($delayNote),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function buildFilteredQuery(Request $request, User $user)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $query = PunchList::with(['project', 'pic'])->forUser($user);

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }
        if ($picId = $request->input('pic_user_id')) {
            $query->where('pic_user_id', $picId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }
        if ($from = $request->input('date_from')) {
            $query->whereDate('finding_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('finding_date', '<=', $to);
        }
        if ($request->boolean('filter_overdue')) {
            $query->where('status', '!=', PunchList::STATUS_SELESAI)
                ->whereDate('due_date', '<', $today);
        }
        if ($request->boolean('filter_completed')) {
            $query->where('status', PunchList::STATUS_SELESAI);
        }

        return $query->orderBy('due_date', 'asc');
    }

    /**
     * Prevent CSV injection attacks by escaping dangerous leading characters
     */
    private function sanitizeForCsv(string $value): string
    {
        $dangerousChars = ['=', '+', '-', '@', "\t", "\r"];
        if (strlen($value) > 0 && in_array($value[0], $dangerousChars, true)) {
            return "'" . $value;
        }

        return $value;
    }
}
