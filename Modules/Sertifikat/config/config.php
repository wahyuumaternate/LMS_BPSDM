<?php

return [
    'name' => 'Sertifikat',
    
    /*
    |--------------------------------------------------------------------------
    | Default Template
    |--------------------------------------------------------------------------
    */
    'default_template' => 'modern',
    
    /*
    |--------------------------------------------------------------------------
    | Templates Configuration
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'default' => [
            'name' => 'Template Default BPSDM',
            'background' => 'templates/backgrounds/default.jpg',
         'logo_bpsdm' => storage_path('public/logo.png'),
        'logo_pemda' => storage_path('public/logo.png'),
            'footer_text' => 'BPSDM MALUKU UTARA',
            'font' => [
                'family' => 'times',
                'title_size' => 28,
                'name_size' => 20,
                'body_size' => 12,
            ],
            'page' => [
                'orientation' => 'landscape',
                'unit' => 'mm',
                'format' => 'A4',
            ],
        ],
        
        'tema_2' => [
            'name' => 'Template Default BPSDM',
            'background' => 'templates/backgrounds/default.jpg',
         'logo_bpsdm' => storage_path('public/logo.png'),
        'logo_pemda' => storage_path('public/logo.png'),
            'footer_text' => 'BPSDM MALUKU UTARA',
            'font' => [
                'family' => 'times',
                'title_size' => 28,
                'name_size' => 20,
                'body_size' => 12,
            ],
            'page' => [
                'orientation' => 'landscape',
                'unit' => 'mm',
                'format' => 'A4',
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => 'public',
        'paths' => [
            'certificates' => 'certificates/pdf',
            'qrcodes' => 'certificates/qrcodes',
            'signatures' => 'certificates/signatures',
            'temp' => 'certificates/temp',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    */
    'qr_code' => [
        'enabled' => true,
        'size' => 100,
        'margin' => 10,
        'format' => 'png',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Verification
    |--------------------------------------------------------------------------
    */
    'verification' => [
        'enabled' => true,
        'base_url' => env('APP_URL') . '/verify-certificate/',
        'expires_days' => null, // null = never expires
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Certificate Number Format
    |--------------------------------------------------------------------------
    */
    'nomor_format' => [
        'prefix' => 'SERT',
        'separator' => '/',
        'year' => true,
        'counter_length' => 5, // 00001, 00002, etc.
        // Format: SERT/2025/00001
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    */
    'email' => [
        'enabled' => true,
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@bpsdm.go.id'),
            'name' => env('MAIL_FROM_NAME', 'BPSDM Sertifikat'),
        ],
        'subject' => 'Sertifikat Pelatihan - {nama_kursus}',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Default Signatories
    |--------------------------------------------------------------------------
    */
    'default_signatories' => [
        'penandatangan1' => [
            'nama' => 'Dr. Muhammad Taufiq, DEA',
            'jabatan' => 'Deputi Bidang Kebijakan Pengembangan Kompetensi ASN',
            'nip' => null,
            'signature_path' => null,
        ],
        'penandatangan2' => [
            'nama' => 'Erna Irawati, S.Sos., M.Pol.Adm',
            'jabatan' => 'Kepala Pusat Pembinaan Program dan Kebijakan Pengembangan Kompetensi ASN',
            'nip' => null,
            'signature_path' => null,
        ],
    ],
];