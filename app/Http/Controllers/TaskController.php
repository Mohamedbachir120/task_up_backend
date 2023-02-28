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

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            "start_date"=>Carbon::now(),
            "end_date"=>$request["end_date"],
            "dependance_id"=>$request["dependance_id"],
            "project_id"=>$request["project_id"],
            "description"=>$request["description"],

        ]);
        $task->save();
        


        $task->users()->sync($request["users"]);
        $project = Project::find($request["project_id"]);
        $users = array_diff($request["users"],[Auth::user()->id]);
        $creator = Auth::user()->name;

        foreach($users as $user){
            
            TaskAffected::dispatch($user,"Nouvelle tâche","Une nouvelle tâche vous a été affectée par ".$creator." dans le projet ".$project->name);
           
        }
         
        $data = [];
        $data = array_map(function ($e) use ($task){
            return ["title"=>$e,"task_id"=>$task->id];
        },$request["sub_tasks"]);

        SubTask::insert($data);
        


        return response()->json(["success"=>true,"message"=>"Task created successfully"],200);
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
