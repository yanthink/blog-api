<?php

namespace App\Http\Controllers\V2;

use App\Models\Reply;
use App\Transformers\V2\ReplyTransformer;

class ReplyController extends Controller
{
    public function show(Reply $reply)
    {
        return $this->response->item($reply, new ReplyTransformer);
    }
}