<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use App\Models\SubTask;
use App\Events\TaskAffected;
use App\Models\Project;
use App\Models\Document;
use App\Models\ScheduledAlert;
use PDF;
use Storage;
use File;


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $finished = Auth::user()->tasks()
        ->where('status','TERMINÉ')
        ->with([
            'users'=>function($query){
                $query->select('users.id','users.name');
            },
            'project'=>function($query){
                $query->select('projects.id','projects.name');
            }
        ])
        ->orderBy('priority')
        ->get();
     

        $late = Auth::user()->tasks()
        ->where('status','EN RETARD')  
        ->with([
            'users'=>function($query){
                $query->select('users.id','users.name');
            },
            'project'=>function($query){
                $query->select('projects.id','projects.name');
            }
        ])->orderBy('priority')
        ->get();

        $todo = Auth::user()->tasks()
        ->where('status','À FAIRE')
        ->with([
            'users'=>function($query){
                $query->select('users.id','users.name');
            },
            'project'=>function($query){
                $query->select('projects.id','projects.name');
            }
        ])
        ->orderBy('priority')
        ->get();


        return response()->json(["finished" => $finished, "late" => $late, "todo"=>$todo],200);


    }
    public function fetch_initial_data(Request $request){
        $departement = Auth::user()->structurable;
        $users = $departement->users;
        $tasks = DB::table('tasks')
                ->join('task_user','task_user.task_id','=','tasks.id')
                ->join('users','users.id','=','task_user.user_id')
                ->where('users.structurable_id',$departement->id)
                ->whereIn('tasks.status',array('À FAIRE','EN RETARD'))
                ->select('tasks.*')
                ->distinct()
                ->get();
        return response()->json(["projects"=>$departement->projects,"users"=>$users,"tasks"=>$tasks],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {  
        $task = Task::create([
            "title"=>$request["title"],
            "start_date"=>Carbon::now("GMT+1"),
            "end_date"=>$request["end_date"],
            "dependance_id"=>$request["dependance_id"],
            "project_id"=>$request["project_id"],
            "description"=>$request["description"],
            "priority"=>$request["priority"]

        ]);
        $task->save();
        
        $task->users()->sync($request["users"]);
        $project = Project::find($request["project_id"]);
        

        // scheduling prevention and time over for tasks     

        $over_time = new Carbon($request["end_date"]);
        $over_time = $over_time->addHour();

        $prevention_time = new Carbon($request["end_date"]);
        $prevention_time = $prevention_time->subHour();

        ScheduledAlert::create(['destination'=>implode(",",$request["users"]),"send_time"=>$over_time,"task_id"=>$task->id]);    
        ScheduledAlert::create(['destination'=>implode(",",$request["users"]),"send_time"=>$prevention_time,"task_id"=>$task->id]);    
        

        // send live notification to users  except for the owner 


        $users = array_diff($request["users"],[Auth::user()->id]);
        $creator = Auth::user()->name;

        foreach($users as $user){
            
            TaskAffected::dispatch($user,"Nouvelle tâche","Une nouvelle tâche vous a été affectée par ".$creator." dans le projet ".$project->name);
           
        }


        // inserting subtask 

        $data = [];
        $data = array_map(function ($e) use ($task){
            return ["title"=>$e,"task_id"=>$task->id];
        },$request["sub_tasks"]);

        SubTask::insert($data);
        
        return response()->json(["success"=>true,"message"=>"Task created successfully"],200);
    }
    public function assign_sub_task(Request $request,$id){

        SubTask::create(['title'=>$request["title"],"task_id"=>$id]);
        
        return response()->json(["success"=>true,"message"=>"sous tâche ajouté avec succès"]);
    }


    public function mark_as_finished(Request $request,$id){

        $task = Task::find($id);
        $task->finished_at = Carbon::now();
        $task->status = "TERMINÉ";

        $task->save();
        
        return response()->json(["success"=>true,"message"=>"Task marked as finished"],200);


    }
    public function delete(Request $request,$id){
        $task = Task::find($id)->delete();
        return response()->json(["success"=>true,"message"=>"Task deleted successfully"],200);

    }
    public function getDayTasks(Request $request){
        $today = new Carbon($request["date"]);
        $tomorrow = new Carbon($request["date"]);

        $today = $today->isoFormat("YYYY-MM-DD 00:00:00");
        $tomorrow = $tomorrow->isoFormat("YYYY-MM-DD 23:59:59");

       $tasks =  Auth::user()->tasks()->whereBetween('end_date',[$today,$tomorrow])
                ->with('project','users','sub_tasks')->get();
       return response()->json(["tasks"=>$tasks],200);
    }
    function getTaskDate(Request $request){
        $date = Task::where('title','like','%'.$request['keyword'].'%')->pluck('end_date')->first();
        return response()->json(['date'=>$date],200); 
    }
    function generate_report(Request $request){

        $today = new Carbon($request["date"]);
        $today = $today->locale('fr');
        $monthString = $today->isoFormat("MMMM"); 
        
        $year = $today->year;
        $month = $today->month;
        $document = Auth::user()->documents()->where('year',$year)->where('month',$month)->get();
        if(count($document) == 0){

            $user = Auth::user();
            $today = $today->isoFormat("YYYY-MM-01 00:00:00");
            $tomorrow = new Carbon($today);
            $tomorrow = $tomorrow->addMonth()->isoFormat('YYYY-MM-DD 00:00:00');
    
            $tasks = $user->tasks()->whereBetween('end_date',[$today,$tomorrow])->with("sub_tasks")->with('project')->orderBy('project_id')->get();
            $pdf = PDF::loadView('pdf.rapport', compact('tasks','user','monthString'));
            
            $path = public_path()."/rapport/".$user->name.$user->id."/";
            
            if(!File::isDirectory($path)){
                File::makeDirectory($path,0777,true,true);
            }
            $pdf->save($path.$year."-".$month.'.pdf');

            Document::create([
                            "name"=>"Rapport ".$monthString." ".$year,
                            "url"=>"rapport/".$user->name.$user->id."/".$year."-".$month.'.pdf',
                            "year"=>$year,
                            "month"=>$month,
                            "user_id"=>$user->id]);

            return response()->json(['url'=> "rapport/".$user->name.$user->id."/".$year."-".$month.'.pdf'],200);

        }else{
            return response()->json(['url'=>$document[0]->url],200);
        }
       



    }
    public function rapports(Request $request){
        $rapports = Auth::user()->documents;
        return response()->json(["rapports" => $rapports],200);
    }

    public function sub_tasks(Request $request,$id){
        $task = Task::find($id);

        return response()->json(['subTasks'=>$task->sub_tasks()->select('id','title')->get()],200);

    
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        //
    }
}
