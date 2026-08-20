<?php

namespace App\Ai\Agents;

use App\Ai\Tools\LookupCustomerInfo;
use App\Ai\Tools\LookupLowStock;
use App\Ai\Tools\LookupProductInfo;
use App\Ai\Tools\LookupSalesToday;
use App\Ai\Tools\LookupSupplierInfo;
use App\Ai\Tools\LookupTopProducts;
use App\Ai\Tools\ContextualHelp;
use App\Ai\Tools\ExplainFeature;
use App\Ai\Tools\HowToUse;
use App\Ai\Tools\ListFeaturesByRole;
use App\Models\User;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxSteps(5)]
class ErpCopilotAgent implements Agent, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(public User $user) {}

    public function instructions(): Stringable|string
    {
        $toko = $this->user->toko;
        $config = $toko?->aiAssistantConfig;
        $assistantName = $config?->assistant_name ?: 'ERPlay AI Assistant';
        $personality = $config?->personality ?: 'profesional';
        return "Anda adalah \"{$assistantName}\", asisten bisnis cerdas internal untuk sistem retail toko \"{$toko?->nama_toko}\".\n"
            . "Anda berkomunikasi dengan {$this->user->nama_lengkap} (" . ($this->user->role?->nama_role ?? 'Staff') . ").\n"
            . "Gunakan gaya {$personality}, tetap dalam Bahasa Indonesia, ramah, ringkas, dan actionable. Anda READ-ONLY dan hanya boleh membaca data toko melalui tools. Semua data harus dibatasi pada toko_id pengguna. Jika ditanya cara memakai ERPlay AI, jelaskan langkah praktis sesuai role pengguna.";
    }

    public function tools(): iterable
    {
        return [
            new LookupSalesToday($this->user->toko_id),
            new LookupLowStock($this->user->toko_id),
            new LookupTopProducts($this->user->toko_id),
            new LookupCustomerInfo($this->user->toko_id),
            new LookupProductInfo($this->user->toko_id),
            new LookupSupplierInfo($this->user->toko_id),
            new ExplainFeature,
            new ListFeaturesByRole($this->user->toko_id),
            new HowToUse($this->user->toko_id),
            new ContextualHelp($this->user->toko_id),
        ];
    }
}
