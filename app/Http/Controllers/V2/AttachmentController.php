<?php

namespace App\Http\Controllers\V2;

use Illuminate\Http\Request;
use Storage;

class AttachmentController extends Controller
{
    public function __construct()
    {
        $this->rateLimit(30, 3600); // 60分钟30次
        $this->middleware('api.throttle');
    }

    public function upload(Request $request)
    {
        $isFounder = user() && user()->hasRole('Founder');

        $rules = [
            'file' => 'required|image|mimes:png,jpg,jpeg,gif|max:' . ($isFounder ? 2048 : 200),
        ];

        $this->validate($request, $rules);

        $disk = Storage::disk('public');
        $path = $disk->putFile('tmp', $request->file('file'));

        $data = [
            'fileUrl' => $disk->url($path),
        ];
        return compact('data');
    }
}