<?php

namespace App\Controllers;

class TestEmailController extends BaseController
{
    public function index()
    {
        $email = \Config\Services::email();
        $email->setTo('sndprintmedia@gmail.com');
        $email->setSubject('SND Rental — SMTP Test ✅');
        $email->setMessage('<h2>It works!</h2><p>Gmail SMTP is configured correctly.</p>');
        $email->setMailType('html');

        if ($email->send()) {
            return $this->response->setBody('<h2 style="color:green">✅ Email sent successfully! Check your inbox.</h2>');
        } else {
            return $this->response->setBody('<pre>' . htmlspecialchars($email->printDebugger()) . '</pre>');
        }
    }
}
