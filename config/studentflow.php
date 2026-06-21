<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Starter data
    |--------------------------------------------------------------------------
    |
    | Production installations should normally leave this disabled. When
    | enabled, realistic starter accounts, classrooms, subjects, schedules, and
    | enrollments are inserted into the backend database.
    |
    */
    'seed_starter_data' => (bool) env('STUDENTFLOW_SEED_STARTER_DATA', false),

    'allow_starter_seed' => false,

    'starter_passwords' => [
        'admin' => env('STUDENTFLOW_SEED_ADMIN_PASSWORD'),
        'teacher' => env('STUDENTFLOW_SEED_TEACHER_PASSWORD'),
        'student' => env('STUDENTFLOW_SEED_STUDENT_PASSWORD'),
    ],

    'android_app_cert_sha256' => env('ANDROID_APP_CERT_SHA256'),
];
