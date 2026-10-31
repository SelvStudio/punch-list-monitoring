<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PunchList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $projects = Project::orderBy('project_name')->get();
        $picUsers = User::where('role', 'field_team')->where('is_active', true)->orderBy('name')->get();

        return view('calendar.index', compact('projects', 'picUsers'));
    }

    public function events(Request $request): JsonResponse
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

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

        $items = $query->get();

        $events = $items->map(function ($item) use ($today) {
            $dueDate = Carbon::parse($item->due_date, 'Asia/Jakarta')->startOfDay();
            $isOverdue = ($today->gt($dueDate) && $item->status !== PunchList::STATUS_SELESAI);

            // Determine color
            if ($item->status === PunchList::STATUS_SELESAI) {
                $color = '#28a745'; // Green
                $textColor = '#ffffff';
            } elseif ($isOverdue) {
                $color = '#dc3545'; // Red
                $textColor = '#ffffff';
            } elseif ($item->status === PunchList::STATUS_MENUNGGU_VERIFIKASI) {
                $color = '#17a2b8'; // Cyan
                $textColor = '#ffffff';
            } elseif ($item->status === PunchList::STATUS_DALAM_PROSES) {
                $color = '#fd7e14'; // Orange
                $textColor = '#ffffff';
            } else {
                $color = '#007bff'; // Blue
                $textColor = '#ffffff';
            }

            return [
                'id' => $item->id,
                'title' => "{$item->punch_code}: {$item->title}",
                'start' => $item->due_date->format('Y-m-d'),
                'url' => route('punch-lists.show', $item),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'code' => $item->punch_code,
                    'status' => $item->status,
                    'priority' => $item->priority,
                    'pic' => $item->pic?->name ?? 'Belum Ditugaskan',
                    'project' => $item->project->project_name,
                    'is_overdue' => $isOverdue,
                ],
            ];
        });

        return response()->json($events);
    }
}
