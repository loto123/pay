<?php

namespace App\Jobs;

use App\Admin\Controllers\NoticeController;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SystemMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\SystemMessage
     */
    private $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        //
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $user_ids = User::where("status", 0)->pluck('id')->toArray();

        NoticeController::send($user_ids, 3, strip_tags($this->message->content), $this->message->title, ['message_id' => $this->message->id, 'link' => (string)$this->message->link]);
    }
}
