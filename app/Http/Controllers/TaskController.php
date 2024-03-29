<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
use App\Models\User;

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
    public function project_tasks(Request $request,$id)
    {   
        $finished = Auth::user()->tasks()
        ->where('status','TERMINÉ')
        ->where('project_id',$id)
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
        ->where('project_id',$id)
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
        ->where('project_id',$id)

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
        if(Auth::user()->role->name == "Directeur"){
            $projects = new Collection();
            $users = new Collection();
            foreach ($departement->departements as $dept) {
              $projects =  $projects->merge($dept->projects);
              $users = $users->merge($dept->users);  
            }
            
            $tasks = DB::table('tasks')
                    ->join('task_user','task_user.task_id','=','tasks.id')
                    ->join('users','users.id','=','task_user.user_id')
                    ->whereIn('users.structurable_id',$departement->departements->pluck('id'))
                    ->whereIn('tasks.status',array('À FAIRE','EN RETARD'))
                    ->select('tasks.*')
                    ->distinct()
                    ->get();

            return response()->json(["projects"=>$projects,"users"=>$users,"tasks"=>$tasks],200);

        }else{

            $users =Auth::user()->role_id == 2 ?  $departement->users : $departement->users()->where('role_id','<>',2)->get();
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
        $task = Task::find($id);
        $users = $task->users;
        foreach($users as $user){
            
            TaskAffected::dispatch($user->id,"Suppression d'une tâche","La tâche ".$task->title." a été supprimé par ".Auth::user()->name);
           
        }
        $task->delete();
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
    public function getMonthTask(Request $request){
        $today = new Carbon($request["date"]);
        $tomorrow = new Carbon($request["date"]);

        $today = $today->isoFormat("YYYY-MM-01 00:00:00");
        $tomorrow = $tomorrow->addMonth();
        $tasks = Auth::user()->tasks()
        ->whereBetween('end_date',[$today,$tomorrow])
        ->orderBy('end_date')
        ->get();    
      
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
            
            $path = public_path()."/rapport/".$user->name.$user->id."/";
            
            if(!File::isDirectory($path)){
                File::makeDirectory($path,0777,true,true);
            }

            Document::create([
                            "name"=>"Rapport ".$monthString." ".$year,
                            "url"=>"rapport/".$user->name.$user->id."/".$year."-".$month.'.docx',
                            "year"=>$year,
                            "month"=>$month,
                            "user_id"=>$user->id]);
                            $phpWord = new \PhpOffice\PhpWord\PhpWord();
                            // styles 
                            $titleStyle = array('bold'=>true,'italic'=> true, 'size'=>14, 'name'=>'Times New Roman','afterSpacing' => 0, 'Spacing'=> 0, 'cellMargin'=>0 );
                    
                            $fontStyle = array('italic'=> true, 'size'=>11, 'name'=>'Times New Roman','afterSpacing' => 0, 'Spacing'=> 0, 'cellMargin'=>0 );
                            $TfontStyle = array('bold'=>true, 'italic'=> true, 'size'=>11, 'name' => 'Times New Roman', 'afterSpacing' => 3, 'Spacing'=> 0, 'cellMargin'=>0);
                            $styleCell = array('borderTopSize'=>1 ,'borderTopColor' =>'black','borderLeftSize'=>1,'borderLeftColor' =>'black','borderRightSize'=>1,'borderRightColor'=>'black','borderBottomSize' =>1,'borderBottomColor'=>'black' ,'bgColor' =>'839FD6');
                            $styleCellBody = array('borderTopSize'=>1 ,'borderTopColor' =>'black','borderLeftSize'=>1,'borderLeftColor' =>'black','borderRightSize'=>1,'borderRightColor'=>'black','borderBottomSize' =>1,'borderBottomColor'=>'black' );

                            $inversedStyle = array('textDirection'=>\PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                            $section = $phpWord->addSection();
                            $tableStyle = array(
                                'borderColor' => '006699',
                                'borderSize'  => 6,
                                'cellMargin'  => 50
                            );
                            $section->addText('Direction : '.$user->structurable->direction->name ,$titleStyle);
                            $section->addText('Département : '.$user->structurable->name  ,$titleStyle);
                            $section->addText('Nom : '.$user->name  ,$titleStyle);
                    
                            $section->addTextBreak(2);
                            $section->addText('Rapport d\'activité du mois de  '.$monthString  ,array_merge(array( 'center'=>true,'underline' =>'single'),$titleStyle), array('align' => 'center'));
                            $section->addTextBreak(3);
                    
                            
                            $table = $section->addTable('myOwnTableStyle',
                            array('borderSize'=>0, 
                            'leftFromText'=>10,
                            'borderColor'=>'eeeeee',
                            'cellMargin'=>0, 
                            'spaceBefore' => 0, 
                            'spaceAfter' => 0,
                            'spacing' => 0  ));
                            $table->addRow(-0.5, array('exactHeight' => -5));
                            
                      
                            // $cell = $table->addCell(5000,$inversedStyle );
                            $cell = $table->addCell(5000,$styleCell)->addText('Projets',$TfontStyle);
                            $cell = $table->addCell(5000,$styleCell)->addText('Tâches',$TfontStyle);
                    
                            
                            for ($i = 0 ; $i < count($tasks);$i++) {
                                if($i == 0 || ($i > 0 && $tasks[$i]->project->name !=  $tasks[$i-1]->project->name)){
                                    $table->addRow(2.5, array('exactHeight' => 10000));
                                    $cell = $table->addCell(5000,$styleCellBody );
                                    $cell->addText($tasks[$i]->project->name,$TfontStyle);
                                    $cell = $table->addCell(5000,$styleCellBody );
                                    $cell->addText("- ".$tasks[$i]->title,$TfontStyle);
                    
                    
                                }else{
                                    $cell->addText("- ".$tasks[$i]->title,$TfontStyle);
                                }
                            }
                    
                       
                    
                            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                            $objWriter->save($path.$year."-".$month.'.docx');                

            return response()->json(['url'=> "rapport/".$user->name.$user->id."/".$year."-".$month.'.docx'],200);

        }else{
            return response()->json(['url'=>$document[0]->url],200);
        }
       
        


    }
    function generate_departement_report(Request $request){

        $today = new Carbon($request["date"]);
        $today = $today->locale('fr');
        $projects  = Project::where('departement_id',Auth::user()->structurable_id)->where('is_fixed',0)->get()->pluck('id'); 
        $fixed = Project::where('departement_id',Auth::user()->structurable_id)->where('is_fixed',1)->get()->pluck('id');
        $monthString = $today->isoFormat("MMMM"); 
        
        $year = $today->year;
        $month = $today->month;
        $document = Auth::user()->documents()->where('year',$year)->where('month',$month)->get();
        if(count($document) == 0){

            $user = Auth::user();
            $today = $today->isoFormat("YYYY-MM-01 00:00:00");
            $tomorrow = new Carbon($today);
            $tomorrow = $tomorrow->addMonth()->isoFormat('YYYY-MM-DD 00:00:00');
    
            $original = Task::whereIn('project_id',$projects)->whereBetween('end_date',[$today,$tomorrow])->with("sub_tasks")->with('project')->orderBy('project_id')->get();
            $fixed_tasks = Task::whereIn('project_id',$fixed)->with("sub_tasks")->with('project')->orderBy('project_id')->get();
           
            $tasks = $original->merge($fixed_tasks);
            
            $path = public_path()."/rapport/".$user->name.$user->id."/";
            
            if(!File::isDirectory($path)){
                File::makeDirectory($path,0777,true,true);
            }

           
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            // styles 
            $titleStyle = array('bold'=>true,'italic'=> true, 'size'=>14, 'name'=>'Times New Roman','afterSpacing' => 0, 'Spacing'=> 0, 'cellMargin'=>0 );
    
            $fontStyle = array('italic'=> true, 'size'=>11, 'name'=>'Times New Roman','afterSpacing' => 0, 'Spacing'=> 0, 'cellMargin'=>0 );
            $TfontStyle = array('bold'=>true, 'italic'=> true, 'size'=>11, 'name' => 'Times New Roman', 'afterSpacing' => 3, 'Spacing'=> 0, 'cellMargin'=>0);
            $styleCell = array('borderTopSize'=>1 ,'borderTopColor' =>'black','borderLeftSize'=>1,'borderLeftColor' =>'black','borderRightSize'=>1,'borderRightColor'=>'black','borderBottomSize' =>1,'borderBottomColor'=>'black' ,'bgColor' =>'839FD6');
            $styleCellBody = array('borderTopSize'=>1 ,'borderTopColor' =>'black','borderLeftSize'=>1,'borderLeftColor' =>'black','borderRightSize'=>1,'borderRightColor'=>'black','borderBottomSize' =>1,'borderBottomColor'=>'black' );
           
            $inversedStyle = array('textDirection'=>\PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
            $section = $phpWord->addSection();
            $tableStyle = array(
                'borderColor' => 'black',
                'borderSize'  => 6,
                'cellMargin'  => 50
            );
            $section->addText('Direction : '.$user->structurable->direction->name ,$titleStyle);
            $section->addText('Département : '.$user->structurable->name  ,$titleStyle);
            $section->addText('Nom : '.$user->name  ,$titleStyle);
    
            $section->addTextBreak(2);
            $section->addText('Rapport d\'activité du mois de  '.$monthString  ,array_merge(array( 'center'=>true,'underline' =>'single'),$titleStyle), array('align' => 'center'));
            $section->addTextBreak(3);
    
            
            $table = $section->addTable('myOwnTableStyle',
            array('borderSize'=>3, 
            'leftFromText'=>10,
            'borderColor'=>'black',
            'cellMargin'=>0, 
            'spaceBefore' => 0, 
            'spaceAfter' => 0,
            'spacing' => 0  ));
            $table->addRow(-0.5, array('exactHeight' => -5));
            
        
            // $cell = $table->addCell(5000,$inversedStyle );
            $cell = $table->addCell(5000,$styleCell);
            $cell->addText(' ');
            $cell->addText('Projets',$TfontStyle);
            $cell = $table->addCell(5000,$styleCell);
            $cell->addText(' ');
            $cell->addText('Tâches',$TfontStyle);
          
    
            
            for ($i = 0 ; $i < count($tasks);$i++) {
                if($i == 0 || ($i > 0 && $tasks[$i]->project->name !=  $tasks[$i-1]->project->name)){
                    $cell->addText(' ');

                    $table->addRow(2.5, array('exactHeight' => 10000));
                    $cell = $table->addCell(5000,$styleCellBody );
                    $cell->addText(' ');
                    $cell->addText($tasks[$i]->project->name,$TfontStyle);
                    $cell->addText(' ');

                    $cell = $table->addCell(5000,$styleCellBody );
                    $cell->addText(' ');

                    $cell->addText("- ".$tasks[$i]->title,$TfontStyle);
    
    
                }else{
                    $cell->addText("- ".$tasks[$i]->title,$TfontStyle);
                }
            }
    
        
    
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($path.$year."-".$month.'.docx');   
            Document::create([
                "name"=>"Rapport ".$monthString." ".$year,
                "url"=>"rapport/".$user->name.$user->id."/".$year."-".$month.'.docx',
                "year"=>$year,
                "month"=>$month,
                "user_id"=>$user->id]);


            return response()->json(['url'=> "rapport/".$user->name.$user->id."/".$year."-".$month.'.docx'],200);

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

    public function perfomances(Request $request){
        $year_start = Carbon::now();
        $date = $year_start->isoFormat('YYYY-01-01');
        $year_start = new Carbon($date);
        $month = Carbon::now();
        $iterations =  $month->month;
        $data_status = [
            'À FAIRE'=> Auth::user()->tasks()->where('status','À FAIRE')->count(),
            'EN RETARD'=> Auth::user()->tasks()->where('status','EN RETARD')->count(),
            'TERMINÉ'=> Auth::user()->tasks()->where('status','TERMINÉ')->count(),

        ];
        
        $data_month = [];
        for($i=1 ; $i <= $iterations ; $i++){
            $next = new Carbon($year_start);
            
            $count =  Auth::user()->tasks()
                    ->whereBetween('end_date',[$year_start,$next->addMonth()])->count();
            $data_month[$year_start->locale('fr')->isoFormat('MMMM')] = $count;
            $year_start = $year_start->addMonth();
            $next = $next->addMonth();

        }
        $departement = Auth::user()->structurable_id;
        $data_projects = [];
        $projects = Project::where('departement_id',$departement)->get();
        foreach($projects as $project){
            $data_projects[$project->name] = $project->tasks()->count();
        }
        return response()->json([
            'label_data_month'=>array_keys($data_month),
            'data_month'=>array_values($data_month),
            'label_data_status'=>array_keys($data_status) ,
            'data_status'=>array_values($data_status),
            'label_data_projects'=>array_keys($data_projects),
            'data_projects'=>array_values($data_projects) 
        
        ]);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function TaskPerDepartmentStatus(Request $request){
        $projects  = Project::where('departement_id',Auth::user()->structurable_id)->get()->pluck('id');
        
        $finished = Task::whereIn('project_id',$projects)
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
     

        $late = Task::whereIn('project_id',$projects)
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

        $todo = Task::whereIn('project_id',$projects)
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
    public function TaskPerDirectionStatus(Request $request){
       $projects =  Project::whereIn('departement_id',Auth::user()->structurable->departements->pluck('id'))->get()->pluck('id') ;
        $finished = Task::whereIn('project_id',$projects)
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
     

        $late = Task::whereIn('project_id',$projects)
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

        $todo = Task::whereIn('project_id',$projects)
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
    public function TaskPerProject(Request $request){
        $role = Auth::user()->role->name;
        $projects = [];
        $projects  = $role == "Chef de département" ? 
        Project::where('departement_id',Auth::user()->structurable_id)->get():
        Project::whereIn('departement_id',Auth::user()->structurable->departements->pluck('id'))->get() ;

        $data = [];
        foreach ($projects as $project) {
            $data[$project->name] = Task::where('project_id',$project->id)
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
            # code...
        }
       


        return response()->json(["data" => $data],200);
        
    }
    public function TaskPerDepartement(Request $request){
        $departements = Auth::user()->structurable->departements;
        $data = [];
        foreach($departements as $departement){
            $tasks = Task::whereIn('project_id',$departement->projects->pluck('id'))
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
            $data[$departement->name] = $tasks;
        }
        return response()->json(["data" => $data],200);
    }
    public function TaskPerPersonne(Request $request){
        $users  = User::where('structurable_id',Auth::user()->structurable_id)->get();
        $data = [];
        foreach ($users as $user) {
            $data[$user->name] = $user->tasks()
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
            # code...
        }
       


        return response()->json(["data" => $data],200);
        
    }
    public function search(Request $request){
        $rapports = [];
        $tasks = [];
        $users = [];
        $projects = [];
        if($request['keyword'] == ''){
            return response()->json(['rapports'=>$rapports,'tasks'=>$tasks,'users'=>$users,'projects'=>$projects],200);
        }
        if($request['filter'] == 'documents'){
            $rapports = Auth::user()->documents()
                    ->where('name','like','%'.$request['keyword'].'%')
                    ->orWhere('year','like','%'.$request['keyword'].'%')
                    ->get();

        }else if($request['filter'] == 'tasks'){
        $role = Auth::user()->role->name;
            $tasks = [];
            
            if($role == "Directeur")  {
                
                $projects =  Project::whereIn('departement_id',Auth::user()->structurable->departements->pluck('id'))->get()->pluck('id') ;
                $tasks =     
                  Task::whereIn('project_id',$projects)
                  
                  ->where(function($query) use ($request){
                    return  $query->where('title','like','%'.$request['keyword'].'%')
                   ->orWhere('description','like','%'.$request['keyword'].'%');

                  })
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
            }        
            else if($role == "Chef de département"){
                $projects =  Project::where('departement_id',Auth::user()->structurable_id)->get()->pluck('id') ;
               
                Task::whereIn('project_id',$projects)
                  
                ->where(function($query) use ($request){
                  return  $query->where('title','like','%'.$request['keyword'].'%')
                 ->orWhere('description','like','%'.$request['keyword'].'%');

                })
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
            }
            else{
            $task = Auth::user()->tasks()
            ->where('title','like','%'.$request['keyword'].'%')
            ->orWhere('description','like','%'.$request['keyword'].'%')
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
            }
                   
        }else if($request['filter'] == 'personnes'){
            $users = User::where(function($query) use ($request){
                      return $query->where('name','like','%'.$request['keyword'].'%')
                       ->orWhere('email','like','%'.$request['keyword'].'%');
                     })
                    ->get();
        }else if($request['filter'] == 'projets'){
            $role = Auth::user()->role->name;
            $projects = $role == "Directeur"?  
                        Project::whereIn('departement_id',Auth::user()->structurable->departements->pluck('id'))
                        ->where(function($query)  use ($request){
                            return $query->where('name','like','%'.$request['keyword'].'%');
                        })
                        ->get()  
            
                        : Project::where('departement_id',Auth::user()->structurable_id)
                        ->where(function($query)  use ($request){
                            return $query->where('name','like','%'.$request['keyword'].'%');
                        })
                        ->get();
        }
        return response()->json(['rapports'=>$rapports,'tasks'=>$tasks,'users'=>$users,'projects'=>$projects],200);




    }
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
