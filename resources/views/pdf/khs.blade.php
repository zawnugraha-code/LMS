<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Hasil Studi (KHS) — {{ $course?->code }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1A2E5E;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 14pt;
            margin: 0;
            color: #1A2E5E;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 11pt;
            font-weight: normal;
            margin: 2px 0 0 0;
            color: #475569;
        }
        .header p {
            font-size: 8.5pt;
            color: #64748b;
            margin: 4px 0 0 0;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 18px;
        }
        .doc-title h3 {
            margin: 0;
            font-size: 12pt;
            color: #1A2E5E;
            letter-spacing: 0.5px;
        }
        .doc-title span {
            font-size: 9pt;
            color: #64748b;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 18px;
            font-size: 9.5pt;
        }
        .info-grid td {
            padding: 3px 0;
            vertical-align: top;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 9.5pt;
        }
        .table-data th, .table-data td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            text-align: left;
        }
        .table-data th {
            background-color: #f8fafc;
            color: #1A2E5E;
            font-weight: bold;
            font-size: 9pt;
        }
        .table-data .text-center { text-align: center; }
        .table-data .text-right { text-align: right; }
        .score-box {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 18px;
        }
        .score-box table {
            width: 100%;
        }
        .cpmk-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1A2E5E;
            margin-bottom: 6px;
        }
        .signature-area {
            width: 100%;
            margin-top: 30px;
        }
        .signature-area td {
            vertical-align: top;
            text-align: center;
            font-size: 9pt;
        }
        .stamp {
            border: 1.5px dashed #2563EB;
            color: #2563EB;
            padding: 4px 8px;
            display: inline-block;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <div class="header">
        <h1>Universitas Nusantara Indonesia</h1>
        <h2>Fakultas Ilmu Komputer — Program Studi S1 Teknik Informatika</h2>
        <p>Jl. Boulevard Kampus Merdeka No. 101, Jakarta | Website: https://unus.ac.id | Email: baak@unus.ac.id</p>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">
        <h3>TRANSKRIP NILAI &amp; HASIL EVALUASI OBE MATA KULIAH</h3>
        <span>Tahun Akademik 2026/2027 — {{ $term?->name ?? 'Semester Ganjil 2026/2027' }}</span>
    </div>

    <!-- INFO MAHASISWA & MATA KULIAH -->
    <table class="info-grid">
        <tr>
            <td style="width: 18%;"><strong>Nama Mahasiswa</strong></td>
            <td style="width: 32%;">: {{ $student?->name ?? 'Arya Pratama' }}</td>
            <td style="width: 20%;"><strong>Mata Kuliah</strong></td>
            <td style="width: 30%;">: [{{ $course?->code }}] {{ $course?->name }}</td>
        </tr>
        <tr>
            <td><strong>NIM</strong></td>
            <td>: {{ $student?->identifier ?? '23060101024' }}</td>
            <td><strong>Bobot SKS</strong></td>
            <td>: {{ $course?->credits ?? 3 }} SKS</td>
        </tr>
        <tr>
            <td><strong>Program Studi</strong></td>
            <td>: S1 Teknik Informatika</td>
            <td><strong>Dosen Pengampu</strong></td>
            <td>: {{ $lecturer?->name ?? 'Prof. Dr. Budi Rahardjo, Ph.D.' }}</td>
        </tr>
        <tr>
            <td><strong>Status Kelulusan</strong></td>
            <td>: <strong style="color: #047857;">{{ $status_label ?? 'Lulus' }}</strong></td>
            <td><strong>Tanggal Terbit</strong></td>
            <td>: {{ date('d F Y') }}</td>
        </tr>
    </table>

    <!-- RINGKASAN NILAI AKHIR -->
    <div class="score-box">
        <table>
            <tr>
                <td style="text-align: center; width: 33%;">
                    <span style="font-size: 8.5pt; color: #64748b; text-transform: uppercase;">Nilai Angka</span><br>
                    <strong style="font-size: 16pt; color: #047857;">{{ $final_score }}</strong>
                </td>
                <td style="text-align: center; width: 33%; border-left: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1;">
                    <span style="font-size: 8.5pt; color: #64748b; text-transform: uppercase;">Nilai Huruf</span><br>
                    <strong style="font-size: 16pt; color: #1A2E5E;">{{ $letter_grade }}</strong>
                </td>
                <td style="text-align: center; width: 33%;">
                    <span style="font-size: 8.5pt; color: #64748b; text-transform: uppercase;">Bobot Indeks</span><br>
                    <strong style="font-size: 16pt; color: #2563EB;">{{ number_format($gpa_point, 2) }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- RINCIAN KOMPONEN PENILAIAN -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 6%;" class="text-center">No</th>
                <th style="width: 38%;">Komponen Asesmen Perkuliahan</th>
                <th style="width: 14%;" class="text-center">Bobot</th>
                <th style="width: 18%;" class="text-center">Skor Mentah</th>
                <th style="width: 24%;" class="text-right">Kontribusi Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($components as $idx => $comp)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $comp['name'] }}</strong><br>
                        <span style="font-size: 8pt; color: #64748b;">{{ $comp['items_count'] }}</span>
                    </td>
                    <td class="text-center">{{ $comp['weight'] }}%</td>
                    <td class="text-center">{{ number_format($comp['score'], 2) }}</td>
                    <td class="text-right"><strong>{{ number_format($comp['contribution'], 2) }}</strong></td>
                </tr>
            @endforeach
            <tr style="background-color: #f8fafc;">
                <td colspan="4" class="text-right"><strong>Total Nilai Akhir Terhitung:</strong></td>
                <td class="text-right"><strong style="font-size: 11pt; color: #047857;">{{ $final_score }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- KETERCAPAIAN CPMK (OBE) -->
    <div class="cpmk-title">Ketercapaian Capaian Pembelajaran Mata Kuliah (CPMK):</div>
    <table class="table-data" style="margin-bottom: 24px;">
        <thead>
            <tr>
                <th style="width: 15%;">Kode</th>
                <th style="width: 55%;">Deskripsi Kompetensi</th>
                <th style="width: 15%;" class="text-center">Capaian</th>
                <th style="width: 15%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cpmks as $cp)
                <tr>
                    <td><strong>{{ $cp['code'] }}</strong></td>
                    <td style="font-size: 8.5pt;">{{ $cp['description'] }}</td>
                    <td class="text-center"><strong>{{ $cp['mastery'] }}%</strong></td>
                    <td class="text-center"><span style="color: #047857; font-weight: bold; font-size: 8pt;">{{ $cp['status'] }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TANDA TANGAN & PENGESAHAN RESMI -->
    <table class="signature-area">
        <tr>
            <td style="width: 45%;">
                Mengetahui,<br>
                Ketua Program Studi S1 Teknik Informatika<br><br><br><br>
                <strong>Dr. Rudi Rahardjo, S.T., M.Kom.</strong><br>
                NIP. 19780512 200501 1 002
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%;">
                Jakarta, {{ date('d F Y') }}<br>
                Dosen Pengampu Utama<br><br><br><br>
                <strong>{{ $lecturer?->name ?? 'Prof. Dr. Budi Rahardjo, Ph.D.' }}</strong><br>
                NIDN. {{ $lecturer?->identifier ?? '0012057801' }}<br>
                <div class="stamp">Verified Digital Signature &bull; Arunika LMS</div>
            </td>
        </tr>
    </table>

</body>
</html>
