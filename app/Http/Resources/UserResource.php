<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Auth;

/**
 * Class UserResource
 * @property \App\Models\User $resource
 * @package App\Http\Resources
 */
class UserResource extends Resource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'settings' => $this->when($this->resource->id == Auth::id(), $this->resource->settings),
        ]);
    }
}
