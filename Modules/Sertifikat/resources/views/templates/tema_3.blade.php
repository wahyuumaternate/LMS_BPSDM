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
        
        /* Page setup */
        @page {
            margin: 0;
            size: A4 landscape;
        }
        
        .certificate {
            width: 100%;
            height: 100%;
            position: relative;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }
        
        /* Border Frame - Modern Geometric */
        .border-frame {
            position: absolute;
            border: 3px solid {{ $config['colors']['primary'] ?? '#05339C' }};
            top: 25px;
            left: 25px;
            right: 25px;
            bottom: 25px;
        }
        
        .border-inner {
            position: absolute;
            border: 1px solid {{ $config['colors']['primary'] ?? '#05339C' }};
            top: 35px;
            left: 35px;
            right: 35px;
            bottom: 35px;
            opacity: 0.3;
        }
        
        /* Corner Decorations */
        .corner-decoration {
            position: absolute;
            width: 60px;
            height: 60px;
            border: 2px solid {{ $config['colors']['primary'] ?? '#05339C' }};
        }
        
        .corner-tl {
            top: 15px;
            left: 15px;
            border-right: none;
            border-bottom: none;
        }
        
        .corner-tr {
            top: 15px;
            right: 15px;
            border-left: none;
            border-bottom: none;
        }
        
        .corner-bl {
            bottom: 15px;
            left: 15px;
            border-right: none;
            border-top: none;
        }
        
        .corner-br {
            bottom: 15px;
            right: 15px;
            border-left: none;
            border-top: none;
        }
        
        /* Main Container */
        .content {
            position: relative;
            z-index: 10;
            margin: 0 80px;
            padding-top: 50px;
            padding-bottom: 40px;
        }
        
        /* Header */
        .header {
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo-section {
            vertical-align: middle;
            text-align: left;
        }
        
        .logo-box {
            display: inline-block;
            width: 55px;
            height: 55px;
            background: {{ $config['colors']['primary'] ?? '#05339C' }};
            color: white;
            font-weight: bold;
            font-size: 22px;
            text-align: center;
            line-height: 55px;
            vertical-align: middle;
            margin-right: 15px;
            border-radius: 3px;
        }
        
        .logo-img {
            width: 55px;
            height: 55px;
            vertical-align: middle;
            margin-right: 15px;
        }
        
        .institution-name {
            display: inline-block;
            font-size: 15px;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: 2px;
            vertical-align: middle;
            text-transform: uppercase;
        }
        
        .gta-section {
            vertical-align: middle;
            text-align: right;
        }
        
        .gta-logo {
            font-size: 36px;
            font-weight: bold;
            color: {{ $config['colors']['primary'] ?? '#05339C' }};
            line-height: 1;
            margin-bottom: 3px;
        }
        
        .gta-subtitle {
            font-size: 10px;
            color: #666;
            line-height: 1.4;
        }
        
        .right-logo-img {
            max-width: 110px;
            max-height: 65px;
        }
        
        /* Title Section */
        .title-section {
            text-align: center;
            margin-bottom: 12px;
            margin-top: 12px;
            position: relative;
        }
        
        .certificate-title {
            font-size: 50px;
            font-weight: 900;
            color: {{ $config['colors']['primary'] ?? '#05339C' }};
            letter-spacing: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(5, 51, 156, 0.1);
        }
        
        .certificate-number {
            font-size: 11px;
            color: #888;
            letter-spacing: 2px;
            font-weight: 500;
        }
        
        .decorative-line {
            width: 150px;
            height: 4px;
            background: linear-gradient(90deg, transparent, {{ $config['colors']['primary'] ?? '#05339C' }}, transparent);
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
            color: #555;
            margin-bottom: 16px;
            font-style: italic;
            letter-spacing: 1px;
        }
        
        .participant-name {
            font-size: 25px;
            font-weight: 900;
            color: #1a1a1a;
            letter-spacing: 3px;
            margin: 14px 0;
            text-transform: uppercase;
            border-bottom: 3px solid {{ $config['colors']['primary'] ?? '#05339C' }};
            display: inline-block;
            padding-bottom: 6px;
        }
        
        .detail-peserta {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
            font-style: italic;
        }
        
        .completion-text {
            font-size: 13px;
            color: #555;
            margin-bottom: 16px;
            margin-top: 10px;
        }
        
        .training-info {
            margin-bottom: 14px;
            background: rgba(5, 51, 156, 0.03);
            padding: 14px;
            border-left: 4px solid {{ $config['colors']['primary'] ?? '#05339C' }};
            border-right: 4px solid {{ $config['colors']['primary'] ?? '#05339C' }};
        }
        
        .training-title {
            font-size: 17px;
            font-weight: 800;
            color: {{ $config['colors']['primary'] ?? '#05339C' }};
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .training-subtitle {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        
        .training-program {
            font-size: 12px;
            color: #666;
            margin-bottom: 16px;
            font-style: italic;
        }
        
        .training-details {
            font-size: 11px;
            color: #555;
            line-height: 1.6;
        }
        
        .training-details strong {
            color: {{ $config['colors']['primary'] ?? '#05339C' }};
            font-weight: 700;
        }
        
        /* Footer */
        .footer {
            width: 100%;
            margin-top: 20px;
        }
        
        .footer table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .location-date {
            font-size: 12px;
            color: #555;
            text-align: left;
            vertical-align: bottom;
            font-weight: 600;
        }
        
        .signature-section {
            text-align: center;
            vertical-align: bottom;
        }
        
        .qr-box {
            display: inline-block;
            border: 3px solid {{ $config['colors']['primary'] ?? '#05339C' }};
            padding: 8px;
            margin-bottom: 8px;
            background: white;
            border-radius: 3px;
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
            font-size: 9px;
            color: #666;
            margin-bottom: 6px;
            font-style: italic;
        }
        
        .signature-image {
            max-width: 110px;
            max-height: 55px;
            margin: 6px auto;
        }
        
        .signer-name {
            font-size: 14px;
            font-weight: 900;
            color: {{ $config['colors']['primary'] ?? '#05339C' }};
            text-transform: uppercase;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }
        
        .signer-position {
            font-size: 11px;
            color: #555;
            line-height: 1.4;
            font-weight: 600;
        }
        
        .signer-nip {
            font-size: 9px;
            color: #888;
            margin-top: 3px;
        }
        
        /* Verification Footer */
        .verification-footer {
            position: absolute;
            bottom: 20px;
            left: 80px;
            right: 80px;
            text-align: center;
            font-size: 7px;
            color: #999;
            line-height: 1.5;
        }
        
        /* Geometric Pattern Background */
        .geometric-container {
            position: absolute;
            width: 200px;
            height: 200px;
            opacity: 0.04;
            z-index: 1;
        }
        
        .geometric-left {
            top: 50%;
            left: 50px;
            margin-top: -100px;
        }
        
        .geometric-right {
            top: 50%;
            right: 50px;
            margin-top: -100px;
        }
        
        .geo-shape {
            position: absolute;
            border: 2px solid {{ $config['colors']['primary'] ?? '#05339C' }};
        }
        
        .geo-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            top: 60px;
            left: 60px;
        }
        
        .geo-square {
            width: 100px;
            height: 100px;
            top: 50px;
            left: 50px;
            transform: rotate(45deg);
        }
        
        .geo-triangle {
            width: 0;
            height: 0;
            border-left: 50px solid transparent;
            border-right: 50px solid transparent;
            border-bottom: 87px solid {{ $config['colors']['primary'] ?? '#05339C' }};
            opacity: 0.5;
            top: 20px;
            left: 50px;
        }

        /* ===== STYLES UNTUK HALAMAN MATERI ===== */
        .materi-page {
            width: 100%;
            height: 100%;
            position: relative;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            page-break-before: always;
        }

        .materi-content {
            position: relative;
            z-index: 10;
            margin: 0 80px;
            padding-top: 65px;
            padding-bottom: 55px;
        }

        .materi-header {
            text-align: center;
            margin-bottom: 35px;
            padding: 25px;
            background: linear-gradient(135deg, rgba(5, 51, 156, 0.05), rgba(5, 51, 156, 0.02));
            border-top: 3px solid {{ $config['colors']['primary'] ?? '#05339C' }};
            border-bottom: 3px solid {{ $config['colors']['primary'] ?? '#05339C' }};
        }

        .materi-title {
            font-size: 20px;
            font-weight: 900;
            color: {{ $config['colors']['primary'] ?? '#05339C' }};
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .materi-subtitle {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .materi-total {
            font-size: 14px;
            color: {{ $config['colors']['primary'] ?? '#05339C' }};
            font-weight: 800;
            margin-top: 8px;
            letter-spacing: 1px;
        }

        /* Table Materi */
        .materi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .materi-table th {
            background: linear-gradient(135deg, {{ $config['colors']['primary'] ?? '#05339C' }}, #0644b8);
            color: white;
            font-size: 14px;
            font-weight: 800;
            padding: 15px;
            text-align: left;
            border: none;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .materi-table th:last-child {
            text-align: center;
            width: 120px;
        }

        .materi-table td {
            padding: 14px 15px;
            border: 1px solid #e8e8e8;
            font-size: 13px;
            color: #444;
            background: white;
        }

        .materi-table td:last-child {
            text-align: center;
            font-weight: 700;
            color: {{ $config['colors']['primary'] ?? '#05339C' }};
            font-size: 14px;
        }

        .materi-table tr:hover {
            background-color: rgba(5, 51, 156, 0.02);
        }

        .materi-table tbody tr:nth-child(even) {
            background-color: #fafbff;
        }

        /* Modul Section */
        .modul-section {
            margin-bottom: 30px;
        }

        .modul-title {
            font-size: 16px;
            font-weight: 800;
            color: white;
            margin-bottom: 18px;
            padding: 12px 20px;
            background: linear-gradient(135deg, {{ $config['colors']['primary'] ?? '#05339C' }}, #0644b8);
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <!-- Border Frames -->
        <div class="border-frame"></div>
        <div class="border-inner"></div>
        
        <!-- Corner Decorations -->
        <div class="corner-decoration corner-tl"></div>
        <div class="corner-decoration corner-tr"></div>
        <div class="corner-decoration corner-bl"></div>
        <div class="corner-decoration corner-br"></div>
        
        <!-- Geometric Patterns -->
        <div class="geometric-container geometric-left">
            <div class="geo-shape geo-square"></div>
            <div class="geo-shape geo-circle"></div>
        </div>
        
        <div class="geometric-container geometric-right">
            <div class="geo-shape geo-square"></div>
            <div class="geo-shape geo-circle"></div>
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
                        {{-- <td class="location-date" width="40%">
                            {{ $tanggal_terbit }}
                        </td> --}}
                        
                        <td class="signature-section" width="60%">
                             <p class="training-details"> <small> {{ $tanggal_terbit }}</small></p>
                            <br>
                            <div class="qr-box">
                                <div class="qr-code">
                                    <img src="{{ $qrcode }}" alt="QR Code">
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
        
       

        {{-- HALAMAN 2: MATERI PELATIHAN --}}
        @php
            $moduls = $kursus->modul ?? collect();
            $hasMateri = false;
            $allMateris = collect();
            $totalJP = 0;
            
            if($moduls && $moduls->count() > 0) {
                foreach($moduls as $modul) {
                    if($modul->materis && $modul->materis->count() > 0) {
                        $hasMateri = true;
                        foreach($modul->materis as $materi) {
                            $durasi = $materi->jp ?? ($materi->durasi_menit ? round($materi->durasi_menit / 45, 1) : 0);
                            $totalJP += $durasi;
                            
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
            <div class="border-frame"></div>
            <div class="border-inner"></div>
            
            <!-- Corner Decorations -->
            <div class="corner-decoration corner-tl"></div>
            <div class="corner-decoration corner-tr"></div>
            <div class="corner-decoration corner-bl"></div>
            <div class="corner-decoration corner-br"></div>
            
            <!-- Geometric Patterns -->
            <div class="geometric-container geometric-left">
                <div class="geo-shape geo-square"></div>
                <div class="geo-shape geo-circle"></div>
            </div>
            
            <div class="geometric-container geometric-right">
                <div class="geo-shape geo-square"></div>
                <div class="geo-shape geo-circle"></div>
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
                
                <!-- Table Materi -->
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