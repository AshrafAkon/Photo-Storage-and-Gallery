<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPhoto;
use App\Models\Photo;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render(
            'Upload/Upload',
            [
                // lazy load tags on reload
                'tags' => Inertia::lazy(fn() => Tag::select('id', 'name', 'slug')->get()),
            ]
        );
    }
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
     * This function will be used to store the initial data
     * when a user selects a file. This entry will be updated
     * When the actual file is sent.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->storeLocal($request);

        $files = $request->validate([
            '*.tempId' => 'required|integer',
            '*.title' => 'required|string',
            '*.size' => 'required|integer',
            '*.ext' => 'required|in:jpg,jpeg,png,psd',

        ]);
        // will save the file id as an array
        // token will be used as key. From frontend
        // we will use this token to match the correct
        // photo id
        $resp_data = [];
        $s3Client = new \Aws\S3\S3Client ([
            'region' => config('filesystems.disks.s3_fullsize.region'),
            'version' => '2006-03-01',
        ]);
        $bucketName = config('filesystems.disks.s3_fullsize.bucket');

        foreach ($files as $file) {
            $fileName = bin2hex(random_bytes(32)) . '.' . $file['ext'];
            $id = Photo::create([
                'title' => $file['title'],
                'user_id' => Auth::user()->id,
                'size' => $file['size'],
                'file_name' => $fileName,
                // 'user_id' => Auth::user()->id,

            ])->id;

            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket' => $bucketName,
                'Key' => 'full_size/' . $fileName,
                // 'ContentType' => 'image/jpeg', //enforce content type in future
                'Region' => 'ap-southeast-1',

            ]);

            $request = $s3Client->createPresignedRequest($cmd, now()->addMinutes(20));
            // Get the actual presigned-url
            $presignedUrl = (string) $request->getUri();

            array_push($resp_data, ['tempId' => $file['tempId'], 'id' => $id, 'uploadUrl' => $presignedUrl]);
        }
        return $resp_data;
    }

    public function storeLocal(Request $request)
    {

        $files = $request->validate([
            '*.tempId' => 'required|integer',
            '*.title' => 'required|string',
            '*.size' => 'required|integer',
            '*.ext' => 'required|in:jpg,jpeg,png,psd',

        ]);
        // will save the file id as an array
        // token will be used as key. From frontend
        // we will use this token to match the correct
        // photo id
        $resp_data = [];

        foreach ($files as $file) {
            $fileName = bin2hex(random_bytes(32)) . '.' . $file['ext'];
            $id = Photo::create([
                'title' => $file['title'],
                'user_id' => Auth::user()->id,
                'size' => $file['size'],
                'file_name' => $fileName,
                // 'user_id' => Auth::user()->id,

            ])->id;

            // Get the actual presigned-url
            $presignedUrl = route('uploads.store_file', ['id' => $id]);

            array_push($resp_data, ['tempId' => $file['tempId'], 'id' => $id, 'uploadUrl' => $presignedUrl]);
        }
        return $resp_data;
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'title' => 'string',
            'description' => 'string',
            'tags' => 'exists:tags,id',
        ]);
        $photo = Photo::where('token', '=', $data['token'])->get();
        if (!$photo) {
            // if photo doesn't exist then we should create the photo
            $photo = Photo::create([
                'token' => $data['token'],
                'title' => $data['title'] ? $data['title'] : '',
            ]);
        }
        // if tags are supplied then we sync it with pivot table
        if (array_key_exists('tags', $data)) {
            $photo->tags->sync($data['tags']);
            // removing tags from $data array for updating $photo
            unset($data['tags']);
        }
        // we dont want to update token. So removing it from array
        unset($data['token']);

        $photo->update($data);
    }

    public function completeUpload(Request $request)
    {
        $data = $request->validate([
            // 'ids' => 'required|array',
            "id" => "required|exists:photos,id",
        ]);
        //foreach ($data['ids'] as $id) {
        ProcessPhoto::dispatch($data['id']);
        //}
        return response('', $status = 200);
    }

    /**
     * Cancel an upload while its still uploading
     * This function will be used from cancel button
     * in /upload page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cancelUpload(Request $request)
    {
        $data = $request->validate(['*.id' => "required|integer"]);
        foreach ($data as $single_photo) {

            $photo = Photo::where([
                ['id', "=", $single_photo['id']],
                ['user_id', "=", Auth::id()],
                ['created_at', ">=", Carbon::now()->subHours(12)->toDateTimeString()],
            ])->first();

            if ($photo) {
                $photo->forceDelete();
            }
        }

        return response('', 200);
    }

    public function updateDetails(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:photos,id',
            'title' => 'string',
            'description' => 'string',
            'copyright' => 'string',
        ]);

        // photo details can only 9be updated in upload time
        // if the photo belongs to the authenticated user
        $photo = Photo::where([
            ['id', '=', $data['id']],
            ['user_id', "=", Auth::id()],
        ])->first();
        if ($photo) {
            // removed the id from $data array. as we dont want to update the id
            unset($data['id']);

            // updating the photo with updated details
            $photo->update($data);
        }
        return response('', 200);
    }
}
