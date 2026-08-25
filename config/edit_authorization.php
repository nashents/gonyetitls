<?php

/**
 * Registry of modules that support the "request one-off edit authorization"
 * workflow (App\Services\EditAuthorizationService). To add a new module:
 * implement App\Contracts\EditAuthorizable on its model, wire the lock
 * check + post-consume side effect into its Edit component (see
 * App\Http\Livewire\Bills\Edit for the pattern), then register it here.
 */
return [
    'trips' => [
        'label' => 'Trips',
        'model' => \App\Models\Trip::class,
        'route' => 'trips.edit',
    ],
    'bills' => [
        'label' => 'Bills',
        'model' => \App\Models\Bill::class,
        'route' => 'bills.edit',
    ],
];
