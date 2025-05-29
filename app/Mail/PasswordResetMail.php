<?php
namespace App\Mail;

// app/Http/Controllers/ForgotPasswordController.php

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use SerializesModels;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function build()
    {
        return $this->view('email.password-reset')
                    ->with([
                        'token' => $this->token,
                    ]);
    }
}