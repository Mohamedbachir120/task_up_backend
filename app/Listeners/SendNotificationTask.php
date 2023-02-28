<?php

namespace App\Listeners;

use App\Events\TaskAffected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;


class SendNotificationTask
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\TaskAffected  $event
     * @return void
     */
    public function handle(TaskAffected $event)
    {

        $response = Http::post(env('SOCKET_SERVER')."/send_notification", [
            'id' => strval($event->user_id),
            'title'=>$event->title,
            'message' => $event->message,
        ]);

        $task = Task::create(["title"=>$event->title,"message"=>$event->message]);
    }
}
