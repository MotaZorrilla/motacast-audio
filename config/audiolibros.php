<?php

return [
    'python_binary' => env('AUDIOLIBROS_PYTHON_BIN', PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3'),

    'timeouts' => [
        'extraction' => (int) env('AUDIOLIBROS_EXTRACTION_TIMEOUT', 1800),
        'tts' => (int) env('AUDIOLIBROS_TTS_TIMEOUT', 600),
    ],

    'storage_disk' => env('AUDIOLIBROS_STORAGE_DISK', 'local'),

    'uploads' => [
        'standard_max_kb' => 102400, // 100 MB
        'chunk_max_kb' => 25600,     // 25 MB per chunk
        'max_chunks' => 5000,
        'chunk_ttl_hours' => 24,
        'min_free_disk_bytes' => 512 * 1024 * 1024, // 512 MB safety buffer
        'rate_limit_per_minute' => 300,
        'preview_rate_limit_per_minute' => 12,
    ],

    'voices' => [
        [
            'id' => 'es-VE-SebastianNeural',
            'name' => 'Sebastián (Venezuela - Masculina)',
            'profile_label' => 'Sebastián (Venezuela - Masculina, Sonora y Convincente)',
            'locale' => 'es-VE',
            'gender' => 'Male',
            'recommended' => true,
        ],
        [
            'id' => 'es-VE-PaolaNeural',
            'name' => 'Paola (Venezuela - Femenina)',
            'profile_label' => 'Paola (Venezuela - Femenina, Clara y Expresiva)',
            'locale' => 'es-VE',
            'gender' => 'Female',
            'recommended' => false,
        ],
        [
            'id' => 'es-MX-DaliaNeural',
            'name' => 'Dalia (México - Femenina)',
            'profile_label' => 'Dalia (México - Femenina, Dulce y Didáctica)',
            'locale' => 'es-MX',
            'gender' => 'Female',
            'recommended' => false,
        ],
        [
            'id' => 'es-MX-JorgeNeural',
            'name' => 'Jorge (México - Masculina)',
            'profile_label' => 'Jorge (México - Masculina, Cálida y Neutral)',
            'locale' => 'es-MX',
            'gender' => 'Male',
            'recommended' => false,
        ],
        [
            'id' => 'es-ES-AlvaroNeural',
            'name' => 'Álvaro (España - Masculina)',
            'profile_label' => 'Álvaro (España - Masculina, Dinámica y Natural)',
            'locale' => 'es-ES',
            'gender' => 'Male',
            'recommended' => false,
        ],
        [
            'id' => 'es-ES-ElviraNeural',
            'name' => 'Elvira (España - Femenina)',
            'profile_label' => 'Elvira (España - Femenina, Clara y Expresiva)',
            'locale' => 'es-ES',
            'gender' => 'Female',
            'recommended' => false,
        ],
        [
            'id' => 'es-AR-TomasNeural',
            'name' => 'Tomás (Argentina - Masculina)',
            'profile_label' => 'Tomás (Argentina - Masculina, Urbana y Cercana)',
            'locale' => 'es-AR',
            'gender' => 'Male',
            'recommended' => false,
        ],
        [
            'id' => 'es-CO-GonzaloNeural',
            'name' => 'Gonzalo (Colombia - Masculina)',
            'profile_label' => 'Gonzalo (Colombia - Masculina, Académica)',
            'locale' => 'es-CO',
            'gender' => 'Male',
            'recommended' => false,
        ],
    ],

    'speed_rates' => ['-20%', '-15%', '-10%', '+0%', '+10%', '+15%', '+20%', '+25%', '+30%', '+35%'],
    'pitches' => ['-20Hz', '-10Hz', '+0Hz', '+10Hz', '+20Hz'],
];
