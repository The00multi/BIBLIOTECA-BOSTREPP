<?php

return [
    'admin_name' => env('BIBLIOTECA_ADMIN_NAME', 'Administración'),
    'admin_email' => env('BIBLIOTECA_ADMIN_EMAIL'),
    'admin_password' => env('BIBLIOTECA_ADMIN_PASSWORD'),
    'max_active_loans' => (int) env('BIBLIOTECA_MAX_ACTIVE_LOANS', 5),
    'loan_period_days' => (int) env('BIBLIOTECA_LOAN_PERIOD_DAYS', 14),
    'fine_per_day' => (float) env('BIBLIOTECA_FINE_PER_DAY', 0.50),
];
