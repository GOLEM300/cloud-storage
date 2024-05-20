<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;

class StorageTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * post /storage/store
     * @return void
     */
    public function testFolderStore()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $path = 'test_folder';

        $folder_name = 'test_folder';

        $response = $this->actingAs($user)->postJson('/api/v1/storage/store', ['path' => $path]);

        $response->assertStatus(200)->assertJson(['message' => 'Папка создана']);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $this->assertDatabaseHas('folders', [
            'folder_name' => $folder_name,
            'user_id' => $user->id,
            'path' => $path
        ]);
    }

    /**
     * post /storage/store/update
     * @return void
     */
    public function testFolderUpdate()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $folder = Folder::factory()->create(['user_id' => $user->id]);

        Storage::disk('public')->makeDirectory($folder->path);

        $path = 'new_folder_name';

        $new_folder_name = 'new_folder_name';

        $response = $this->actingAs($user)->postJson("/api/v1/storage/update/{$folder->id}", ['path' => $path]);

        $response->assertStatus(200)->assertJson(['message' => 'Папка переименована']);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $this->assertDatabaseHas('folders', [
            'id' => $folder->id,
            'folder_name' => $new_folder_name,
            'user_id' => $user->id,
            'path' => $path
        ]);
    }

    /**
     * delete /storage/store/update
     * @return void
     */
    public function testFolderDelete()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $folder = Folder::factory()->create(['user_id' => $user->id]);

        Storage::disk('public')->makeDirectory($folder->folder_name);

        $response = $this->actingAs($user)->deleteJson("/api/v1/storage/{$folder->id}");

        $response->assertStatus(200)->assertJson(['message' => 'Папка удалена']);

        $this->assertFalse(Storage::disk('public')->exists($folder->path));

        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
    }

    /**
     * get /storage/size
     * @return void
     */
    public function testFolderSize()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $folder_1 = Folder::factory()->create(['user_id' => $user->id]);

        Storage::disk('public')->makeDirectory($folder_1->folder_name);

        Storage::disk('public')->put($folder_1->folder_name . '/file_1.txt', 'test');

        $folder_2 = Folder::factory()->create(['user_id' => $user->id]);

        Storage::disk('public')->makeDirectory($folder_2->folder_name);

        Storage::disk('public')->put($folder_2->folder_name . '/file_2.txt', 'test');

        $response = $this->actingAs($user)->getJson('/api/v1/storage/size');

        $response->assertStatus(200)->assertJsonStructure([
            'message',
            'memory_usage (mb)',
        ]);
    }
}
