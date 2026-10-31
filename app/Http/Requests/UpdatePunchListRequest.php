<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePunchListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole(['admin', 'management']);
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location_detail' => 'nullable|string|max:255',
            'priority' => 'required|in:Rendah,Sedang,Tinggi,Kritis',
            'pic_user_id' => 'nullable|exists:users,id',
            'finding_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:finding_date',
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'Proyek wajib dipilih.',
            'title.required' => 'Judul punch list wajib diisi.',
            'description.required' => 'Deskripsi temuan wajib diisi.',
            'category.required' => 'Kategori pekerjaan wajib dipilih.',
            'priority.required' => 'Tingkat prioritas wajib ditentukan.',
            'finding_date.required' => 'Tanggal temuan wajib diisi.',
            'due_date.required' => 'Tenggat waktu penyelesaian wajib diisi.',
            'due_date.after_or_equal' => 'Tenggat waktu tidak boleh lebih awal dari tanggal temuan.',
        ];
    }
}
