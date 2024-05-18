<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Upload\UploadedFileStoreRequest;
use App\Http\Requests\Upload\UploadedFileUpdateRequest;
use App\Http\Requests\Upload\UploadedFileVisiblyRequest;
use App\Models\Folder;
use App\Models\UploadedFile;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UploadController extends Controller
{
    /**
     * @param UploadedFileStoreRequest $request
     * @return ResponseFactory|Response
     */
    public function store(UploadedFileStoreRequest $request): ResponseFactory|Response
    {
        try {
            $files = $request->allFiles()['files'];

            //Здесь все это можно вынести в фон, подключив rabbitmq или redis
            //Но в тз было написано запускать все одной командой
            //А для запуска воркера нужна еще одна
            foreach ($files as $key => $file) {
                $file_name = $file->getClientOriginalName();
                $file_size = $file->getSize();
                $file_path = $request->folder_name;
                $folder_id = Folder::select('id')->where('folder_name',$request->folder_name)->get()[0]->id;

                Storage::disk('public')->putFileAs($file_path, $file, $file_name);

                $uploaded_file[$key]['name'] = $file_name;
                $uploaded_file[$key]['size'] = $file_size;
                $uploaded_file[$key]['path'] = $file_path;
                $uploaded_file[$key]['upload_time'] = date('Y-m-d H:i:s');
                $uploaded_file[$key]['user_id'] = $request->user()->id;
                $uploaded_file[$key]['folder_id'] = $folder_id;
            }

            UploadedFile::upsert($uploaded_file, ['name', 'path']);

            return response(['message' => 'Файлы успешно сохранены'], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Что то пошло не так'], 500);
        }
    }

    /**
     * @param UploadedFile $uploaded_file
     * @return ResponseFactory|Response
     */
    public function delete(UploadedFile $uploaded_file): ResponseFactory|Response
    {
        if (!Gate::allows('update-uploaded_file', $uploaded_file)) {
            abort(404);
        }
        try {
            Storage::disk('public')->delete($uploaded_file->path . '/' . $uploaded_file->name);
            $uploaded_file->delete();
            return response(['message' => 'Файл удален'], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Что то пошло не так'], 500);
        }
    }

    /**
     * @param UploadedFileUpdateRequest $request
     * @param UploadedFile $uploaded_file
     * @return ResponseFactory|Response
     */
    public function update(UploadedFileUpdateRequest $request, UploadedFile $uploaded_file): ResponseFactory|Response
    {
        if (!Gate::allows('update-uploaded_file', $uploaded_file)) {
            abort(404);
        }
        try {
            $new_file_name = $request->name . '.' . explode('.', $uploaded_file->name)[1];

            Storage::disk('public')->move($uploaded_file->path . '/' . $uploaded_file->name, $uploaded_file->path . '/' . $new_file_name);

            $uploaded_file->name = $new_file_name;
            $uploaded_file->save();

            return response(['message' => 'Файл переименован'], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Что то пошло не так'], 500);
        }
    }

    /**
     * @param UploadedFile $uploaded_file
     * @return ResponseFactory|Response
     */
    public function show(UploadedFile $uploaded_file): ResponseFactory|Response
    {
        if (!Gate::allows('visible-uploaded_file', $uploaded_file)) {
            abort(404);
        }
        try {
            $file = [
                'size (mb)' => number_format($uploaded_file->size / 1048576, 2),
                'upload_time' => $uploaded_file->upload_time
            ];

            return response([
                'message' => 'Файл получен',
                'file' => $file
            ], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Файла не существует'], 500);
        }
    }

    /**
     * @param UploadedFile $uploaded_file
     * @return ResponseFactory|Response|BinaryFileResponse
     */
    public function download(UploadedFile $uploaded_file): ResponseFactory|Response|BinaryFileResponse
    {
        if (!Gate::allows('visible-uploaded_file', $uploaded_file)) {
            abort(404);
        }
        try {
            return response()->download(Storage::disk('public')->path($uploaded_file->path . '/' . $uploaded_file->name));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Что то пошло не так'], 500);
        }
    }

    /**
     * @param UploadedFileVisiblyRequest $request
     * @param UploadedFile $uploaded_file
     * @return ResponseFactory|Response
     */
    public function visibly(UploadedFileVisiblyRequest $request, UploadedFile $uploaded_file): ResponseFactory|Response
    {
        if (!Gate::allows('update-uploaded_file', $uploaded_file)) {
            abort(404);
        }
        try {
            Storage::disk('public')->setVisibility($uploaded_file->path . '/' . $uploaded_file->name, $request->visibly);
            $uploaded_file->visibly = $request->visibly === 'public' ? 1 : 0;
            $uploaded_file->save();
            return response(['message' => 'Видимость файла изменена'], 200);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Что то пошло не так'], 500);
        }
    }
}
