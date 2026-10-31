<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\InternalNotification;
use App\Models\Project;
use App\Models\PunchList;
use App\Models\PunchListComment;
use App\Models\PunchListDocument;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. System Settings
        SystemSetting::setValue('deadline_warning_days', 3, 'Ambang batas hari peringatan mendekati tenggat waktu');
        SystemSetting::setValue('company_name', 'PT Citra Kontraktor Utama Mandiri', 'Nama Kontraktor Utama');
        SystemSetting::setValue('app_contact', 'pemeliharaan@kontraktor.test', 'Email Dukungan Sistem');

        // 2. Core Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@punchlist.test'],
            [
                'name' => 'Administrator Sistem',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081234567890',
                'position' => 'Administrator Proyek & TI',
                'is_active' => true,
            ]
        );

        $management = User::firstOrCreate(
            ['email' => 'manajemen@punchlist.test'],
            [
                'name' => 'Budi Santoso, S.T.',
                'password' => Hash::make('password'),
                'role' => 'management',
                'phone' => '081298765432',
                'position' => 'Project Manager (Manajemen Kontraktor)',
                'is_active' => true,
            ]
        );

        // 3. Field Team (PIC from Capstone.docx)
        $picNames = [
            'anton' => ['name' => 'Anton Wijaya', 'pos' => 'Teknisi Terminasi Kabel'],
            'adul'  => ['name' => 'Adul Rochman', 'pos' => 'Teknisi Konektor & Baut'],
            'lala'  => ['name' => 'Lala Karmila', 'pos' => 'Teknisi Grounding Trafo'],
            'lili'  => ['name' => 'Lili Suryani', 'pos' => 'Teknisi Skun & Bodi Trafo'],
            'adi'   => ['name' => 'Adi Nugroho', 'pos' => 'Teknisi Neutral Grounding'],
            'raka'  => ['name' => 'Raka Pratama', 'pos' => 'Teknisi Terminasi Titik Grounding'],
            'andri' => ['name' => 'Andri Setiawan', 'pos' => 'Teknisi Pembautan Terminal'],
            'amira' => ['name' => 'Amira Kusuma', 'pos' => 'Inspektur Nilai Tahanan Grounding'],
            'asep'  => ['name' => 'Asep Saepullah', 'pos' => 'Teknisi Isolasi & Skun'],
            'ali'   => ['name' => 'Ali Syahputra', 'pos' => 'Teknisi Suplai Daya & Ballast'],
            'aca'   => ['name' => 'Aca Darmawan', 'pos' => 'Teknisi Kontinuitas Jalur Kabel'],
            'dul'   => ['name' => 'Dul Gani', 'pos' => 'Teknisi Armatur Lampu'],
            'ramzi' => ['name' => 'Ramzi Fauzan', 'pos' => 'Teknisi Pemotongan & Skun Baru'],
            'rehan' => ['name' => 'Rehan Alamsyah', 'pos' => 'Teknisi Baut Torsi Kabel'],
            'rizal' => ['name' => 'Rizal Fahmi', 'pos' => 'Teknisi Megger Test Isolasi'],
        ];

        $picUsers = [];
        foreach ($picNames as $username => $info) {
            $picUsers[$username] = User::firstOrCreate(
                ['email' => "{$username}@punchlist.test"],
                [
                    'name' => $info['name'],
                    'password' => Hash::make('password'),
                    'role' => 'field_team',
                    'phone' => '08' . rand(111111111, 999999999),
                    'position' => $info['pos'],
                    'is_active' => true,
                ]
            );
        }

        // 4. Project
        $project = Project::firstOrCreate(
            ['project_code' => 'PRJ-2026-AFL01'],
            [
                'project_name' => 'Pemeliharaan Fasilitas Airfield Lighting (AFL) & Kelistrikan',
                'location' => 'Bandara Internasional - Area Runway, Taxiway, & Gardu AFL',
                'contractor_name' => 'PT Citra Kontraktor Utama Mandiri',
                'maintenance_start_date' => '2026-06-01',
                'maintenance_end_date' => '2026-12-01',
                'description' => 'Masa pemeliharaan pekerjaan sistem kelistrikan, lampu penerangan landasan, koneksi kabel, dan pembumian (grounding) trafo gardu bandara.',
                'status' => 'Masa Pemeliharaan',
            ]
        );

        // 5. 15 Punch List items from Capstone.docx
        $itemsData = [
            [
                'num' => 1,
                'title' => 'Connecting kabel rusak',
                'desc' => 'Koneksi/terminasi kabel mengalami kerusakan; perlu dilakukan pemotongan ulang, penyambungan kembali, atau penggantian skun kabel (cable lug).',
                'pic' => 'anton',
                'finding_date' => '2026-06-21',
                'due_date' => '2026-11-21',
                'category' => 'Elektrikal',
                'location' => 'Pit Box Pit-01 Runway Centerline',
                'priority' => PunchList::PRIORITY_TINGGI,
                'status' => PunchList::STATUS_SELESAI,
                'completed_at' => '2026-10-21 14:30:00',
                'verified_at' => '2026-10-22 09:00:00',
                'verified_by' => $management->id,
                'res_notes' => 'Telah dilakukan pemotongan kabel lapuk, penyambungan konektor kedap air baru, dan pengetesan isolasi berhasil.',
                'before_img' => 'image1.jpeg',
                'after_img' => 'image2.jpeg',
            ],
            [
                'num' => 2,
                'title' => 'Connecting kabel rusak',
                'desc' => 'Konektor/terminasi kabel mengalami kerusakan/kendur; perlu perbaikan koneksi serta pengencangan ulang baut terminal.',
                'pic' => 'adul',
                'finding_date' => '2026-06-14',
                'due_date' => '2026-11-14',
                'category' => 'Elektrikal',
                'location' => 'Pit Box Pit-04 Taxiway Alpha',
                'priority' => PunchList::PRIORITY_TINGGI,
                'status' => PunchList::STATUS_SELESAI,
                'completed_at' => '2026-10-18 11:00:00',
                'verified_at' => '2026-10-19 10:15:00',
                'verified_by' => $management->id,
                'res_notes' => 'Konektor telah diganti baru, dibalut shrink sleeve kedap air dan baut dikencangkan sesuai torsi standar.',
                'before_img' => 'image3.jpeg',
                'after_img' => 'image4.jpeg',
            ],
            [
                'num' => 3,
                'title' => 'Kabel BC belum di-couple ke trafo',
                'desc' => 'Kabel Bare Copper (BC) untuk sistem pembumian (grounding) belum terhubung/terpasang pada busbar/terminal grounding trafo.',
                'pic' => 'lala',
                'finding_date' => '2026-07-19',
                'due_date' => '2026-11-26',
                'category' => 'Grounding & Proteksi',
                'location' => 'Gardu Trafo Substation AFL Unit 1',
                'priority' => PunchList::PRIORITY_KRITIS,
                'status' => PunchList::STATUS_MENUNGGU_VERIFIKASI,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => 'Kabel BC telah di-couple ke grounding bar trafo unit 1. Mohon verifikasi dan pengukuran tahanan dari Manajemen.',
                'before_img' => null,
                'after_img' => 'image5.jpeg',
            ],
            [
                'num' => 4,
                'title' => 'Kabel BC belum di-couple ke trafo',
                'desc' => 'Kabel BC grounding belum dikoneksikan ke bodi/body grounding trafo; lakukan penyambungan menggunakan klem/skun kabel yang sesuai.',
                'pic' => 'lili',
                'finding_date' => '2026-07-19',
                'due_date' => '2026-11-26',
                'category' => 'Grounding & Proteksi',
                'location' => 'Gardu Trafo Substation AFL Unit 2',
                'priority' => PunchList::PRIORITY_KRITIS,
                'status' => PunchList::STATUS_MENUNGGU_VERIFIKASI,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => 'Penyambungan menggunakan klem tembaga tahan korosi telah diselesaikan, menunggu inspeksi lapangan.',
                'before_img' => null,
                'after_img' => 'image6.jpeg',
            ],
            [
                'num' => 5,
                'title' => 'Kabel BC belum di-couple ke trafo',
                'desc' => 'Kabel BC belum terpasang ke terminal neutral/grounding trafo; lakukan penyambungan dan pastikan koneksi kencang.',
                'pic' => 'adi',
                'finding_date' => '2026-07-17',
                'due_date' => '2026-11-17',
                'category' => 'Grounding & Proteksi',
                'location' => 'Gardu Trafo Substation AFL Unit 3',
                'priority' => PunchList::PRIORITY_TINGGI,
                'status' => PunchList::STATUS_DALAM_PROSES,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => 'Sedang dalam pengadaan klem lug tembaga 70mm2 dan pembersihan terminal trafo.',
                'before_img' => null,
                'after_img' => 'image7.jpeg',
            ],
            [
                'num' => 6,
                'title' => 'Kabel BC belum di-couple ke trafo',
                'desc' => 'Kabel BC grounding belum terhubung ke trafo; lakukan terminasi kabel BC pada titik grounding trafo.',
                'pic' => 'raka',
                'finding_date' => '2026-07-19',
                'due_date' => '2026-11-26',
                'category' => 'Grounding & Proteksi',
                'location' => 'Gardu Step-Up 3.3kV Runway 09',
                'priority' => PunchList::PRIORITY_SEDANG,
                'status' => PunchList::STATUS_DALAM_PROSES,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => 'Jalur kabel BC sudah ditarik, menunggu jadwal pemadaman trafo untuk pengencangan terminal.',
                'before_img' => null,
                'after_img' => 'image8.jpeg',
            ],
            [
                'num' => 7,
                'title' => 'Kabel BC belum di-couple ke trafo',
                'desc' => 'Penyambungan kabel BC ke grounding trafo belum dilakukan; persiapkan lug kabel dan lakukan pembautan pada terminal trafo.',
                'pic' => 'andri',
                'finding_date' => '2026-10-01',
                'due_date' => '2026-11-01',
                'category' => 'Grounding & Proteksi',
                'location' => 'Gardu Distribusi Sektor Bravo',
                'priority' => PunchList::PRIORITY_KRITIS,
                'status' => PunchList::STATUS_DALAM_PROSES,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => 'Material skun dan baut galvanis sudah siap di gudang proyek.',
                'before_img' => null,
                'after_img' => 'image9.jpeg',
            ],
            [
                'num' => 8,
                'title' => 'Kabel BC belum di-couple ke trafo',
                'desc' => 'Kabel BC grounding mengantung dan belum terhubung ke trafo; lakukan terminasi dan pastikan nilai tahanan grounding sesuai standar.',
                'pic' => 'amira',
                'finding_date' => '2026-07-19',
                'due_date' => '2026-11-26',
                'category' => 'Grounding & Proteksi',
                'location' => 'Gardu CCR (Constant Current Regulator) 1',
                'priority' => PunchList::PRIORITY_TINGGI,
                'status' => PunchList::STATUS_DITUGASKAN,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => null,
                'before_img' => null,
                'after_img' => 'image10.jpeg',
            ],
            [
                'num' => 9,
                'title' => 'Connecting kabel rusak',
                'desc' => 'Konektor/skun kabel mengalami kerusakan/korosi; lakukan penggantian konektor baru dan isolasi ulang.',
                'pic' => 'asep',
                'finding_date' => '2026-07-18',
                'due_date' => '2026-11-18',
                'category' => 'Elektrikal',
                'location' => 'Pit Box Pit-08 Threshold Runway 27',
                'priority' => PunchList::PRIORITY_SEDANG,
                'status' => PunchList::STATUS_DITUGASKAN,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => null,
                'before_img' => 'image11.jpeg',
                'after_img' => 'image13.jpeg',
            ],
            [
                'num' => 10,
                'title' => 'Lampu mati',
                'desc' => 'Lampu penerangan tidak menyala; lakukan pengecekan suplai daya, sakelar, ballast, atau lakukan penggantian bohlam lampu baru.',
                'pic' => 'ali',
                'finding_date' => '2026-06-12',
                'due_date' => '2026-11-12',
                'category' => 'Airfield Lighting',
                'location' => 'Runway Centerline Light RCL-221',
                'priority' => PunchList::PRIORITY_KRITIS,
                'status' => PunchList::STATUS_SELESAI,
                'completed_at' => '2026-10-15 22:00:00',
                'verified_at' => '2026-10-16 08:30:00',
                'verified_by' => $management->id,
                'res_notes' => 'Dilakukan penggantian modul LED armatur RCL-221 dan pembersihan optik lensa. Lampu telah diuji menyala normal.',
                'before_img' => 'image14.jpeg',
                'after_img' => 'image15.jpeg',
            ],
            [
                'num' => 11,
                'title' => 'Connecting kabel rusak',
                'desc' => 'Koneksi kabel terbakar/rusak; perlu perbaikan kabel, penggantian terminal block/skun, serta pengujian kontinuitas.',
                'pic' => 'aca',
                'finding_date' => '2026-07-17',
                'due_date' => '2026-11-10',
                'category' => 'Elektrikal',
                'location' => 'Pit Box Pit-12 Taxiway Charlie',
                'priority' => PunchList::PRIORITY_TINGGI,
                'status' => PunchList::STATUS_DALAM_PROSES,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => 'Bagian terbakar telah dipotong, sedang menunggu pengeringan area pit sebelum pemasangan waterproof joint resin.',
                'before_img' => 'image16.jpeg',
                'after_img' => 'image17.jpeg',
            ],
            [
                'num' => 12,
                'title' => 'Lampu mati',
                'desc' => 'Armatur/bohlam lampu mati; lakukan pemeriksaan pengkabelan fasa-netral dan ganti armatur/lampu jika rusak.',
                'pic' => 'dul',
                'finding_date' => '2026-06-12',
                'due_date' => '2026-11-12',
                'category' => 'Airfield Lighting',
                'location' => 'Runway Centerline Light RCL-225',
                'priority' => PunchList::PRIORITY_KRITIS,
                'status' => PunchList::STATUS_DITOLAK,
                'completed_at' => null,
                'verified_at' => '2026-10-20 16:00:00',
                'verified_by' => $management->id,
                'res_notes' => 'Catatan Verifikasi: Lampu menyala namun segel paking karet armatur belum terpasang rapat dan berpotensi kemasukan air hujan. Perbaiki paking segera.',
                'before_img' => 'image18.jpeg',
                'after_img' => 'image19.jpeg',
            ],
            [
                'num' => 13,
                'title' => 'Connecting kabel rusak',
                'desc' => 'Terminasi kabel terkelupas/rusak; lakukan pemotongan bagian kabel rusak dan pasang skun kabel baru.',
                'pic' => 'ramzi',
                'finding_date' => '2026-07-18',
                'due_date' => '2026-11-18',
                'category' => 'Elektrikal',
                'location' => 'Pit Box Pit-15 Apron Stand 4',
                'priority' => PunchList::PRIORITY_SEDANG,
                'status' => PunchList::STATUS_DITUGASKAN,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => null,
                'before_img' => null,
                'after_img' => 'image20.jpeg',
            ],
            [
                'num' => 14,
                'title' => 'Connecting kabel rusak',
                'desc' => 'Koneksi sambungan kabel tidak sempurna/rusak; lakukan terminasi ulang dan kencangkan sesuai spesifikasi torak/baut.',
                'pic' => 'rehan',
                'finding_date' => '2026-07-18',
                'due_date' => '2026-11-18',
                'category' => 'Elektrikal',
                'location' => 'Pit Box Pit-16 Apron Stand 5',
                'priority' => PunchList::PRIORITY_SEDANG,
                'status' => PunchList::STATUS_BARU,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => null,
                'before_img' => null,
                'after_img' => 'image21.jpeg',
            ],
            [
                'num' => 15,
                'title' => 'Connecting kabel rusak',
                'desc' => 'Koneksi kabel cacat/rusak; lakukan penggantian aksesoris sambungan dan lakukan pengujian isolasi kabel (megger test).',
                'pic' => 'rizal',
                'finding_date' => '2026-07-18',
                'due_date' => '2026-11-18',
                'category' => 'Elektrikal',
                'location' => 'Pit Box Pit-18 Perimeter Sektor Selatan',
                'priority' => PunchList::PRIORITY_SEDANG,
                'status' => PunchList::STATUS_BARU,
                'completed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'res_notes' => null,
                'before_img' => null,
                'after_img' => null,
            ],
        ];

        // Also add 1 overdue demonstration item to specifically show active overdue warning in dashboard
        $itemsData[] = [
            'num' => 16,
            'title' => 'Inspeksi Panel Kontrol AFL Sektor Timur',
            'desc' => 'Terminal kabel kontaktor utama ditemukan tanda panas berlebih (overheating) dan memerlukan pembersihan serta penggantian kontaktor.',
            'pic' => 'anton',
            'finding_date' => '2026-10-05',
            'due_date' => '2026-10-18', // Past due (overdue)
            'category' => 'Elektrikal',
            'location' => 'Ruang Panel Timur Gardu 2',
            'priority' => PunchList::PRIORITY_KRITIS,
            'status' => PunchList::STATUS_DALAM_PROSES,
            'completed_at' => null,
            'verified_at' => null,
            'verified_by' => null,
            'res_notes' => 'Menunggu suku cadang pengganti kontaktor Schneider 60A.',
            'before_img' => null,
            'after_img' => null,
        ];

        foreach ($itemsData as $row) {
            $pic = $picUsers[$row['pic']] ?? $picUsers['anton'];
            $code = sprintf('PL-2026-%04d', $row['num']);

            $punchList = PunchList::updateOrCreate(
                ['punch_code' => $code],
                [
                    'project_id' => $project->id,
                    'title' => $row['title'],
                    'description' => $row['desc'],
                    'category' => $row['category'],
                    'location_detail' => $row['location'],
                    'priority' => $row['priority'],
                    'status' => $row['status'],
                    'pic_user_id' => $pic->id,
                    'finding_date' => $row['finding_date'],
                    'due_date' => $row['due_date'],
                    'completed_at' => $row['completed_at'],
                    'verified_at' => $row['verified_at'],
                    'verified_by' => $row['verified_by'],
                    'resolution_notes' => $row['res_notes'],
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );

            // Seed documents if image exists
            if (!empty($row['before_img'])) {
                $filePath = 'punch_documents/' . $row['before_img'];
                $fullPath = storage_path('app/public/' . $filePath);
                $fileSize = File::exists($fullPath) ? File::size($fullPath) : 25000;

                PunchListDocument::firstOrCreate(
                    [
                        'punch_list_id' => $punchList->id,
                        'document_type' => PunchListDocument::TYPE_BEFORE,
                    ],
                    [
                        'uploaded_by' => $admin->id,
                        'file_path' => $filePath,
                        'original_filename' => $row['before_img'],
                        'mime_type' => 'image/jpeg',
                        'file_size' => $fileSize,
                        'caption' => 'Kondisi temuan awal di lapangan (' . $row['finding_date'] . ')',
                    ]
                );
            }

            if (!empty($row['after_img'])) {
                $filePath = 'punch_documents/' . $row['after_img'];
                $fullPath = storage_path('app/public/' . $filePath);
                $fileSize = File::exists($fullPath) ? File::size($fullPath) : 30000;

                PunchListDocument::firstOrCreate(
                    [
                        'punch_list_id' => $punchList->id,
                        'document_type' => PunchListDocument::TYPE_AFTER,
                    ],
                    [
                        'uploaded_by' => $pic->id,
                        'file_path' => $filePath,
                        'original_filename' => $row['after_img'],
                        'mime_type' => 'image/jpeg',
                        'file_size' => $fileSize,
                        'caption' => 'Hasil perbaikan dan tindak lanjut oleh PIC ' . $pic->name,
                    ]
                );
            }

            // Seed sample comments
            PunchListComment::firstOrCreate(
                [
                    'punch_list_id' => $punchList->id,
                    'user_id' => $admin->id,
                    'comment' => "Punch list diterbitkan pada tanggal {$row['finding_date']}. Harap PIC segera menindaklanjuti sebelum tenggat waktu {$row['due_date']}.",
                ]
            );

            if ($row['status'] !== PunchList::STATUS_BARU) {
                PunchListComment::firstOrCreate(
                    [
                        'punch_list_id' => $punchList->id,
                        'user_id' => $pic->id,
                        'comment' => "Pekerjaan diterima oleh PIC {$pic->name}. Koordinasi teknis lapangan dan persiapan alat/material telah dilakukan.",
                    ]
                );
            }

            // Seed initial activity log
            ActivityLog::create([
                'user_id' => $admin->id,
                'action' => 'CREATE',
                'entity_type' => PunchList::class,
                'entity_id' => $punchList->id,
                'description' => "Membuat catatan punch list {$punchList->punch_code}: {$punchList->title} ditugaskan kepada {$pic->name}.",
                'ip_address' => '127.0.0.1',
            ]);

            // Seed notification for PIC
            InternalNotification::create([
                'user_id' => $pic->id,
                'punch_list_id' => $punchList->id,
                'type' => 'assignment',
                'title' => "Penugasan Punch List Baru: {$punchList->punch_code}",
                'message' => "Anda telah ditugaskan menangani '{$punchList->title}' di {$punchList->location_detail} dengan tenggat {$punchList->due_date}.",
                'is_read' => ($row['status'] === PunchList::STATUS_SELESAI),
                'read_at' => ($row['status'] === PunchList::STATUS_SELESAI) ? Carbon::now('Asia/Jakarta') : null,
            ]);
        }

        // Add overdue notification for PIC Anton
        $overdueItem = PunchList::where('punch_code', 'PL-2026-0016')->first();
        if ($overdueItem) {
            InternalNotification::create([
                'user_id' => $picUsers['anton']->id,
                'punch_list_id' => $overdueItem->id,
                'type' => 'overdue',
                'title' => "Peringatan Keterlambatan: {$overdueItem->punch_code}",
                'message' => "Pekerjaan '{$overdueItem->title}' telah melewati batas tenggat ({$overdueItem->due_date}). Segera selesaikan dan laporkan kendala ke Manajemen.",
                'is_read' => false,
            ]);

            InternalNotification::create([
                'user_id' => $management->id,
                'punch_list_id' => $overdueItem->id,
                'type' => 'overdue',
                'title' => "Laporan Keterlambatan: {$overdueItem->punch_code}",
                'message' => "Pekerjaan '{$overdueItem->title}' oleh PIC Anton Wijaya telah terlambat dari tenggat ({$overdueItem->due_date}).",
                'is_read' => false,
            ]);
        }
    }
}
