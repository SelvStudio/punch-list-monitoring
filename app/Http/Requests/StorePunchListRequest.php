<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePunchListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole(['admin', 'management']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            'document_before' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240', // max 10MB
            'caption_before' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'Proyek wajib dipilih.',
            'project_id.exists' => 'Proyek yang dipilih tidak valid.',
            'title.required' => 'Judul punch list wajib diisi.',
            'description.required' => 'Deskripsi temuan wajib diisi.',
            'category.required' => 'Kategori pekerjaan wajib dipilih.',
            'priority.required' => 'Tingkat prioritas wajib ditentukan.',
            'priority.in' => 'Prioritas harus salah satu dari: Rendah, Sedang, Tinggi, Kritis.',
            'finding_date.required' => 'Tanggal temuan wajib diisi.',
            'due_date.required' => 'Tenggat waktu penyelesaian wajib diisi.',
            'due_date.after_or_equal' => 'Tenggat waktu tidak boleh lebih awal dari tanggal temuan.',
            'document_before.mimes' => 'Format file harus berupa JPG, PNG, atau PDF.',
            'document_before.max' => 'Ukuran file tidak boleh melebihi 10MB.',
        ];
    }
}
