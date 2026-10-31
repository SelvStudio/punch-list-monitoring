<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePunchListRequest;
use App\Http\Requests\UpdatePunchListRequest;
use App\Models\ActivityLog;
use App\Models\InternalNotification;
use App\Models\Project;
use App\Models\PunchList;
use App\Models\PunchListComment;
use App\Models\PunchListDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PunchListController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $query = PunchList::with(['project', 'pic'])->forUser($user);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('punch_code', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('location_detail', 'LIKE', "%{$search}%");
            });
        }

        // Project filter
        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        // PIC filter
        if ($picId = $request->input('pic_user_id')) {
            $query->where('pic_user_id', $picId);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Priority filter
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // Category filter
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Finding Date Range
        if ($findingStart = $request->input('finding_start')) {
            $query->whereDate('finding_date', '>=', $findingStart);
        }
        if ($findingEnd = $request->input('finding_end')) {
            $query->whereDate('finding_date', '<=', $findingEnd);
        }

        // Due Date Range
        if ($dueStart = $request->input('due_start')) {
            $query->whereDate('due_date', '>=', $dueStart);
        }
        if ($dueEnd = $request->input('due_end')) {
            $query->whereDate('due_date', '<=', $dueEnd);
        }

        // Overdue filter
        if ($request->boolean('filter_overdue')) {
            $query->where('status', '!=', PunchList::STATUS_SELESAI)
                ->whereDate('due_date', '<', $today);
        }

        // Approaching deadline filter
        if ($request->boolean('filter_approaching')) {
            $query->approachingDeadline();
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = ['created_at', 'due_date', 'finding_date', 'priority', 'status', 'punch_code'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        if (!in_array(strtolower($sortDir), ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $punchLists = $query->orderBy($sortBy, $sortDir)->paginate(10)->withQueryString();

        $projects = Project::orderBy('project_name')->get();
        $picUsers = User::where('role', 'field_team')->where('is_active', true)->orderBy('name')->get();
        $categories = PunchList::select('category')->distinct()->pluck('category');

        return view('punch_lists.index', compact('punchLists', 'projects', 'picUsers', 'categories'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'management'])) {
            abort(403, 'Hanya Administrator atau Manajemen yang dapat menambah punch list baru.');
        }

        $projects = Project::orderBy('project_name')->get();
        $picUsers = User::where('role', 'field_team')->where('is_active', true)->orderBy('name')->get();
        $suggestedCode = PunchList::generatePunchCode();

        return view('punch_lists.create', compact('projects', 'picUsers', 'suggestedCode'));
    }

    public function store(StorePunchListRequest $request): RedirectResponse
    {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $status = $request->filled('pic_user_id') ? PunchList::STATUS_DITUGASKAN : PunchList::STATUS_BARU;
            $code = PunchList::generatePunchCode();

            $punchList = PunchList::create([
                'punch_code' => $code,
                'project_id' => $request->project_id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'location_detail' => $request->location_detail,
                'priority' => $request->priority,
                'status' => $status,
                'pic_user_id' => $request->pic_user_id,
                'finding_date' => $request->finding_date,
                'due_date' => $request->due_date,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Handle initial document upload if present
            if ($request->hasFile('document_before')) {
                $file = $request->file('document_before');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $path = $file->storeAs('punch_documents', $filename, 'public');

                PunchListDocument::create([
                    'punch_list_id' => $punchList->id,
                    'uploaded_by' => $user->id,
                    'document_type' => PunchListDocument::TYPE_BEFORE,
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'caption' => $request->input('caption_before') ?? 'Dokumentasi temuan awal',
                ]);
            }

            // Record Activity Log
            ActivityLog::record(
                'CREATE',
                PunchList::class,
                $punchList->id,
                "Membuat punch list baru [{$punchList->punch_code}] {$punchList->title}."
            );

            // Notify PIC if assigned
            if ($punchList->pic_user_id) {
                InternalNotification::create([
                    'user_id' => $punchList->pic_user_id,
                    'punch_list_id' => $punchList->id,
                    'type' => 'assignment',
                    'title' => "Penugasan Punch List Baru: {$punchList->punch_code}",
                    'message' => "Anda telah ditugaskan untuk '{$punchList->title}' di {$punchList->location_detail} dengan tenggat waktu {$punchList->due_date->format('d/m/Y')}.",
                ]);
            }

            DB::commit();

            return redirect()->route('punch-lists.show', $punchList)
                ->with('success', "Punch list {$punchList->punch_code} berhasil dibuat.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan punch list: ' . $e->getMessage());
        }
    }

    public function show(PunchList $punchList): View
    {
        $user = Auth::user();

        // Field team can only view their own items
        if ($user->isFieldTeam() && $punchList->pic_user_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda hanya dapat melihat punch list yang ditugaskan kepada Anda.');
        }

        $punchList->load([
            'project',
            'pic',
            'creator',
            'updater',
            'verifier',
            'documents.uploader',
            'comments.user',
        ]);

        $activityLogs = ActivityLog::where('entity_type', PunchList::class)
            ->where('entity_id', $punchList->id)
            ->with('user')
            ->latest()
            ->get();

        return view('punch_lists.show', compact('punchList', 'activityLogs'));
    }

    public function edit(PunchList $punchList): View
    {
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'management'])) {
            abort(403, 'Hanya Administrator atau Manajemen yang dapat mengubah data punch list.');
        }

        $projects = Project::orderBy('project_name')->get();
        $picUsers = User::where('role', 'field_team')->where('is_active', true)->orderBy('name')->get();

        return view('punch_lists.edit', compact('punchList', 'projects', 'picUsers'));
    }

    public function update(UpdatePunchListRequest $request, PunchList $punchList): RedirectResponse
    {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $oldPicId = $punchList->pic_user_id;

            $punchList->update([
                'project_id' => $request->project_id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'location_detail' => $request->location_detail,
                'priority' => $request->priority,
                'pic_user_id' => $request->pic_user_id,
                'finding_date' => $request->finding_date,
                'due_date' => $request->due_date,
                'updated_by' => $user->id,
            ]);

            // If PIC changed, update status if was 'Baru' and notify new PIC
            if ($request->filled('pic_user_id') && $request->pic_user_id != $oldPicId) {
                if ($punchList->status === PunchList::STATUS_BARU) {
                    $punchList->update(['status' => PunchList::STATUS_DITUGASKAN]);
                }

                InternalNotification::create([
                    'user_id' => $punchList->pic_user_id,
                    'punch_list_id' => $punchList->id,
                    'type' => 'assignment',
                    'title' => "Penugasan Punch List: {$punchList->punch_code}",
                    'message' => "Anda telah ditugaskan untuk catatan punch list '{$punchList->title}'.",
                ]);
            }

            ActivityLog::record(
                'UPDATE',
                PunchList::class,
                $punchList->id,
                "Memperbarui data punch list {$punchList->punch_code}."
            );

            DB::commit();

            return redirect()->route('punch-lists.show', $punchList)
                ->with('success', "Punch list {$punchList->punch_code} berhasil diperbarui.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui punch list: ' . $e->getMessage());
        }
    }

    public function destroy(PunchList $punchList): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Hanya Administrator yang memiliki wewenang untuk menghapus punch list.');
        }

        $code = $punchList->punch_code;
        $punchList->delete();

        ActivityLog::record(
            'DELETE',
            PunchList::class,
            $punchList->id,
            "Menghapus punch list {$code} (soft delete)."
        );

        return redirect()->route('punch-lists.index')
            ->with('success', "Punch list {$code} berhasil dihapus.");
    }

    /**
     * Workflow state transition handler
     */
    public function updateStatus(Request $request, PunchList $punchList): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'status' => 'required|in:Dalam Proses,Menunggu Verifikasi,Selesai,Ditolak Saat Verifikasi',
            'resolution_notes' => 'nullable|string',
            'document_after' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'caption_after' => 'nullable|string|max:255',
        ]);

        $newStatus = $request->status;
        $notes = $request->resolution_notes;

        // Role-based workflow validation
        if ($user->isFieldTeam()) {
            // Field team cannot mark as Selesai directly or Ditolak
            if (in_array($newStatus, [PunchList::STATUS_SELESAI, PunchList::STATUS_DITOLAK], true)) {
                abort(403, 'Tim lapangan tidak berwenang memverifikasi atau menyelesaikan punch list secara langsung. Harap ajukan Menunggu Verifikasi.');
            }

            // If submitting for verification, require resolution notes
            if ($newStatus === PunchList::STATUS_MENUNGGU_VERIFIKASI && empty($notes)) {
                return back()->with('error', 'Catatan tindakan perbaikan wajib diisi sebelum mengajukan verifikasi.');
            }
        }

        if ($user->isManagement() || $user->isAdmin()) {
            // If rejecting verification, require explanation
            if ($newStatus === PunchList::STATUS_DITOLAK && empty($notes)) {
                return back()->with('error', 'Alasan penolakan verifikasi wajib dicantumkan agar tim lapangan dapat melakukan perbaikan lanjutan.');
            }
        }

        DB::beginTransaction();
        try {
            $updates = [
                'status' => $newStatus,
                'updated_by' => $user->id,
            ];

            if ($notes) {
                $updates['resolution_notes'] = $notes;
            }

            // Handle completion or rejection
            if ($newStatus === PunchList::STATUS_SELESAI) {
                $updates['completed_at'] = Carbon::now('Asia/Jakarta');
                $updates['verified_at'] = Carbon::now('Asia/Jakarta');
                $updates['verified_by'] = $user->id;
            } elseif ($newStatus === PunchList::STATUS_DITOLAK) {
                $updates['verified_at'] = Carbon::now('Asia/Jakarta');
                $updates['verified_by'] = $user->id;
                $updates['completed_at'] = null; // not completed yet
            }

            $punchList->update($updates);

            // Upload after document if attached
            if ($request->hasFile('document_after')) {
                $file = $request->file('document_after');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $path = $file->storeAs('punch_documents', $filename, 'public');

                PunchListDocument::create([
                    'punch_list_id' => $punchList->id,
                    'uploaded_by' => $user->id,
                    'document_type' => PunchListDocument::TYPE_AFTER,
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'caption' => $request->input('caption_after') ?? 'Dokumentasi bukti penyelesaian perbaikan',
                ]);
            }

            // Add comment if notes provided
            if ($notes) {
                PunchListComment::create([
                    'punch_list_id' => $punchList->id,
                    'user_id' => $user->id,
                    'comment' => "[Status: {$newStatus}] {$notes}",
                ]);
            }

            // Log activity
            $actionType = match ($newStatus) {
                PunchList::STATUS_SELESAI => 'VERIFY_APPROVE',
                PunchList::STATUS_DITOLAK => 'VERIFY_REJECT',
                PunchList::STATUS_MENUNGGU_VERIFIKASI => 'SUBMIT_VERIFICATION',
                default => 'STATUS_CHANGE',
            };

            ActivityLog::record(
                $actionType,
                PunchList::class,
                $punchList->id,
                "Pengguna {$user->name} mengubah status {$punchList->punch_code} menjadi '{$newStatus}'." . ($notes ? " Catatan: {$notes}" : '')
            );

            // Notifications
            if ($newStatus === PunchList::STATUS_MENUNGGU_VERIFIKASI) {
                // Notify Management
                $managers = User::whereIn('role', ['admin', 'management'])->get();
                foreach ($managers as $mgr) {
                    InternalNotification::create([
                        'user_id' => $mgr->id,
                        'punch_list_id' => $punchList->id,
                        'type' => 'verification_request',
                        'title' => "Permintaan Verifikasi: {$punchList->punch_code}",
                        'message' => "Pekerjaan '{$punchList->title}' telah diajukan untuk verifikasi oleh PIC " . ($punchList->pic?->name ?? 'Tim Lapangan') . ".",
                    ]);
                }
            } elseif ($newStatus === PunchList::STATUS_SELESAI && $punchList->pic_user_id) {
                InternalNotification::create([
                    'user_id' => $punchList->pic_user_id,
                    'punch_list_id' => $punchList->id,
                    'type' => 'verification_approved',
                    'title' => "Verifikasi Disetujui: {$punchList->punch_code}",
                    'message' => "Pekerjaan '{$punchList->title}' telah berhasil diverifikasi dan dinyatakan Selesai oleh {$user->name}.",
                ]);
            } elseif ($newStatus === PunchList::STATUS_DITOLAK && $punchList->pic_user_id) {
                InternalNotification::create([
                    'user_id' => $punchList->pic_user_id,
                    'punch_list_id' => $punchList->id,
                    'type' => 'verification_rejected',
                    'title' => "Verifikasi Ditolak: {$punchList->punch_code}",
                    'message' => "Pekerjaan '{$punchList->title}' ditolak saat verifikasi. Catatan: {$notes}. Harap lakukan perbaikan lanjutan.",
                ]);
            }

            DB::commit();

            return redirect()->route('punch-lists.show', $punchList)
                ->with('success', "Status punch list berhasil diperbarui menjadi {$newStatus}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    public function addComment(Request $request, PunchList $punchList): RedirectResponse
    {
        $user = Auth::user();

        // Field team can only comment on assigned items
        if ($user->isFieldTeam() && $punchList->pic_user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'comment' => 'required|string|max:2000',
        ], [
            'comment.required' => 'Komentar tidak boleh kosong.',
        ]);

        PunchListComment::create([
            'punch_list_id' => $punchList->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
        ]);

        ActivityLog::record(
            'COMMENT',
            PunchList::class,
            $punchList->id,
            "{$user->name} menambahkan komentar pada {$punchList->punch_code}."
        );

        return back()->with('success', 'Komentar tindak lanjut berhasil dikirim.');
    }

    public function uploadDocument(Request $request, PunchList $punchList): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isFieldTeam() && $punchList->pic_user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'document_file' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'document_type' => 'required|in:Foto Sebelum Perbaikan,Foto Selama Perbaikan,Foto Setelah Perbaikan,Dokumen Pendukung',
            'caption' => 'nullable|string|max:255',
        ], [
            'document_file.required' => 'File dokumen wajib dipilih.',
            'document_file.mimes' => 'Format file harus berupa JPG, PNG, atau PDF.',
            'document_file.max' => 'Ukuran file tidak boleh melebihi 10MB.',
            'document_type.required' => 'Jenis dokumentasi wajib dipilih.',
        ]);

        $file = $request->file('document_file');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $path = $file->storeAs('punch_documents', $filename, 'public');

        PunchListDocument::create([
            'punch_list_id' => $punchList->id,
            'uploaded_by' => $user->id,
            'document_type' => $request->document_type,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'caption' => $request->caption,
        ]);

        ActivityLog::record(
            'DOCUMENT_UPLOAD',
            PunchList::class,
            $punchList->id,
            "{$user->name} mengunggah berkas '{$file->getClientOriginalName()}' ({$request->document_type}) pada {$punchList->punch_code}."
        );

        return back()->with('success', 'Dokumentasi berhasil diunggah.');
    }

    public function deleteDocument(PunchListDocument $document): RedirectResponse
    {
        $user = Auth::user();

        // Admin or original uploader only
        if (!$user->isAdmin() && $document->uploaded_by !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus dokumen ini.');
        }

        $punchList = $document->punchList;
        $name = $document->original_filename;

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        ActivityLog::record(
            'DELETE_DOCUMENT',
            PunchList::class,
            $punchList->id,
            "{$user->name} menghapus dokumen '{$name}' dari {$punchList->punch_code}."
        );

        return back()->with('success', "Dokumen {$name} berhasil dihapus.");
    }
}
