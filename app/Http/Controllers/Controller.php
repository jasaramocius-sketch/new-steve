<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function handleImageUpload($file, $folder)
    {
        $imagename = time().'.'.$file->getClientOriginalExtension();
        $destinationPath = public_path($folder);
        $file->move($destinationPath, $imagename);

        return $imagename;
    }

    public function deleteImage($filename, $folder)
    {
        $path = public_path($folder.'/'.$filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
