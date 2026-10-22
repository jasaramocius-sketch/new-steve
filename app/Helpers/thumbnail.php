<?php

if (! function_exists('thumbUrl')) {
    function thumbUrl(?string $image): string
    {
        if (empty($image)) {
            return 'assets/images/placeholder.png';
        }
        $path = 'assets/images/thumbnails/'.$image;

        return $path;
    }
}

if (! function_exists('thumbUrlAsset')) {
    function thumbUrlAsset(?string $image): string
    {
        return asset(thumbUrl($image));
    }
}
