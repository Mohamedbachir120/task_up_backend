<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    //
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
               
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'structurable_type'=>"App\Models\Departement",
                "structurable_id"=>$request["departement"]
            ]);
            $user->role_id = 1;
            $user->save();
           
            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'id'=> $user->id,
                "name"=>$user->name,
                "role"=>$user->role->name,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'id'=> $user->id,
                "name"=>$user->name,
                "role"=>$user->role->name,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
   
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        
        return response()->json(['success' => true,], 200);
    }
    public function refresh(Request $request){
        $splited = explode("|",$request->bearerToken());
        $token = DB::table('personal_access_tokens')->where('id',$splited[0])->first(); 
        $user = User::find($token->tokenable_id);
        return response()->json([
            'status' => true,
            'message' => 'User Logged In Successfully',
            'id'=> $user->id,
            "name"=>$user->name,
            "role"=>$user->role->name,
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);
    }

    
    public function update_password(Request $request){
        $token = DB::table('personal_access_tokens')->where("id",explode("|",$request->bearerToken())[0])->first();    
        $user =  User::find($token->tokenable_id);

        if(Hash::check($request["oldpassword"], $user->password)){
            $user->password = Hash::make($request["password"]);
            $user->save();
            return response()->json(['success'=>true,"message"=>"updated successfully"],200);

         }else{
            return response()->json(['success'=>false,"message"=>"Ancien mot de passe incorrecte"],401);

         }
    }
    

}
