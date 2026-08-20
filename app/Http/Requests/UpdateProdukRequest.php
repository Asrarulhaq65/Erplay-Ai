<?php

namespace App\Http\Requests;

use App\Models\Produk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProdukRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->toko_id !== null; }

    public function rules(): array
    {
        $produk = $this->route('produk');
        return ['kategori_id' => ['required', 'integer', Rule::exists('kategori_produk', 'id')->where('toko_id', $this->user()->toko_id)], 'barcode' => ['required', 'string', 'max:50', Rule::unique('produk', 'barcode')->where('toko_id', $this->user()->toko_id)->ignore($produk instanceof Produk ? $produk->id : $produk)], 'nama_produk' => ['required', 'string', 'max:150'], 'satuan' => ['required', 'string', 'max:20'], 'harga_modal' => ['required', 'numeric', 'min:0'], 'harga_jual_umum' => ['required', 'numeric', 'min:0'], 'harga_jual_member' => ['required', 'numeric', 'min:0'], 'harga_jual_rekan' => ['required', 'numeric', 'min:0'], 'harga_jual_motoris' => ['required', 'numeric', 'min:0'], 'stok' => ['required', 'integer', 'min:0'], 'stok_minimum' => ['required', 'integer', 'min:0'], 'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048']];
    }

    public function messages(): array { return (new StoreProdukRequest)->messages(); }
}
