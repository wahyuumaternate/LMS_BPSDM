<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat - {{ $nomor_sertifikat }}</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }
        
        /* Page setup - CRITICAL untuk DomPDF */
        @page {
            margin: 0;
            size: A4 landscape;
        }
        
        .certificate {
            width: 100%;
            height: 100%;
            position: relative;
            background: white;
        }
        
        /* Side Panels - Menggunakan absolute positioning yang compatible */
        .side-panel {
            position: absolute;
            width: 95px;
            height: 100%;
            background-color: {{ $config['colors']['primary'] ?? '#31694E' }};
            top: 0;
        }
        
        .side-left {
            left: 0;
        }
        
        .side-right {
            right: 0;
        }
        
        /* Main Container - menggunakan margin instead of flex */
        .content {
            position: relative;
            z-index: 10;
            margin: 0 130px;
            padding-top: 35px;
            padding-bottom: 35px;
        }
        
        /* Header - menggunakan table untuk layout */
        .header {
            width: 100%;
            margin-bottom: 18px;
        }
        
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo-section {
            vertical-align: top;
            text-align: left;
        }
        
        .logo-box {
            display: inline-block;
            width: 48px;
            height: 48px;
            background: {{ $config['colors']['primary'] ?? '#31694E' }};
            color: white;
            font-weight: bold;
            font-size: 20px;
            text-align: center;
            line-height: 48px;
            vertical-align: middle;
            margin-right: 12px;
        }
        
        .logo-img {
            width: 48px;
            height: 48px;
            vertical-align: middle;
            margin-right: 12px;
        }
        
        .institution-name {
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            letter-spacing: 1.5px;
            vertical-align: middle;
        }
        
        .gta-section {
            vertical-align: top;
            text-align: right;
        }
        
        .gta-logo {
            font-size: 32px;
            font-weight: bold;
            color: {{ $config['colors']['primary'] ?? '#31694E' }};
            line-height: 1;
            margin-bottom: 2px;
        }
        
        .gta-subtitle {
            font-size: 9px;
            color: #666;
            line-height: 1.3;
        }
        
        .right-logo-img {
            max-width: 100px;
            max-height: 60px;
        }
        
        /* Title Section */
        .title-section {
            text-align: center;
            margin-bottom: 12px;
            margin-top: 20px;
        }
        
        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            letter-spacing: 8px;
            margin-bottom: 5px;
        }
        
        .certificate-number {
            font-size: 11px;
            color: #666;
        }
        
        .decorative-line {
            width: 100px;
            height: 3px;
            background: {{ $config['colors']['primary'] ?? '#31694E' }};
            margin: 12px auto;
        }
        
        /* Main Content */
        .main-content {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .intro-text {
            font-size: 13px;
            color: #666;
            margin-bottom: 18px;
        }
        
        .participant-name {
            font-size: 45px;
            font-weight: bold;
            color: {{ $config['colors']['primary'] ?? '#31694E' }};
            letter-spacing: 3px;
            margin: 18px 0;
        }
        
        .detail-peserta {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .completion-text {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .training-info {
            margin-bottom: 18px;
        }
        
        .training-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .training-subtitle {
            font-size: 14px;
            font-weight: 600;
            color: {{ $config['colors']['primary'] ?? '#31694E' }};
            margin-bottom: 4px;
        }
        
        .training-program {
            font-size: 13px;
            color: #666;
            margin-bottom: 18px;
        }
        
        .training-details {
            font-size: 12px;
            color: #666;
            line-height: 1.5;
        }
        
        .training-details strong {
            color: #333;
        }
        
        /* Footer - menggunakan table untuk layout */
        .footer {
            width: 100%;
            margin-top: 40px;
        }
        
        .footer table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .location-date {
            font-size: 12px;
            color: #666;
            text-align: left;
            vertical-align: bottom;
        }
        
        .signature-section {
            text-align: center;
            vertical-align: bottom;
        }
        
        .qr-box {
            display: inline-block;
            border: 2px solid {{ $config['colors']['primary'] ?? '#31694E' }};
            padding: 8px;
            margin-bottom: 8px;
        }
        
        .qr-code {
            width: 65px;
            height: 65px;
            text-align: center;
        }
        
        .qr-code img {
            width: 65px;
            height: 65px;
        }
        
        .electronic-sign {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .signature-image {
            max-width: 120px;
            max-height: 60px;
            margin: 5px auto;
        }
        
        .signer-name {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        
        .signer-position {
            font-size: 10px;
            color: #666;
            line-height: 1.3;
        }
        
        .signer-nip {
            font-size: 9px;
            color: #888;
            margin-top: 2px;
        }
        
        /* Verification Footer */
        .verification-footer {
            position: absolute;
            bottom: 20px;
            left: 130px;
            right: 130px;
            text-align: center;
            font-size: 7px;
            color: #999;
            line-height: 1.5;
        }
        
        /* Dot Pattern Background */
    .dots-container {
        position: absolute;
        top: 50%;
        width: 180px;
        height: 180px;
        margin-top: -90px;
        opacity: 0.08;
        z-index: 1;
    }
    
    .dots-left {
        left: 110px;
    }
    
    .dots-right {
        right: 110px;
    }
    
    .dot {
        position: absolute;
        width: 6px;
        height: 6px;
        background-color: {{ $config['colors']['primary'] ?? '#31694E' }};
        border-radius: 50%;
    }

        /* ===== STYLES UNTUK HALAMAN MATERI ===== */
.materi-page {
    width: 100%;
    height: 100%;
    position: relative;
    background: white;
    page-break-before: always;
}

.materi-content {
    position: relative;
    z-index: 10;
    margin: 0 130px;
    padding-top: 50px;
    padding-bottom: 50px;
}

.materi-header {
    text-align: left;
    margin-bottom: 30px;
}

.materi-title {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.materi-subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 3px;
}

.materi-total {
    font-size: 13px;
    color: #666;
    font-weight: 600;
    margin-top: 5px;
}

/* Table Materi */
.materi-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.materi-table th {
    background-color: #f8f9fa;
    color: #333;
    font-size: 14px;
    font-weight: bold;
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #dee2e6;
}

.materi-table th:last-child {
    text-align: center;
    width: 150px;
}

.materi-table td {
    padding: 12px 15px;
    border: 1px solid #dee2e6;
    font-size: 13px;
    color: #555;
}

.materi-table td:last-child {
    text-align: center;
    font-weight: 600;
    color: #333;
}

.materi-table tr:nth-child(even) {
    background-color: #f8f9fa;
}

/* Modul Section */
.modul-section {
    margin-bottom: 25px;
}

.modul-title {
    font-size: 15px;
    font-weight: bold;
    color: #31694E;
    margin-bottom: 15px;
    padding: 10px 15px;
    background-color: #f0f8f5;
    border-left: 4px solid #31694E;
}


    </style>
</head>
<body>
    <div class="certificate">
        <!-- Side Panels -->
        <div class="side-panel side-left"></div>
        <div class="side-panel side-right"></div>
        
       <!-- Dot Patterns dengan div -->
<div class="dots-container dots-left">
    @php
        $dotSize = 6;
        $spacing = 20;
        $rows = 9;
        $cols = 9;
    @endphp
    @for($row = 0; $row < $rows; $row++)
        @for($col = 0; $col < $cols; $col++)
            <div class="dot" style="left: {{ $col * $spacing }}px; top: {{ $row * $spacing }}px;"></div>
        @endfor
    @endfor
</div>

<div class="dots-container dots-right">
    @for($row = 0; $row < $rows; $row++)
        @for($col = 0; $col < $cols; $col++)
            <div class="dot" style="left: {{ $col * $spacing }}px; top: {{ $row * $spacing }}px;"></div>
        @endfor
    @endfor
</div>
        
        <!-- Main Content -->
        <div class="content">
            <!-- Header menggunakan table -->
            <div class="header">
                <table>
                    <tr>
                        <td class="logo-section" width="60%">
                             @php
                    $logoPath = public_path('logo.png');
                    $qrcode = public_path('qr-code.png');
                @endphp
                                {{-- <img src="{{ $logoPath }}" alt="Logo" class="logo-img"> --}}
                            {{-- @if($logo_bpsdm && file_exists($logo_bpsdm))
                            @else
                                <div class="logo-box">{{ $config['logo_text'] ?? 'BPSDM' }}</div>
                            @endif --}}
                            <div class="institution-name">{{ $config['institution_name'] ?? 'BADAN PENGEMBANGAN SDM MALUKU UTARA' }}</div>
                        </td>
                        <td class="gta-section" width="40%">
                            <img src="{{ $logoPath }}" alt="Logo Pemda" class="right-logo-img">
                            {{-- @if($logo_pemda && file_exists($logo_pemda)) --}}
                            {{-- @else
                                <div class="gta-logo">{{ $config['right_logo_text'] ?? 'MALUT' }}</div>
                                <div class="gta-subtitle">
                                    {!! $config['right_logo_subtitle'] ?? 'Pemerintah<br>Provinsi<br>Maluku Utara' !!}
                                </div>
                            @endif --}}
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Title -->
            <div class="title-section">
                <div class="certificate-title">{{ $config['title'] ?? 'SERTIFIKAT' }}</div>
                <div class="certificate-number">{{ $nomor_sertifikat }}</div>
            </div>
            
            <div class="decorative-line"></div>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="intro-text">{{ $config['intro_text'] ?? 'Diberikan kepada' }}</div>
                
                <div class="participant-name">{{ $nama_peserta }}</div>
                
                @if($detail_peserta)
                    <div class="detail-peserta">{{ $detail_peserta }}</div>
                @endif
                
                <div class="completion-text">{{ $config['completion_text'] ?? 'telah menyelesaikan pelatihan' }}</div>
                
                <div class="training-info">
                    <div class="training-title">{{ $kursus->judul ?? $kursus->nama_kursus ?? 'Training Program' }}</div>
                    
                    @if(!empty($kursus->sub_judul) || !empty($config['training_subtitle']))
                        <div class="training-subtitle">{{ $kursus->sub_judul ?? $config['training_subtitle'] ?? '' }}</div>
                    @endif
                    
                    @if(!empty($kursus->program) || !empty($config['training_program']))
                        <div class="training-program">{{ $kursus->program ?? $config['training_program'] ?? '' }}</div>
                    @endif
                </div>
                
                <div class="training-details">
                    yang diselenggarakan oleh <strong>{{ $config['organizer'] ?? 'Badan Pengembangan Sumber Daya Manusia Maluku Utara' }}</strong>
                    @if(!empty($kursus->tanggal_mulai_kursus) && !empty($kursus->tanggal_selesai_kursus))
                        <br>pada tanggal <strong>{{ \Carbon\Carbon::parse($kursus->tanggal_mulai_kursus)->locale('id')->isoFormat('D MMMM') }} - {{ \Carbon\Carbon::parse($kursus->tanggal_selesai_kursus)->locale('id')->isoFormat('D MMMM YYYY') }}</strong>
                    @endif
                    @if(!empty($kursus->durasi_jam) || !empty($kursus->jumlah_jp))
                        selama <strong>{{ $kursus->durasi_jam ?? $kursus->jumlah_jp ?? '36' }} Jam Pelatihan</strong>
                    @endif
                </div>
            </div>
            
            <!-- Footer menggunakan table -->
            <div class="footer">
                
                <table>
                    <tr>
                        {{-- <td class="location-date" width="10%">
                            {{ $tanggal_terbit }}
                        </td> --}}
                        <td class="signature-section" width="400%">
                            <p class="training-details"> <small> {{ $tanggal_terbit }}</small></p>
                            <br>
                            <div class="qr-box">
                                <div class="qr-code">
                                    <img src="{{  $qrcode }}" alt="QR Code">
                                </div>
                            </div>
                            <div class="electronic-sign">Ditandatangani secara elektronik</div>
                            {{-- @if($qr_code && file_exists($qr_code))
                            @endif --}}
                            
                            @if(!empty($penandatangan1['signature']) && file_exists($penandatangan1['signature']))
                                <img src="{{ $penandatangan1['signature'] }}" alt="Tanda Tangan" class="signature-image">
                            @endif
                            
                            <div class="signer-name">{{ $penandatangan1['nama'] ?? '' }}</div>
                            <div class="signer-position">{{ $penandatangan1['jabatan'] ?? '' }}</div>
                            @if(!empty($penandatangan1['nip']))
                                <div class="signer-nip">NIP. {{ $penandatangan1['nip'] }}</div>
                            @endif
                           
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        {{-- <!-- Verification Footer -->
        @if(!empty($verification_url) || !empty($footer_text))
            <div class="verification-footer">
                @if(!empty($footer_text))
                    {!! nl2br(e($footer_text)) !!}<br>
                @else
                    Dokumen ini telah ditandatangani secara elektronik menggunakan<br>
                    sertifikat elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), Badan Siber dan Sandi Negara (BSSN).<br>
                @endif
                @if(!empty($verification_url))
                    Verifikasi: {{ $verification_url }}
                @endif
            </div>
        @endif --}}

      
    {{-- HALAMAN 2: MATERI PELATIHAN --}}
@php
    $moduls = $kursus->modul ?? collect();
    $hasMateri = false;
    $allMateris = collect(); // Kumpulkan semua materi dari semua modul
    $totalJP = 0;
    
    if($moduls && $moduls->count() > 0) {
        foreach($moduls as $modul) {
            if($modul->materis && $modul->materis->count() > 0) {
                $hasMateri = true;
                foreach($modul->materis as $materi) {
                    // Konversi menit ke JP (45 menit = 1 JP)
                    $durasi = $materi->jp ?? ($materi->durasi_menit ? round($materi->durasi_menit / 45, 1) : 0);
                    $totalJP += $durasi;
                    
                    // Tambahkan ke collection
                    $allMateris->push([
                        'judul' => $materi->judul_materi ?? 'Materi',
                        'jp' => $durasi
                    ]);
                }
            }
        }
    }
@endphp

@if($hasMateri)
<div class="materi-page">
    <div class="side-panel side-left"></div>
    <div class="side-panel side-right"></div>
    
    <!-- Dot Patterns -->
    <div class="dots-container dots-left">
        @php
            $dotSize = 6;
            $spacing = 20;
            $rows = 9;
            $cols = 9;
        @endphp
        @for($row = 0; $row < $rows; $row++)
            @for($col = 0; $col < $cols; $col++)
                <div class="dot" style="left: {{ $col * $spacing }}px; top: {{ $row * $spacing }}px;"></div>
            @endfor
        @endfor
    </div>

    <div class="dots-container dots-right">
        @for($row = 0; $row < $rows; $row++)
            @for($col = 0; $col < $cols; $col++)
                <div class="dot" style="left: {{ $col * $spacing }}px; top: {{ $row * $spacing }}px;"></div>
            @endfor
        @endfor
    </div>
    
    <div class="materi-content">
        <!-- Header -->
        <div class="header">
            <table>
                <tr>
                    <td class="logo-section" width="60%">
                        @php
                            $logoPath = public_path('logo.png');
                        @endphp
                       
                        <div class="institution-name">{{ $config['institution_name'] ?? 'BADAN PENGEMBANGAN SDM MALUKU UTARA' }}</div>
                    </td>
                    <td class="gta-section" width="40%">
                        @if(file_exists($logoPath))
                            <img src="{{ $logoPath }}" alt="Logo Pemda" class="right-logo-img">
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Materi Header -->
        <div class="materi-header">
            <div class="materi-title">Pelatihan {{ $kursus->judul ?? $kursus->nama_kursus }}</div>
            @if(!empty($kursus->sub_judul))
                <div class="materi-subtitle">{{ $kursus->sub_judul }}</div>
            @endif
            @if(!empty($kursus->program))
                <div class="materi-subtitle">{{ $kursus->program }}</div>
            @endif
            <div class="materi-total">Total {{ $totalJP > 0 ? $totalJP : ($kursus->durasi_jam ?? $kursus->jumlah_jp ?? '36') }} Jam Pelatihan</div>
        </div>
        
        <!-- Table Materi - SATU TABEL SAJA -->
        <table class="materi-table">
            <thead>
                <tr>
                    <th width="50px" style="text-align: center;">No</th>
                    <th>Materi</th>
                    <th width="100px" style="text-align: center;">JP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allMateris as $index => $materi)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $materi['judul'] }}</td>
                        <td style="text-align: center;">{{ $materi['jp'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
    </div>
</body>
</html>