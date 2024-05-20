<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\UploadedFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile as iuf;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * get /upload/show
     * @return void
     */
    public function testShowUploadedFile()
    {
        $uploaded_file = UploadedFile::factory()->create();

        $response = $this->get('/api/v1/upload/show/' . $uploaded_file->id);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Файл получен',
            'file' => [
                'size (mb)' => number_format($uploaded_file->size / 1048576, 2),
                'upload_time' => $uploaded_file->upload_time
            ]
        ]);
    }

    /**
     * post /upload/store
     * @return void
     */
    public function testStoreUploadedFiles()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $folder = Folder::factory()->create(['user_id' => $user->id]);

        Storage::disk('public')->makeDirectory($folder->path);

        $file_1 = iuf::fake()->create('file_1.txt', 100);

        $file_2 = iuf::fake()->create('file_2.txt', 100);

        $response = $this->actingAs($user)->postJson('/api/v1/upload/store/' . $folder->id, [
            'files' => [$file_1, $file_2]
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Файлы успешно сохранены']);

        $this->assertDatabaseHas('uploaded_files', ['name' => 'file_1.txt']);

        $this->assertDatabaseHas('uploaded_files', ['name' => 'file_2.txt',]);
    }


    /**
     * delete /upload
     * @return void
     */
    public function testDeleteUploadedFile()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $uploaded_file = UploadedFile::factory()->create(['user_id' => $user->id]);

        Storage::disk('public')->put($uploaded_file->path . '/' . $uploaded_file->name, 'test');

        $response = $this->actingAs($user)->deleteJson("/api/v1/upload/{$uploaded_file->id}");

        $response->assertStatus(200)->assertJson(['message' => 'Файл удален']);

        $this->assertDatabaseMissing('uploaded_files', ['id' => $uploaded_file->id]);

        $this->assertFalse(Storage::disk('public')->exists($uploaded_file->path . '/' . $uploaded_file->name));
    }

    /**
     * post /upload/update
     * @return void
     */
    public function testUpdateUploadedFile()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $uploaded_file = UploadedFile::factory()->create([
            'name' => 'test.txt',
            'user_id' => $user->id,
            'path' => 'test'
        ]);

        Storage::disk('public')->put($uploaded_file->path . '/' . $uploaded_file->name, 'test');

        $response = $this->actingAs($user)->postJson("/api/v1/upload/update/{$uploaded_file->id}", [
            'name' => 'new_file_name',
        ]);

        $response->assertStatus(200);

        Storage::disk('public')->assertExists($uploaded_file->path . '/new_file_name.txt');

        $this->assertDatabaseHas('uploaded_files', [
            'id' => $uploaded_file->id,
            'name' => 'new_file_name.txt',
        ]);
    }

    /**
     * get /upload/download
     * @return void
     */
    public function testDownloadUploadedFile()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $uploaded_file = UploadedFile::factory()->create([
            'name' => 'test.txt',
            'user_id' => $user->id,
            'path' => 'test'
        ]);

        Storage::disk('public')->put($uploaded_file->path . '/' . $uploaded_file->name, 'test');

        $response = $this->actingAs($user)->get("/api/v1/upload/download/{$uploaded_file->id}");

        $response->assertStatus(200);
    }

    /**
     * post /upload/visibly
     * @return void
     */
    public function testChangeVisibilityUploadedFile()
    {
        $user = User::factory()->create();

        $uploaded_file = UploadedFile::factory()->create([
            'name' => 'test.txt',
            'user_id' => $user->id,
            'path' => 'test',
            'visibly' => 1
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/upload/visibly/{$uploaded_file->id}", [
            'visibly' => 'private'
        ]);

        $response->assertStatus(200);

        $this->assertEquals(0, $uploaded_file->fresh()->visibly);
    }
}
