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
];
