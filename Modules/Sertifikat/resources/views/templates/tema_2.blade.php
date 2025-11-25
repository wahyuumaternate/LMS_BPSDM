<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat - {{ $nomor_sertifikat }}</title>
    <style>
        @page { margin: 0; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: white;
        }
        
        /* ===== HALAMAN 1: SERTIFIKAT ===== */
        .certificate {
            width: 100%;
            height: 100%;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        }
        
        .wave-bg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .card {
            position: absolute;
            top: 25px;
            left: 25px;
            right: 25px;
            bottom: 25px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 3px solid rgba(102, 126, 234, 0.3);
        }
        
        .accent-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(to right, #667eea, #764ba2, #f093fb);
            border-radius: 20px 20px 0 0;
        }
        
        .content {
            position: relative;
            z-index: 10;
            padding: 35px 80px;
            text-align: center;
        }
        
        /* Header dengan table untuk logo dan institusi */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .badge {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            padding: 5px;
            display: inline-block;
            border: 3px solid #667eea;
        }
        
        .badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .header-text {
            font-size: 11px;
            color: #667eea;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .main-title {
            font-size: 44px;
            font-weight: 900;
            color: #667eea;
            letter-spacing: 8px;
            margin: 12px 0;
        }
        
        .cert-id {
            font-size: 10px;
            color: #999;
            letter-spacing: 1px;
            font-family: 'Courier New', monospace;
        }
        
        .divider-gradient {
            width: 150px;
            height: 3px;
            background: linear-gradient(to right, transparent, #667eea, #764ba2, #f093fb, transparent);
            margin: 15px auto;
            border-radius: 2px;
        }
        
        .intro-text {
            font-size: 12px;
            color: #666;
            margin: 15px 0;
        }
        
        .participant-name {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin: 15px 0;
            line-height: 1.2;
        }
        
        .participant-detail {
            font-size: 10px;
            color: #999;
            margin-bottom: 15px;
        }
        
        .achievement-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            padding: 16px 50px;
            border-radius: 12px;
            margin: 15px 0;
        }
        
        .achievement-text {
            font-size: 11px;
            color: #444;
            line-height: 1.8;
        }
        
        .course-title {
            font-size: 16px;
            font-weight: bold;
            color: #764ba2;
            margin: 6px 0;
            display: inline;
        }
        
        .footer-layout {
            margin-top: 20px;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .location-col {
            width: 35%;
            text-align: left;
            vertical-align: bottom;
            padding-left: 20px;
            padding-bottom: 10px;
        }
        
        .signature-col {
            width: 30%;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 10px;
        }
        
        .qr-signature-col {
            width: 35%;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 10px;
        }
        
        .qr-signature-wrapper {
            display: inline-block;
            text-align: center;
        }
        
        .qr-modern {
            width: 70px;
            height: 70px;
            border: 3px solid #667eea;
            border-radius: 12px;
            padding: 5px;
            background: white;
            margin: 0 auto 8px auto;
        }
        
        .qr-modern img {
            width: 100%;
            height: 100%;
        }
        
        .signature-gradient-line {
            width: 160px;
            height: 3px;
            background: linear-gradient(to right, #667eea, #764ba2);
            margin: 3px auto;
            border-radius: 2px;
        }
        
        .signature-image {
            max-width: 110px;
            max-height: 50px;
            margin-bottom: 3px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        .signer-name {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin: 3px 0 2px 0;
            text-transform: uppercase;
            line-height: 1.2;
        }
        
        .signer-role {
            font-size: 10px;
            color: #666;
            line-height: 1.2;
        }
        
        .signer-nip {
            font-size: 9px;
            color: #999;
            margin-top: 2px;
        }
        
        .location-text {
            font-size: 11px;
            color: #666;
        }
        
        .circle-deco {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
        }
        
        .circle-1 {
            width: 250px;
            height: 250px;
            background: #667eea;
            top: -125px;
            right: -125px;
        }
        
        .circle-2 {
            width: 180px;
            height: 180px;
            background: #f093fb;
            bottom: -90px;
            left: -90px;
        }
        
        /* ===== HALAMAN 2: MATERI ===== */
        .materi-page {
            page-break-before: always;
            width: 100%;
            height: 100%;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        }
        
        .materi-content {
            position: relative;
            z-index: 10;
            padding: 40px 70px;
        }
        
        .materi-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .materi-badge-cell {
            width: 50px;
            vertical-align: middle;
        }
        
        .materi-badge {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            padding: 4px;
            border: 2px solid #667eea;
        }
        
        .materi-badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .materi-institution-cell {
            vertical-align: middle;
            padding-left: 15px;
        }
        
        .materi-institution {
            font-size: 11px;
            color: white;
            font-weight: 600;
            letter-spacing: 2px;
        }
        
        .materi-title-box {
            background: white;
            padding: 18px 25px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        
        .materi-title {
            font-size: 17px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            display: inline;
        }
        
        .materi-subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .materi-total {
            font-size: 12px;
            color: #667eea;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .materi-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .materi-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .materi-table th {
            color: white;
            font-size: 12px;
            font-weight: 600;
            padding: 12px 15px;
            text-align: left;
            letter-spacing: 0.5px;
        }
        
        .materi-table th:first-child {
            text-align: center;
            width: 60px;
        }
        
        .materi-table th:last-child {
            text-align: center;
            width: 90px;
        }
        
        .materi-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
            color: #444;
        }
        
        .materi-table td:first-child {
            text-align: center;
            font-weight: 600;
            color: #667eea;
        }
        
        .materi-table td:last-child {
            text-align: center;
            font-weight: 600;
            color: #764ba2;
        }
        
        .materi-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .materi-table tbody tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
    {{-- HALAMAN 1: SERTIFIKAT --}}
    <div class="certificate">
        <div class="wave-bg"></div>
        
        <div class="card">
            <div class="accent-bar"></div>
            
            <div class="circle-deco circle-1"></div>
            <div class="circle-deco circle-2"></div>
            
            <div class="content">
                @php 
                    $logoPath = public_path('logo.png');
                    $qrcode = public_path('qr-code.png');
                @endphp
                
                {{-- Header dengan table --}}
                <table class="header-table">
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <div class="badge">
                                @if(file_exists($logoPath))
                                    <img src="{{ $logoPath }}" alt="Logo">
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <div class="header-text">{{ $config['institution_name'] ?? 'BADAN PENGEMBANGAN SDM DAERAH' }}</div>
                        </td>
                    </tr>
                </table>
                
                <div class="main-title">SERTIFIKAT</div>
                <div class="cert-id">{{ $nomor_sertifikat }}</div>
                
                <div class="divider-gradient"></div>
                
                <div class="intro-text">{{ $config['intro_text'] ?? 'Diberikan kepada' }}</div>
                
                <div class="participant-name">{{ $nama_peserta }}</div>
                @if($detail_peserta)
                    <div class="participant-detail">{{ $detail_peserta }}</div>
                @endif
                
                <div class="achievement-box">
                    <div class="achievement-text">
                        {{ $config['completion_text'] ?? 'telah menyelesaikan pelatihan' }}
                    </div>
                    <div class="course-title">{{ $kursus->judul ?? $kursus->nama_kursus }}</div>
                    @if(!empty($kursus->sub_judul))
                        <div class="achievement-text" style="margin-top: 3px;">{{ $kursus->sub_judul }}</div>
                    @endif
                    <div class="achievement-text" style="margin-top: 8px;">
                        yang diselenggarakan oleh <strong>{{ $config['organizer'] ?? 'Badan Pengembangan SDM Daerah' }}</strong>
                        @if(!empty($kursus->tanggal_mulai_kursus) && !empty($kursus->tanggal_selesai_kursus))
                            <br>{{ \Carbon\Carbon::parse($kursus->tanggal_mulai_kursus)->locale('id')->isoFormat('D MMMM') }} - 
                            {{ \Carbon\Carbon::parse($kursus->tanggal_selesai_kursus)->locale('id')->isoFormat('D MMMM YYYY') }}
                        @endif
                        @if(!empty($kursus->durasi_jam))
                            | {{ $kursus->durasi_jam }} Jam Pelatihan
                        @endif
                    </div>
                </div>
                
                <div class="footer-layout">
                    <table class="footer-table">
                        <tr>
                          
                            <td class="qr-signature-col">
                                <div class="qr-signature-wrapper">
                                     <p class="training-details"> <small> {{ $tanggal_terbit }}</small></p>
                            <br>
                                    @if(file_exists($qrcode))
                                        <div class="qr-modern">
                                            <img src="{{ $qrcode }}" alt="QR">
                                        </div>
                                    @endif
                                    <div style="font-size: 9px; color: #999; font-style: italic; margin-top: 5px;">
                                        Ditandatangani secara elektronik
                                    </div>
                                   
                                    {{-- <div class="signature-gradient-line"></div> --}}
                                    <div class="signer-name">{{ $penandatangan1['nama'] ?? '' }}</div>
                                    <div class="signer-role">{{ $penandatangan1['jabatan'] ?? '' }}</div>
                                    @if(!empty($penandatangan1['nip']))
                                        <div class="signer-nip">NIP. {{ $penandatangan1['nip'] }}</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- HALAMAN 2: MATERI --}}
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
        <div class="wave-bg"></div>
        
        <div class="card">
            <div class="accent-bar"></div>
            
            <div class="circle-deco circle-1"></div>
            <div class="circle-deco circle-2"></div>
            
            <div class="materi-content">
                <table class="materi-header-table">
                    <tr>
                        <td class="materi-badge-cell">
                            <div class="materi-badge">
                                @if(file_exists($logoPath))
                                    <img src="{{ $logoPath }}" alt="Logo">
                                @endif
                            </div>
                        </td>
                        <td class="materi-institution-cell">
                            <div class="materi-institution">{{ $config['institution_name'] ?? 'BADAN PENGEMBANGAN SDM DAERAH' }}</div>
                        </td>
                    </tr>
                </table>
                
                <div class="materi-title-box">
                    <div class="materi-title">{{ $kursus->judul ?? $kursus->nama_kursus }}</div>
                    @if(!empty($kursus->sub_judul))
                        <div class="materi-subtitle" style="margin-top: 5px;">{{ $kursus->sub_judul }}</div>
                    @endif
                    @if(!empty($kursus->program))
                        <div class="materi-subtitle">{{ $kursus->program }}</div>
                    @endif
                    <div class="materi-total">Total {{ $totalJP > 0 ? $totalJP : ($kursus->durasi_jam ?? '36') }} Jam Pelatihan</div>
                </div>
                
                <table class="materi-table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>MATERI PELATIHAN</th>
                            <th>JP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allMateris as $index => $materi)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $materi['judul'] }}</td>
                                <td>{{ $materi['jp'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</body>
</html>