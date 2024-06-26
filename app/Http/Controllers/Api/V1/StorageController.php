<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\FolderPatchRequest;
use App\Http\Requests\Storage\FolderCreateRequest;
use App\Models\Folder;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Str;

class StorageController extends Controller
{
    /**
     * @param FolderCreateRequest $request
     * @return ResponseFactory|Response
     */
    public function store(FolderCreateRequest $request): ResponseFactory|Response
    {
        try {
            $path = $request->path;

            $folder_name = Str::afterLast($path,'/');

            $user_id = $request->user()->id;

            Storage::disk('public')->makeDirectory($path);

            Folder::create([
                'folder_name' => $folder_name,
                'user_id' => $user_id,
                'path' => $path
            ]);

            return response(['message' => 'Папка создана'], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return response(['message' => 'Что то пошло не так'], 500);
        }
    }

    /**
     * @param FolderPatchRequest $request
     * @return ResponseFactory|Response
     */
    public function update(FolderPatchRequest $request, Folder $folder): ResponseFactory|Response
    {
        try {
            $path = $request->path;

            $folder_name = Str::afterLast($request->path,'/');

            Storage::disk('public')->move($folder->path, $path);

            $folder->update([
                'folder_name' => $folder_name,
                'path' => $path
            ]);

            return response(['message' => 'Папка переименована'], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return response(['message' => 'Что то пошло не так'], 500);
        }
    }

    /**
     * @param Folder $folder
     * @return ResponseFactory|Response
     */
    public function delete(Folder $folder): ResponseFactory|Response
    {
        try {
            Storage::disk('public')->deleteDirectory($folder->path);

            $folder->delete();

            return response(['message' => 'Папка удалена'], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return response(['message' => 'Что то пошло не так'], 500);
        }
    }

    /**
     * @return ResponseFactory|Response
     */
    public function size(): ResponseFactory|Response
    {
        try {
            $fileSize = 0;

            $files = (File::allFiles(storage_path() . '/app/public/'));

            foreach ($files as $file) {
                $fileSize += $file->getSize();
            }

            return response([
                'message' => 'Успешно',
                'memory_usage (mb)' => number_format($fileSize / 1048576, 2)
            ], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return response(['message' => 'Что то пошло не так'], 500);
        }
    }
}
