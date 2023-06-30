<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FileStorageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Photo $photo)
    {

        $data = $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,zip,psd',

        ]);

        // generating a random filename for photo
        // $fileName = bin2hex(random_bytes(32)) . '.' . $data['file']->getClientOriginalExtension();
        $imgsize = getimagesize($data['file']->getPathName());
        $path = Storage::disk('local')->putFileAs('full_size', $data['file'], $photo->file_name);

        // updating the photo entry

        $photo->update([

            'size' => $data['file']->getSize(),
            'height' => $imgsize[1],
            'width' => $imgsize[0],
            'file_type' => $data['file']->getClientMimeType(),
            'should_process' => false,

        ]);
        $response = Http::post('http://localhost:8080/process', [
            'img_path' => $path,
            'save_path' => 'thumb.jpg',
        ]);

        return http_response_code(204);
    }
}
