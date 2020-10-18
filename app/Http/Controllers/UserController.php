<?php

namespace App\Http\Controllers;

use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    //---User signup function ----//
    public function UserSignup(Request $request){
        $response = [
            'error' => true,
            'message' => 'Data is not validated'
        ];
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email','unique:users', 'max:255'],
            'username'=>['required','unique:users'],
            'password' => [
            'required',
            'string',
            'min:4',             // must be at least 4 characters in length
            'regex:/[a-z]/',      // must contain at least one lowercase letter
            'regex:/[A-Z]/',      // must contain at least one uppercase letter
            'regex:/[0-9]/',      // must contain at least one digit
            'regex:/[@$!%*#?&.]/', // must contain a special character
            ],
            'confirm_password'=>'required|same:password',   // required and has to match the password field
            'dob' => 'date|before:today',
        ]);
        $errors=$validator->errors();  //retrieve errors of validations
        if ($validator->fails()) {
            $response['errors']=$errors;
            return response()->json($response,422);
        }

        else{
            //------- get all the requests --------//
            $email= $request->email;
            $username=$request->username;

            $pass=$request->password;
            $password = Hash::make($pass);          //hashing the password
            $dob=$request->dob;
            $user= new User();                  //creating a new instance of User
            $user->username=$username;
            $user->email=$email;
            $user->password=$password;
            $user->dob=$dob;
            if ($user->save()){                 //if data is stored in db
            $response = [
                'error' => false,
                'message' => 'data is validated and user is registered',
                'data'=> $user,
                'email'=>$email,
                'password'=>$pass,
            ];
            return response()->json($response);         //if data is not stored in db return negative response
            }
        }
    }

    // ---- User login function ---- //

    public function login(Request $request){
        $response = [
            'error' => true,
            'message' => 'Data is not validated'
        ];

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'password' => [
                'required',
                'string',
                'min:4',               // must be at least 4 characters in length
                'regex:/[a-z]/',       // must contain at least one lowercase letter
                'regex:/[A-Z]/',       // must contain at least one uppercase letter
                'regex:/[0-9]/',       // must contain at least one digit
                'regex:/[@$!%*#?&.]/', // must contain a special character
            ],
        ]);
        $errors=$validator->errors();  //retrieve errors of validations
        if ($validator->fails()) {
            $response['errors']=$errors;
            return response()->json($response,422);
        }
        else{
            $email= $request->email;
            $password=$request->password;
            $user = User:: where('email',$email)->first();                  //fetching the email

            if (empty($user)){
                return response()->json($response,422);         // if email not found return a negative response
            }

            if(Hash::check($password, $user->password)){
                $token=Hash::make($user->id);              //generate token for corresponding id
                $affected = User::
                   where('id', $user->id)
                    ->update(['remember_token' =>$token ]);   //update token for the logged in user

                $response = [
                    'error' => false,
                    'message' => 'You are authenticated',
                    'token'=> $token,
                ];
                return  response()->json($response);
            }
            else{
                return  response()->json($response,422);   //return negative response if password does not match
            }
        }
    }

    //--- user logout function ---//
    public function logout(Request $request){
        $response = [
            'error' => false,
            'message' => 'Logged OUT SUCCESSFULLY',
        ];
        $tkn = $request->header('token');
        //fetching user_id again the token present in the header
        //checking for the logged in user that only logged in user is requesting for the logout function
        $user = User::where('remember_token', $tkn)->first();
        if (empty($user)){
            $response = [
                'error' => true,
                'message' => 'You are not logged in',
            ];
            return response()->json($response);
        }
        $affected = User::where('id', $user->id)
            ->update(['remember_token' =>null ]);       //-- if user is logged in and validated then the
        return response()->json($response);             //--  token is set to null and is logged out
    }
}
