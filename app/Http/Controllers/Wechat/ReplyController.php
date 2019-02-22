<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Reply;
use App\Transformers\ReplyTransformer;

class ReplyController extends Controller
{
    public function show(Reply $reply)
    {
        return $this->response->item($reply, new ReplyTransformer);
    }
}