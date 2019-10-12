<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'settings' => $this->when($this->id == Auth::id(), $this->settings),
        ]);
    }
}
