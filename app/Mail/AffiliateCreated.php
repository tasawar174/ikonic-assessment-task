<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AffiliateCreated extends Mailable
{
    use Queueable, SerializesModels;
    public $affiliate;
    /**
     * Create a new message instance.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function __construct(Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Welcome to our affiliate program')
            ->view('mail.affiliate-created')
            ->with(['affiliate' => $this->affiliate]);
    }
}
