<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Paths
    |--------------------------------------------------------------------------
    |
    | This is the array of paths that Blade will look in when compiling
    | templates. You may add as many as you like and templates in any
    | of them will be available to the view renderers.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This is the path where compiled Blade templates are stored. Compiled
    | templates improve performance because they are only compiled once.
    |
    */

    'compiled' => realpath(storage_path('framework/views')),

];
