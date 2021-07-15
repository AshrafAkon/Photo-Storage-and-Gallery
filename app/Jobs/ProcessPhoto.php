<?php

namespace App\Jobs;

use App\Models\Label;
use App\Models\Photo;
use Aws\Lambda\LambdaClient;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Validator;

class ProcessPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The photo instance.
     *
     * @var \App\Models\Photo
     */
    protected $photoId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($photoId)
    {
        $this->photoId = $photoId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $photo = Photo::where('id', "=", $this->photoId)->first();

        $client = new RekognitionClient(array(
            //'credentials' => $credentials,
            'region' => config('services.ses.region'),
            'version' => 'latest'
        ));
        $result = $client->detectLabels(
            [
                'Image' => [
                    'S3Object' => [
                        'Bucket' => config('aws.fullsize_bucket'),
                        'Name' => 'full_size/' . $photo->file_name
                    ],
                ]
            ]
        );
        $label_ids = [];
        $label_scores = [];

        foreach ($result['Labels'] as $label) {

            $label_id = Label::firstOrCreate([
                "name" => $label['Name']
            ])->id;
            array_push($label_ids, $label_id);
            array_push($label_scores, ['score' => round($label['Confidence'])]);
        }
        //dd(array_combine($label_ids, $label_scores));
        // adding the labels to the photo
        $photo->labels()->sync(array_combine($label_ids, $label_scores));

        // invoking lambda function to generate image previews and thumbnails
        $client = new LambdaClient(array(
            // 'credentials' => $credentials,
            'region' => config('services.ses.region'),
            'version' => 'latest'
        ));
        $result = $client->invoke(array(
            // FunctionName is required
            'FunctionName' => config('aws.post_upload_arn'),
            'InvocationType' => 'RequestResponse',
            'LogType' => 'None',
            //'ClientContext' => 'string',
            'Payload' => json_encode(array(
                'file_name' => $photo->file_name
            )),
            //'Qualifier' => 'string',
        ));
        $payload = $result->get('Payload');
        // validating details provided by lambda client
        $photoDetails = Validator::make(json_decode($payload, true), [
            'height' => 'required|integer',
            'width' => 'required|integer',
            'file_type' => 'required|in:jpg,jpeg,png,psd',
            'size' => 'required|integer',
        ])->validate();

        // updating the photo details returned by lambda function
        $photo->update($photoDetails);
    }
}
