<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Storage;

class AttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:30,60');
    }

    public function upload(Request $request)
    {
        $isFounder = Auth::check() && Auth::user()->hasRole('Founder');

        $rules = [
            'file' => 'required|image|mimes:png,jpg,jpeg,gif|max:'.($isFounder ? 2048 : 500),
        ];

        $this->validate($request, $rules);

        $disk = Storage::disk('public');
        $path = $disk->putFile('tmp', $request->file('file'));

        $data = [
            'url' => $disk->url($path),
        ];

        return compact('data');
    }
}