<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function getFile($url, $name)
    {
        try {
            $path = $url.DIRECTORY_SEPARATOR.$name;
            $exists = Storage::disk()->exists($path);
            if (!$exists) {
                throw new \Exception;
            }
            $content['content'] = Storage::get($path);
    		$content['contentMime'] = Storage::mimetype($path);
    		$content['code'] = 200;

        } catch (Exception $e) {
            $content['content'] = 'picture not found';
            $content['contentMime'] = 'text/plain';
            $content['code'] = 404;
        }

    	return response()->make($content['content'], $content['code'], ['Content-Type' => $content['contentMime']]);        
    }

    public function downloadFile($path)
    {
        return response()->download(storage_path($path));
    }
}
