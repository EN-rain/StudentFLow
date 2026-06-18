<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo data
    |--------------------------------------------------------------------------
    |
    | Production installations should leave this disabled. Classes, schedules,
    | rooms, subjects, and teacher assignments are then created and maintained
    | by an administrator through the Classes page instead of being loaded from
    | the demo seeder.
    |
    */
    'seed_demo_data' => (bool) env('STUDENTFLOW_SEED_DEMO_DATA', false),

    'allow_demo_seed' => false,
];
