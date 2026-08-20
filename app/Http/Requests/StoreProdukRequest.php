<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProdukRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->toko_id !== null; }

    public function rules(): array
    {
        return ['kategori_id' => ['required', 'integer', Rule::exists('kategori_produk', 'id')->where('toko_id', $this->user()->toko_id)], 'barcode' => ['required', 'string', 'max:50', Rule::unique('produk', 'barcode')->where('toko_id', $this->user()->toko_id)], 'nama_produk' => ['required', 'string', 'max:150'], 'satuan' => ['required', 'string', 'max:20'], 'harga_modal' => ['required', 'numeric', 'min:0'], 'harga_jual_umum' => ['required', 'numeric', 'min:0'], 'harga_jual_member' => ['required', 'numeric', 'min:0'], 'harga_jual_rekan' => ['required', 'numeric', 'min:0'], 'harga_jual_motoris' => ['required', 'numeric', 'min:0'], 'stok' => ['required', 'integer', 'min:0'], 'stok_minimum' => ['required', 'integer', 'min:0'], 'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048']];
    }

    public function messages(): array
    {
        return ['kategori_id.required' => 'Kategori wajib dipilih.', 'kategori_id.exists' => 'Kategori yang dipilih tidak valid.', 'barcode.required' => 'Barcode wajib diisi.', 'barcode.unique' => 'Barcode sudah digunakan produk lain.', 'nama_produk.required' => 'Nama produk wajib diisi.', 'satuan.required' => 'Satuan wajib diisi.', 'harga_modal.required' => 'Harga modal wajib diisi.', 'harga_jual_umum.required' => 'Harga jual umum wajib diisi.', 'harga_jual_member.required' => 'Harga jual member wajib diisi.', 'harga_jual_rekan.required' => 'Harga jual rekan wajib diisi.', 'harga_jual_motoris.required' => 'Harga jual motoris wajib diisi.', 'stok.required' => 'Stok awal wajib diisi.', 'stok_minimum.required' => 'Stok minimum wajib diisi.'];
    }
}
