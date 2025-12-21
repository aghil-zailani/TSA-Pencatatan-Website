<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 40px; 
        }
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-right: 20px;
        }

        .company-info {
            flex: 1;
            text-align: center;
        }

        .company-info h2 {
            margin: 0;
            font-size: 16px;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 11px;
        }

        h3 {
            text-align: center;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
        .footer {
            position: fixed;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }
        .footer hr {
            border: none;
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <img src="{{ public_path('logo/tsa.png') }}" alt="Logo" class="logo">
        <div class="company-info">
            <h2>PT. Tunas Siak Anugrah</h2>
            <p>Jl. Tengku Maharatu I Blok D No.05 Maharani, Rumbai Pekanbaru – RIAU 28264</p>
            <p>Tlp : 0811 7604 545 - 0811 576 976</p>
        </div>
    </div>

    <!-- TITLE -->
    <h3>Data Barang beserta Stok Barang pada Gudang</h3>

    <!-- INFO -->
    <p><strong>Monitoring Stok Barang</strong></p>
    <p>Total Barang Keseluruhan: {{ $totalKeseluruhanBarang }}</p>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Jenis Barang</th>
                <th>Total Stok</th>
                <th>Berat</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach ($barangAggregated as $item)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->tipe_barang }}</td>
                    <td>{{ $item->total_stok }}</td>
                    <td>{{ number_format($item->berat_barang, 2) }} kg</td>
                    <td>{{ number_format($item->harga_beli, 2) }}</td>
                    <td>{{ number_format($item->harga_jual, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <hr>
        <p>PT. Tunas Siak Anugrah</p>
        <p>Jl. Tengku Maharatu I Blok D No.05 Maharani, Rumbai Pekanbaru – RIAU 28264 | Tlp : 0811 7604 545 - 0811 576 976</p>
    </div>

</body>
</html>
