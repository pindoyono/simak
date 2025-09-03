<?php

use Maatwebsite\Excel\Excel;

return [
    'exports' => [
        'chunk_size' => 1000,
        'pre_calculate_formulas' => false,
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
        ],
        'properties' => [
            'creator' => 'SIMAK System',
            'company' => 'Education Department',
        ],
    ],

    'imports' => [
        'read_only' => true,
        'ignore_empty' => true,
        'heading_row' => [
            'formatter' => 'slug',
        ],
    ],

    'extension_detector' => [
        'xlsx' => Excel::XLSX,
        'xls' => Excel::XLS,
        'csv' => Excel::CSV,
    ],

    'value_binder' => [
        'default' => \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder::class,
    ],
];
