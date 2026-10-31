<?php

namespace App\Console\Commands;

use App\Models\InternalNotification;
use App\Models\PunchList;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckDeadlinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'punchlist:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa tenggat waktu punch list dan kirimkan notifikasi pengingat / keterlambatan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $warningDays = SystemSetting::getDeadlineWarningDays();
        $this->info("Memeriksa tenggat waktu (Hari ini: {$today->toDateString()}, Ambang peringatan: {$warningDays} hari)...");

        $activePunchLists = PunchList::with(['pic', 'project'])
            ->where('status', '!=', PunchList::STATUS_SELESAI)
            ->get();

        $managementUsers = User::whereIn('role', ['admin', 'management'])->where('is_active', true)->get();
        $notificationCount = 0;

        foreach ($activePunchLists as $item) {
            $dueDate = Carbon::parse($item->due_date, 'Asia/Jakarta')->startOfDay();
            $diffDays = (int) $today->diffInDays($dueDate, false);

            // 1. OVERDUE (due_date < today)
            if ($today->gt($dueDate)) {
                $daysOverdue = (int) $dueDate->diffInDays($today);

                // Send to PIC
                if ($item->pic_user_id) {
                    $alreadyNotified = InternalNotification::where('user_id', $item->pic_user_id)
                        ->where('punch_list_id', $item->id)
                        ->where('type', 'overdue')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();

                    if (!$alreadyNotified) {
                        InternalNotification::create([
                            'user_id' => $item->pic_user_id,
                            'punch_list_id' => $item->id,
                            'type' => 'overdue',
                            'title' => "Peringatan Keterlambatan: {$item->punch_code}",
                            'message' => "Pekerjaan '{$item->title}' telah terlambat {$daysOverdue} hari dari tenggat waktu ({$item->due_date->format('d/m/Y')}). Segera selesaikan dan laporkan perkembangannya.",
                        ]);
                        $notificationCount++;
                    }
                }

                // Send to Management
                foreach ($managementUsers as $mgt) {
                    $alreadyNotified = InternalNotification::where('user_id', $mgt->id)
                        ->where('punch_list_id', $item->id)
                        ->where('type', 'overdue')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();

                    if (!$alreadyNotified) {
                        InternalNotification::create([
                            'user_id' => $mgt->id,
                            'punch_list_id' => $item->id,
                            'type' => 'overdue',
                            'title' => "Monitoring Keterlambatan: {$item->punch_code}",
                            'message' => "Pekerjaan '{$item->title}' (PIC: " . ($item->pic?->name ?? 'Belum Ditugaskan') . ") telah terlambat {$daysOverdue} hari.",
                        ]);
                        $notificationCount++;
                    }
                }
            }
            // 2. DUE TODAY (diff == 0)
            elseif ($diffDays === 0) {
                if ($item->pic_user_id) {
                    $alreadyNotified = InternalNotification::where('user_id', $item->pic_user_id)
                        ->where('punch_list_id', $item->id)
                        ->where('type', 'deadline_approaching')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();

                    if (!$alreadyNotified) {
                        InternalNotification::create([
                            'user_id' => $item->pic_user_id,
                            'punch_list_id' => $item->id,
                            'type' => 'deadline_approaching',
                            'title' => "Tenggat Waktu Hari Ini: {$item->punch_code}",
                            'message' => "Hari ini adalah batas akhir penyelesaian untuk '{$item->title}'. Mohon pastikan pekerjaan telah siap diverifikasi.",
                        ]);
                        $notificationCount++;
                    }
                }
            }
            // 3. APPROACHING DEADLINE (0 < diff <= warningDays)
            elseif ($diffDays > 0 && $diffDays <= $warningDays) {
                if ($item->pic_user_id) {
                    $alreadyNotified = InternalNotification::where('user_id', $item->pic_user_id)
                        ->where('punch_list_id', $item->id)
                        ->where('type', 'deadline_approaching')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();

                    if (!$alreadyNotified) {
                        InternalNotification::create([
                            'user_id' => $item->pic_user_id,
                            'punch_list_id' => $item->id,
                            'type' => 'deadline_approaching',
                            'title' => "Pengingat Tenggat ({$diffDays} Hari Lagi): {$item->punch_code}",
                            'message' => "Pekerjaan '{$item->title}' memiliki tenggat penyelesaian pada {$item->due_date->format('d/m/Y')}. Harap persiapkan tindak lanjut.",
                        ]);
                        $notificationCount++;
                    }
                }
            }
        }

        $this->info("Pemeriksaan selesai. Berhasil membuat {$notificationCount} notifikasi baru hari ini.");
        return Command::SUCCESS;
    }
}
