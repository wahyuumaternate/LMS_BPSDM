<?php

return [

    'defaults' => [
        'guard' => 'admin_instruktur', // Ubah dari 'web' ke guard yang Anda gunakan
        'passwords' => 'admin_instruktur',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'admin_instruktur', // Ubah dari 'users'
        ],

        'admin_instruktur' => [
            'driver' => 'session',
            'provider' => 'admin_instruktur',
        ],

        'peserta' => [
            'driver' => 'sanctum',
            'provider' => 'peserta',
            'hash' => false,
        ],


        'api' => [
            'driver' => 'sanctum',
            'provider' => 'admin_instruktur', // Ubah dari 'users'
            'hash' => false,
        ],
    ],

    'providers' => [
        'admin_instruktur' => [
            'driver' => 'eloquent',
            'model' => Modules\AdminInstruktur\Entities\AdminInstruktur::class,
        ],
        'peserta' => [
            'driver' => 'eloquent',
            'model' => Modules\Peserta\Entities\Peserta::class,
        ],


        // Hapus atau comment provider 'users' jika tidak digunakan
        // 'users' => [
        //     'driver' => 'eloquent',
        //     'model' => App\Models\User::class,
        // ],
    ],

    'passwords' => [
        'admin_instruktur' => [
            'provider' => 'admin_instruktur',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'peserta' => [
            'provider' => 'peserta',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        // Hapus atau comment jika tidak digunakan
        // 'users' => [
        //     'provider' => 'users',
        //     'table' => 'password_reset_tokens',
        //     'expire' => 60,
        //     'throttle' => 60,
        // ],
    ],

    'password_timeout' => 10800,

];
