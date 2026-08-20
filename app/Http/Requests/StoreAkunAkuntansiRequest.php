<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAkunAkuntansiRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->toko_id !== null; }
    public function rules(): array { return ['kode_akun' => 'required|string|max:20', 'nama_akun' => 'required|string|max:100', 'tipe_akun' => 'required|in:Aset,Kewajiban,Ekuitas,Pendapatan,Beban', 'saldo_normal' => 'required|in:Debit,Kredit', 'saldo_awal' => 'nullable|numeric|min:0']; }
}
