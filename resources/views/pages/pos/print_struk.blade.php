<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $penjualan->nomor_invoice }}</title>
    <style>
        :root {
            /* Toggle between 52mm (for 58mm roll) or 72mm (for 80mm roll) */
            --ticket-width: 52mm;
        }

        /* Browser Print Configuration Reset */
        @page { margin: 0; size: auto; }
        body { margin: 0; padding: 0; background: #fff; -webkit-print-color-adjust: exact; display: flex; justify-content: center; }

        /* Rigid Dimensions & Reset (Anti-Overflow) */
        .ticket {
            width: var(--ticket-width);
            max-width: var(--ticket-width);
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.2;
            word-break: break-all;
            color: #000;
        }

        /* Typography & Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        
        /* Header */
        .header { margin-bottom: 5px; }
        .header h3 { margin: 0 0 3px 0; font-size: 14px; font-weight: bold; }
        .header p { margin: 0; }

        /* Dividers */
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        /* Metadata Table */
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .meta-table td { vertical-align: top; padding: 1px 0; }
        .meta-label { width: 35%; }
        .meta-colon { width: 5%; }
        .meta-value { width: 60%; }

        /* Items Structural Layout */
        .item-row { margin-bottom: 4px; }
        .item-name { width: 100%; margin-bottom: 2px; }
        .item-details { display: flex; justify-content: space-between; }
        .item-subtotal { text-align: right; }

        /* Totals Block */
        .totals-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .totals-table td { padding: 2px 0; }
        
        .footer { margin-top: 8px; }
        
        /* Screen preview styling (optional, helps when viewing in browser before print dialog) */
        @media screen {
            body { padding: 20px; background: #f0f0f0; }
            .ticket { background: #fff; padding: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
        }
    </style>
</head>
<body>

<div class="ticket">
    <!-- Header -->
    <div class="header text-center">
        <h3>{{ $penjualan->toko->nama_toko ?? 'Toko Kelontong Jaya' }}</h3>
        <p>{{ $penjualan->toko->alamat ?? 'Jl. Raya Sejahtera No. 1' }}</p>
        <p>{{ $penjualan->toko->no_telepon ?? '08123456789' }}</p>
    </div>

    <div class="divider"></div>

    <!-- Metadata -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Tgl</td>
            <td class="meta-colon">:</td>
            <td class="meta-value">{{ $penjualan->created_at->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td>Inv</td>
            <td>:</td>
            <td>{{ $penjualan->nomor_invoice }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $penjualan->user->username ?? 'kasir' }}</td>
        </tr>
        <tr>
            <td>Plgn</td>
            <td>:</td>
            <td>{{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }} 
                @if($penjualan->pelanggan)
                    ({{ $penjualan->pelanggan->status_pelanggan }})
                @endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Items Grid -->
    <div>
        @foreach($penjualan->details as $item)
            <div class="item-row">
                <div class="item-name"><strong>{{ $item->produk->nama_produk ?? 'Unknown Item' }}</strong></div>
                <div class="item-details">
                    <span>{{ $item->qty }} pcs x {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                    <span class="item-subtotal"><strong>{{ number_format($item->subtotal, 0, ',', '.') }}</strong></span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="divider"></div>

    <!-- Totals Block -->
    <table class="totals-table">
        <tr>
            <td width="60%" class="text-left">Total</td>
            <td width="40%" class="text-right"><strong>{{ number_format($penjualan->total_harga, 0, ',', '.') }}</strong></td>
        </tr>
        @if($penjualan->diskon > 0)
        <tr>
            <td class="text-left">Diskon</td>
            <td class="text-right"><strong>-{{ number_format($penjualan->diskon, 0, ',', '.') }}</strong></td>
        </tr>
        @endif
        <tr>
            <td class="text-left bold" style="font-size: 13px;">Total Bayar</td>
            <td class="text-right bold" style="font-size: 13px;">{{ number_format($penjualan->total_bayar, 0, ',', '.') }}</td>
        </tr>
        
        @if(in_array($penjualan->metode_pembayaran, ['Tunai']))
        <tr>
            <td class="text-left">Tunai</td>
            <td class="text-right"><strong>{{ number_format($penjualan->nominal_uang, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="text-left">Kembali</td>
            <td class="text-right"><strong>{{ number_format($penjualan->kembalian, 0, ',', '.') }}</strong></td>
        </tr>
        @elseif($penjualan->metode_pembayaran === 'Kredit')
        <tr>
            <td class="text-left">Metode</td>
            <td class="text-right"><strong>Kredit ({{ $penjualan->status_pembayaran }})</strong></td>
        </tr>
        <tr>
            <td class="text-left">Uang Muka (DP)</td>
            <td class="text-right"><strong>{{ number_format($penjualan->uang_muka, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="text-left bold">Sisa Piutang</td>
            <td class="text-right bold"><strong>{{ number_format($penjualan->sisa_piutang, 0, ',', '.') }}</strong></td>
        </tr>
        @if($penjualan->tanggal_jatuh_tempo)
        <tr>
            <td class="text-left" style="font-size:10px;">Jatuh Tempo</td>
            <td class="text-right" style="font-size:10px;">{{ $penjualan->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
        </tr>
        @endif
        @else
        <tr>
            <td class="text-left">Metode</td>
            <td class="text-right"><strong>{{ $penjualan->metode_pembayaran }}</strong></td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- Footer -->
    <div class="footer text-center">
        <p class="bold" style="margin-bottom: 2px;">TERIMA KASIH</p>
        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
    </div>
</div>

<!-- Auto Print Script -->
<script>
    window.onload = function() {
        window.print();
    }
    window.onafterprint = function() {
        window.close();
    }
</script>
</body>
</html>
