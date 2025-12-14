<!DOCTYPE html>
<html>
<head>
    <title>Tag Pesanan - {{ $pesanan->kode }}</title>
    <style>
        body { 
            font-family: monospace; 
            font-size: 12px; 
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px 0;
            display: flex;
            justify-content: center;
        }

        .ticket {
            width: 80mm;
            background-color: #fff;
            padding: 15px; /* Jarak pinggir kertas lebih lega */
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: left;
        }

        .header { 
            text-align: center; 
            margin-bottom: 15px; /* Jarak judul ke isi lebih jauh */
            border-bottom: 2px dashed #000; 
            padding-bottom: 10px; 
        }
        
        .header h2 { margin: 0; font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .header p { margin: 0; font-size: 11px; color: #555; }
        
        table { width: 100%; border-collapse: collapse; }
        
        /* INI YANG BIKIN TIDAK MEPET */
        td { 
            vertical-align: top; 
            padding: 6px 0; /* Jarak atas bawah per baris */
            border-bottom: 1px dotted #ddd; /* Garis tipis pembatas */
        }
        
        /* Hilangkan garis di baris terakhir */
        tr:last-child td { border-bottom: none; }

        .label { width: 35%; font-weight: bold; color: #333; }
        .sep { width: 5%; }
        .val { width: 60%; color: #000; }
        
        .footer { 
            margin-top: 15px; 
            border-top: 2px dashed #000; 
            padding-top: 10px; 
            text-align: center; 
            font-size: 11px; 
            font-weight: bold;
        }

        @media print {
            body { background-color: #fff; padding: 0; display: block; }
            .ticket {
                width: 100%; border: none; box-shadow: none; margin: 0; padding: 5px;
            }
            @page { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    
    <div class="ticket">
        <div class="header">
            <h2>LaundryKita</h2>
            <p>Tag Pesanan Pelanggan</p>
        </div>

        <table>
            <tr>
                <td class="label">Kode</td>
                <td class="sep">:</td>
                <td class="val" style="font-size: 14px; font-weight: bold;">{{ $pesanan->kode }}</td>
            </tr>
            <tr>
                <td class="label">Nama</td>
                <td class="sep">:</td>
                <td class="val"><strong>{{ $nama_fix }}</strong></td>
            </tr>
            <tr>
                <td class="label">Layanan</td>
                <td class="sep">:</td>
                <td class="val">{{ $layanan_fix }}</td>
            </tr>
            <tr>
                <td class="label">Total</td>
                <td class="sep">:</td>
                <td class="val">Rp{{ number_format($pesanan->total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="sep">:</td>
                <td class="val">{{ $pesanan->status }}</td>
            </tr>
            <tr>
                <td class="label">Bayar</td>
                <td class="sep">:</td>
                <td class="val">
                    {{ ($pesanan->status_pembayaran == 'lunas' || $pesanan->is_paid) ? 'Lunas' : 'Belum Lunas' }}
                </td>
            </tr>
            <tr>
                <td class="label">Tgl</td>
                <td class="sep">:</td>
                <td class="val">{{ $pesanan->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <div class="footer">
            ~~~ Terima Kasih ~~~
        </div>
    </div>

</body>
</html>