<?php

namespace App\Http\Resources;

class ArticleResource extends Resource
{
    protected static $availableIncludes = ['user'];

    public function toArray($request)
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'user' => new UserResource($this->whenLoaded('user')),
        ]);
    }
}
