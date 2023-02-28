<?php

namespace App\Console\Commands;

use App\Models\ScheduledAlert;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Events\TaskAffected;


class ScheduleAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is command is used to schedule alerts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {   
        $carbon = Carbon::now();
        $time_plus = 
        $preventions = ScheduledAlert::whereBetween('send_time',[Carbon::now("GMT+1")->subMinutes(10)->toDateTimeString(),Carbon::now("GMT+1")->addHour()->toDateTimeString()])->with("task")->get();
        $timed_outs = ScheduledAlert::where('send_time','<=',Carbon::now("GMT+1")->toDateTimeString())->with("task")->get();

        
        foreach($preventions as $prevention){
            $destination = explode(",",$prevention->destination); 
            foreach($destination as $user){

                TaskAffected::dispatch($user,"Dépechez vous","Vous êtes en retard il est temp pour réaliser ".$prevention->task->title);
            } 
            $prevention->delete();


        }
        foreach($timed_outs as $item){
            $destination = explode(",",$item->destination); 
            foreach($destination as $user){

                TaskAffected::dispatch($user,"Date d'échéance expiré","Vous avez dépacé le délai pour rendre la tâche ".$item->task->title);
            } 
            $task = $item->task;
            $task->status = "EN RETARD";
            $task->save();
            $item->delete();


        }


        
        return Command::SUCCESS;
    }
}
