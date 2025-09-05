<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Memory Management Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for managing memory usage in Assessment Wizard
    |
    */

    'memory_limit' => [
        // Memory limit for Assessment Wizard operations
        'assessment_wizard' => '512M',

        // Memory limit for bulk operations
        'bulk_operations' => '1G',

        // Memory limit for file uploads
        'file_uploads' => '256M',
    ],

    'query_optimization' => [
        // Maximum items to load in a single query
        'max_batch_size' => 100,

        // Enable query result caching
        'enable_caching' => true,

        // Cache TTL in seconds
        'cache_ttl' => 300, // 5 minutes

        // Enable eager loading optimization
        'optimize_eager_loading' => true,
    ],

    'performance' => [
        // Enable memory monitoring
        'monitor_memory' => true,

        // Memory threshold for warnings (in bytes)
        'memory_warning_threshold' => 100 * 1024 * 1024, // 100MB

        // Enable query logging for debugging
        'log_queries' => env('APP_DEBUG', false),

        // Maximum execution time for assessment operations
        'max_execution_time' => 300, // 5 minutes
    ],
];
