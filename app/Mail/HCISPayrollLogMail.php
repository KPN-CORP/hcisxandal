<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HCISPayrollLogMail extends Mailable
{
    use Queueable, SerializesModels;

    public $isOk;
    public $isErr;
    public $okUrl;
    public $okPullDate;
    public $okTotal;
    public $errUrl;
    public $errHttpStatus;
    public $errResponseBody;
    public $errException;

    public function __construct(array $data)
    {
        $this->isOk = $data['is_ok'];
        $this->isErr = $data['is_err'];
        $this->okUrl = $data['ok_url'];
        $this->okPullDate = $data['ok_pull_date'];
        $this->okTotal = $data['ok_total'];
        $this->errUrl = $data['err_url'];
        $this->errHttpStatus = $data['err_http_status'];
        $this->errResponseBody = $data['err_response_body'];
        $this->errException = $data['err_exception'];
    }

    public function build()
    {
        return $this->subject('New HCIS Payroll Log')->view('mail.hcisPayrollLogMail');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'HCIS Payroll Log',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.hcisPayrollLogMail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
