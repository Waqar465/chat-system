<?php

namespace App\Http\Controllers;
use App\Mail\TestMail;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Illuminate\Support\Facades\Mail;
use function PHPUnit\Framework\isNull;

class ForgetController extends Controller
{

    //send email function for forget password
    public function ForgetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ]);
        $response=[
            'error'=> true,
        ];
        $errors=$validator->errors();       //retrieve errors of validations
        if ($validator->fails()) {
            $response['errors']=$errors;
            return response()->json($response,422);
        }
        $response=[
            'error'=> true,
            'message'=>'Email not found',
        ];
        $email=$request->email;
        //check whether email exists
        $user = User::where('email', $email)->first();

        if (!empty($user)){
            $userid=$user->id;
        }
        else{
            return response()->json($response,422);
        }
        $random= Str::random(32);
        $token=$random;
        $message="http://127.0.0.1:8000/api/updatepassword/".$token;
        $expire_stamp = date(' H:i:s', strtotime("+5 min"));
        $now_stamp    = date("H:i:s");
        $now_stamp = strtotime($now_stamp);
        $expire_stamp = strtotime($expire_stamp);
        //insert token and expiry time  in password_reset table
        $reset_entry= new PasswordReset();
        $reset_entry->email=$email;
        $reset_entry->token=$token;
        $reset_entry->expired_at=$expire_stamp;
        $reset_entry->save();
        $to_email = $email;
        $token = array( "body" => $message);
        $sendmail=0;
        $sendmail=Mail::to($to_email)->send(new TestMail($token));
        if (isNull($sendmail)){         //-- if email is successfully sent
            $response=[
                'error'=> false,
                'message'=>'Please check your email for password reset link',
                'passtoken'=>$random,
            ];
            return response()->json($response);
        }
        else{               // if unable to send email return negative response
            $response['message']="Something wrong! Unable to send mail.";
            return response()->json($response,422);
        }
    }


    // ------update password function -------//
    public function UpdatePassword(Request $request,$token){
        $response=[
            'error'=> true,
        ];
        //check for the token in the password reset table whether request is made to change password is present
        $user = PasswordReset::where('token', $token)->first();
        if (!empty($user)){
            $newpass=$request->password;
            $now_stamp = date("H:i:s");
            $now_stamp = strtotime($now_stamp);
            $expire_stamp=$user->expired_at;            //get the expiration time from the reset_passwords table

            //---- check the expiration time of the token or link from db.
            if ($expire_stamp<$now_stamp){
                $response['error']=true;
                $response['nowtime']=$now_stamp;
                $response['expiretime']=$expire_stamp;
                $response['message']="Link Expired. Please try again";
                return response()->json($response,422);
            }
            $validator = Validator::make($request->all(), [
                'password' => [
                    'required',
                    'string',
                    'min:4',             // must be at least 4 characters in length
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&.]/', // must contain a special character
                ],
            ]);
            $errors=$validator->errors();  //retrieve errors of validations
            if ($validator->fails()) {
                $response['errors']=$errors;
                return response()->json($response,422);
            }
            $newpass=Hash::make($newpass);          //make hash password
            $affected = User::where('email', $user->email)->update(['password' =>$newpass ]);  //update password of the user
            $response=['error'=> false,];
            $response['message']="your password updated ";
            return response()->json($response);
        }
        $response['message']="you can not update password ";
        return response()->json($response,422);
    }
}


