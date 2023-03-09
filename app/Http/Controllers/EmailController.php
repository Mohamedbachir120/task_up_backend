<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SendMail;
use Mail;


class EmailController extends Controller
{
    //
    public function index()
    {
        $testMailData = [
            'title' => 'Test Email From AllPHPTricks.com',
            'body' => 'This is the body of test email.'
        ];

        Mail::to('Othmane.MAHANE@naftal.dz')->send(new SendMail($testMailData));

        dd('Success! Email has been sent successfully.');
    }
}
