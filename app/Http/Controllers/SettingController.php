<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $warningDays = SystemSetting::getDeadlineWarningDays();
        $companyName = SystemSetting::getValue('company_name', 'PT Citra Kontraktor Utama Mandiri');
        $appContact = SystemSetting::getValue('app_contact', 'pemeliharaan@kontraktor.test');

        return view('settings.index', compact('warningDays', 'companyName', 'appContact'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'deadline_warning_days' => 'required|integer|min:1|max:30',
            'company_name' => 'required|string|max:255',
            'app_contact' => 'nullable|string|max:255',
        ], [
            'deadline_warning_days.required' => 'Ambang hari peringatan tenggat wajib diisi.',
            'deadline_warning_days.min' => 'Ambang minimal 1 hari.',
            'company_name.required' => 'Nama perusahaan kontraktor wajib diisi.',
        ]);

        SystemSetting::setValue('deadline_warning_days', $request->deadline_warning_days, 'Ambang batas hari peringatan mendekati tenggat waktu');
        SystemSetting::setValue('company_name', $request->company_name, 'Nama Kontraktor Utama');
        SystemSetting::setValue('app_contact', $request->app_contact, 'Email Dukungan Sistem');

        ActivityLog::record(
            'UPDATE_SETTINGS',
            'SystemSetting',
            null,
            "Memperbarui pengaturan sistem: ambang peringatan={$request->deadline_warning_days} hari."
        );

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    public function triggerDeadlineCheck(): RedirectResponse
    {
        Artisan::call('punchlist:check-deadlines');
        $output = Artisan::output();

        ActivityLog::record(
            'MANUAL_DEADLINE_CHECK',
            'SystemSetting',
            null,
            'Memicu pemeriksaan manual tenggat waktu pekerjaan.'
        );

        return back()->with('success', 'Pemeriksaan tenggat waktu berhasil dijalankan: ' . trim($output));
    }
}
