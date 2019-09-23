<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string 用户名
     */
    public $username;

    /**
     * @var string 验证码
     */
    public $code;

    /**
     * @var string 识别码
     */
    public $identifyingCode;

    public function __construct($username, $code, $identifyingCode)
    {
        $this->username = $username;
        $this->code = $code;
        $this->identifyingCode = $identifyingCode;
    }

    public function build()
    {
        return $this->markdown('emails.users.code');
    }
}
