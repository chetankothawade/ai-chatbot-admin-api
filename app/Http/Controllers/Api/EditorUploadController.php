<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EditorUploadResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class EditorUploadController extends Controller
{
    use ApiResponse;

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        $file = $request->file('image');
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            'uploads/editor',
            $fileName,
            'public'
        );

        return $this->success('messages.editor_upload_success', new EditorUploadResource([
            'url' => asset('storage/' . $path),
        ]));
    }
}
