<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use phpDocumentor\Reflection\PseudoTypes\False_;

class UploadController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render("Upload");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 'required|mimes:jpg,jpeg,png,zip,psd|max:2048';
        $data = $request->validate([
            'files.*.id' => 'exists:photos,id',
            'files.*.name' => 'string',
            'files.*.description' => 'string',
            'files.*.tags' => 'exists:tags,id',
        ]);

        foreach ($data['files'] as $file) {
            $photo = Photo::where('id', '=', $file['id'])->get();

            $photo->update([
                'name' => $file['name'],
                'description' => $file['description'] ? $file['description'] : null,
                'should_process' => true
            ]);

            // updating tags
            $photo->tags->sync($file['tags']);
        }
        return http_response_code(202);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_file(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,zip,psd|max:2048',
            'name' => 'string'
        ]);
        // generating a random string and getting the first 8 characters of the random
        // hex string. then adding an underscore to the file name. also all spaces are
        // removed from filename
        $fileName = bin2hex(random_bytes(32)) . '.' . $data['file']->getClientOriginalExtension(); //
        $imgsize = getimagesize($data['file']->getPathName());
        $data['file']->storeAs("full_size/", $fileName, 's3');
        // adding the photo entry
        $photoId = Photo::create([
            'name' => $data['file']->getClientOriginalName(),
            'file_name' => $fileName,
            'size' => $data['file']->getSize(),
            'height' => $imgsize[1],
            'width' => $imgsize[0],
            'file_type' => $data['file']->getClientMimeType(),
            'user_id' => Auth::user()->id,
            'should_process' => False,
        ])->id;

        return $photoId;
    }
}
