<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Toko
 *
 * The root tenant entity. Every other transactional table references
 * this via toko_id. No Global Scope is applied here — this IS the scope.
 *
 * @property int         $id
 * @property string      $nama_toko
 * @property string      $alamat
 * @property string      $no_telepon
 * @property string|null $logo
 * @property string|null $slogan_struk
 */
class Toko extends Model
{
    /**
     * The table associated with the model.
     * Laravel would default to 'tokos'; we override to match the actual schema.
     */
    protected $table = 'toko';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_toko',
        'alamat',
        'no_telepon',
        'logo',
        'slogan_struk',
        'catalog_slug',
        'catalog_enabled',
        'catalog_theme',
        'catalog_hero_badge',
        'catalog_hero_title',
        'catalog_hero_description',
        'whatsapp_number',
        'whatsapp_enabled',
        'status_langganan',
        'berakhir_pada',
        'gemini_api_key',
        'gemini_model',
        'ai_provider',
        'ai_api_key',
        'ai_base_url',
        'ai_model',
        'ai_enabled',
        'ai_vision_enabled',
        'ai_total_requests',
        'ai_prompt_tokens',
        'ai_completion_tokens',
        'ai_total_tokens',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'berakhir_pada' => 'date',
        'ai_enabled' => 'boolean',
        'ai_vision_enabled' => 'boolean',
        'ai_api_key' => 'encrypted',
        'catalog_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    /**
     * A toko has many users (staff accounts).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'toko_id');
    }

    /**
     * A toko has many customers.
     */
    public function pelanggans(): HasMany
    {
        return $this->hasMany(Pelanggan::class, 'toko_id');
    }

    /**
     * A toko has many suppliers.
     */
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'toko_id');
    }

    /**
     * A toko has many product groups (kelompok).
     */
    public function kelompokProduks(): HasMany
    {
        return $this->hasMany(KelompokProduk::class, 'toko_id');
    }

    /**
     * A toko has many product categories.
     */
    public function kategoriProduks(): HasMany
    {
        return $this->hasMany(KategoriProduk::class, 'toko_id');
    }

    /**
     * A toko has many products.
     */
    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'toko_id');
    }

    /**
     * A toko has many purchase orders.
     */
    public function pembelians(): HasMany
    {
        return $this->hasMany(Pembelian::class, 'toko_id');
    }

    /**
     * A toko has many sales transactions.
     */
    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'toko_id');
    }

    /**
     * A toko has many cash flow entries.
     */
    public function arusKas(): HasMany
    {
        return $this->hasMany(ArusKas::class, 'toko_id');
    }

    /**
     * A toko has many stock movement logs.
     */
    public function logStoks(): HasMany
    {
        return $this->hasMany(LogStok::class, 'toko_id');
    }

    public function aiAssistantConfig()
    {
        return $this->hasOne(AiAssistantConfig::class);
    }

    public function aiActions(): HasMany
    {
        return $this->hasMany(AiActionLog::class);
    }

    /**
     * Record token usage statistics from Gemini API response.
     */
    public function recordAiUsage(?array $usageMetadata): void
    {
        if (empty($usageMetadata)) return;

        $promptTokens     = (int) ($usageMetadata['promptTokenCount'] ?? 0);
        $completionTokens = (int) ($usageMetadata['candidatesTokenCount'] ?? 0);
        $totalTokens      = (int) ($usageMetadata['totalTokenCount'] ?? ($promptTokens + $completionTokens));

        $this->increment('ai_total_requests', 1);
        if ($promptTokens > 0)     $this->increment('ai_prompt_tokens', $promptTokens);
        if ($completionTokens > 0) $this->increment('ai_completion_tokens', $completionTokens);
        if ($totalTokens > 0)      $this->increment('ai_total_tokens', $totalTokens);
    }
}
