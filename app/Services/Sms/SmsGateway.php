<?php

namespace App\Services\Sms;

interface SmsGateway
{
    /** Sends a text message to a mobile number in 01XXXXXXXXX form. */
    public function send(string $mobile, string $message): void;
}
