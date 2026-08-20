<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJurnalRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->toko_id !== null; }
    public function rules(): array { return ['tanggal' => 'required|date', 'keterangan' => 'required|string|max:255', 'details' => 'required|array|min:2', 'details.*.akun_id' => ['required', Rule::exists('akun_akuntansi', 'id')->where('toko_id', $this->user()->toko_id)], 'details.*.debit' => 'required|numeric|min:0', 'details.*.kredit' => 'required|numeric|min:0']; }
}
