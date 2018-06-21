<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Storage;

class AttachmentController extends Controller
{
    public function upload(Request $request)
    {
        $rules = [
            'file' => 'required|image|mimes:png,jpg,jpeg,gif|max:1024',
        ];

        $this->validate($request, $rules);

        $disk = Storage::disk('public');
        $path = $disk->putFile('tmp', $request->file('file'));

        $data = [
            'filename' => $disk->url($path),
        ];
        return compact('data');
    }
}