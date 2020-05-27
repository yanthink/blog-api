<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SensitiveWordController extends Controller
{
    public function index()
    {
        $this->authorize('update', Comment::class);

        $file = storage_path('SensitiveWords.txt');
        $content = File::exists($file) ? File::get($file) : '';

        return new Resource(compact('content'));
    }

    public function update(Request $request)
    {
        $this->authorize('update', Comment::class);

        $this->validate($request, ['content' => 'required']);

        $file = storage_path('SensitiveWords.txt');

        File::put($file, $request->input('content'));

        return $this->withNoContent();
    }
}
