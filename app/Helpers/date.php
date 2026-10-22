<?php

if (! function_exists('current_year')) {
    /**
     * Get the current 4-digit year.
     */
    function current_year(): string
    {
        return date('Y');
    }
}
