<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Support extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Support Issues Good.Yellow")
            ->markdown('emails.support')
            ->with('mailData', $this->mailData)
            ->attachData(base64_decode($this->mailData['attachOne']), 'OneIssue.jpg', [
                'mime' => 'image/jpeg',
            ])
            ->attachData(base64_decode($this->mailData['attachTwo']), 'TwoIssue.jpg', [
                'mime' => 'image/jpeg',
            ])
            ->attachData(base64_decode($this->mailData['attachThree']), 'ThreeIssue.jpg', [
                'mime' => 'image/jpeg',
            ])
            ->attachData(base64_decode($this->mailData['attachFour']), 'FourIssue.jpg', [
                'mime' => 'image/jpeg',
            ])
            ->attachData(base64_decode($this->mailData['attachFive']), 'FiveIssue.jpg', [
                'mime' => 'image/jpeg',
            ]);
    }
}
