<?php

namespace Tests\Unit;

use App\Http\Controllers\FileStorageController;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreFileTest extends TestCase
{

    /** @test */
    public function test_it_can_store_a_file()
    {
        // Arrange
        Storage::fake('local');
        Http::fake();
        $photo = Photo::factory()->create();
        $file = UploadedFile::fake()->image('file.jpg');

        // Create a request with file
        $request = new Request();
        $request->files->set('file', $file);

        // Act
        $controller = new FileStorageController();
        $response = $controller->store($request, $photo);

        // Assert
        Storage::disk('local')->assertExists('full_size/' . $photo->file_name);
        // Http::assertPosted('http://localhost:8080/process');
        $this->assertEquals(204, $response);
    }
}
