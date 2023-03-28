<?php

namespace App\Http\Controllers;

use App\Models\Step;
use Illuminate\Http\Request;
use App\Models\Collaboration;
use Auth;
use App\Models\Departement;

class StepController extends Controller
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
        $collaboration = Collaboration::find($request["collaboration"]);
        $step = Step::create([
            'title'=>$request['title'],
            'description'=>$request['description'],
            'order'=>$collaboration->steps->count() == 0 ? 1 : $collaboration->steps->count() +1 ,
            'due_date'=>$request["due_date"],
            'collaboration_id'=>$request['collaboration'],
            'departement_id'=>$request['departement'],
            'dependance_id'=>$request['dependance'],
        ]); 
        return response()->json([
            'success'=>true,
            'message'=>"step created successfully"
        ]);



    }
    public function initialQueryStep(Request $request,$id){
        $collaboration = Collaboration::find($id);
        
        return response()->json([
            'steps'=>$collaboration->steps()->with('departement')->get(),
            'departements'=>$collaboration->departements
        ],200);


    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Step  $step
     * @return \Illuminate\Http\Response
     */
    public function show(Step $step)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Step  $step
     * @return \Illuminate\Http\Response
     */
    public function edit(Step $step)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Step  $step
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Step $step)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Step  $step
     * @return \Illuminate\Http\Response
     */
    public function destroy(Step $step)
    {
        //
    }
}
