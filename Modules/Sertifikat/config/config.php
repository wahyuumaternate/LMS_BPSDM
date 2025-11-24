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
        
        'modern' => [
            'name' => 'Modern Certificate - BPSDM Malut',
            'description' => 'Template modern dengan desain profesional untuk BPSDM Maluku Utara',
            
            // Page settings
            'page' => [
                'format' => 'A4',
                'orientation' => 'landscape',
                'unit' => 'mm',
            ],
            
            // Colors
            'colors' => [
                'primary' => '#31694E',
                'secondary' => '#666666',
                'text' => '#333333',
            ],
            
            // Logo and assets
            'logo_bpsdm' => 'templates/logos/logo.png',
            'logo_pemda' => 'templates/logos/logo.png',
            'background' => null,
            
            // Text configuration
            'logo_text' => 'BPSDM',
            'institution_name' => 'BADAN PENGEMBANGAN SDM DAERAH',
            'right_logo_text' => 'MALUT',
            'right_logo_subtitle' => 'Pemerintah<br>Provinsi<br>Maluku Utara',
            
            'title' => 'SERTIFIKAT',
            'intro_text' => 'Diberikan kepada',
            'completion_text' => 'telah menyelesaikan pelatihan',
            
            // Organization
            'organizer' => 'Badan Pengembangan Sumber Daya Manusia Daerah Provinsi Maluku Utara',
            
            // Footer
            'footer_text' => 'BPSDM MALUKU UTARA',
            
            // Font (optional, untuk compatibility dengan template lama)
            'font' => [
                'family' => 'helvetica',
                'title_size' => 48,
                'name_size' => 52,
                'body_size' => 12,
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