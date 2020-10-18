<?php

namespace App\Http\Controllers;

use App\Models\User;
//use http\Message;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use function PHPUnit\Framework\isNull;

class MessageController extends Controller
{

    //get all messages of logged in user
    public function GetMessages(Request $request){
        $response['error']= true;
        $response['message'] = 'you are not authenticated';
        $tkn = $request->header('token');
        //fetching user_id again the token present in the header (getting the id of logged in user)
        $user = User::where('remember_token', $tkn)->first();
        if (!empty($user)) {
            //get the messages for the userid of logged in users from messages table
            $messages= Message::where('sender_id',$user->id)->get();
            if (count($messages)==0){              //-- if user sent no messages
                $response['message']="You sent no messages";
                return response()->json($response);
            }
            $response['error']= false;
            $response['message']=$messages;     //-- if user sent messages then return messages in json response
                    return response()->json($response);
                }
        else{                               //-- if user against the token not found
            $response['message' ]= 'User for the provided token not found ';
            return response()->json($response);
        }
    }
    //send message function
    public function SendMessage(Request $request)
    {
        $response = [
            'error' => true,
            'message'=> "Message not sent",
        ];
        $validator = Validator::make($request->all(), [
            'username' => ['required'],
            'msg_body' => ['required'],
        ]);
        $errors=$validator->errors();  //retrieve errors of validations
        if ($validator->fails()) {
            $response['errors']=$errors;
            return response()->json($response,422);
        }
        $message = new \App\Models\Message();
        $tkn = $request->header('token');
        //get respective logged in user from the token
        $user= User::where('remember_token', $tkn)->first();
        if (!empty($user)) {
            $sender_id = $user->id;
            $username = $request->username;
            //check whether receiver exists or not
            $user= User::where('username',$username)->first();
            if (!empty($user)){
                $message->sender_id = $sender_id;
                $message->receiver_id = $user->id;
                //checking if the msg is file type or text type
                if ($request->hasFile('msg_body')) {
                    $validator = Validator::make($request->all(), [
                        'msg_body' => ['mimes:mp3'],        //-- check the file type is only mp3
                    ]);
                    $errors=$validator->errors();  //retrieve errors of validations
                    if ($validator->fails()) {
                        $response['errors']=$errors;
                        return response()->json($response,422);
                    }
                    $fileName = time().'.'.$request->msg_body->extension();
                    //---- upload file to the directory/server
                    $path= $request->msg_body->move(public_path('uploads'), $fileName);
                    $path=public_path('uploads')."/".$fileName;
                    $body=$path;
                }
                else{       //--- if the msg_body type is not file then simply assign the text in msg_body to $body
                    $body=$request->msg_body;
                }
                $message->body = $body;
                if ($message->save()) {             //-- check if data is properly saving in db / message is sent successfully
                    $response['error']= false;
                    $response['message'] = 'Message is sent';
                    $response['data']=$message;
                    $response['id']=$message->id;
                    return response()->json($response);
                }
                return response()->json($response,422);
            }
            else{                   //if receiver id not found return all the available users in json
                $response['message' ]= 'Receiver  doesnot exists';

                $users= User::select('id','username')->get();
                $response['Available users are']=$users;
                return response()->json($response,422);
            }
        }
        else{                               //if user against the token not found
            $response['message' ]= 'User for the provided token not found ';
            return response()->json($response,422);
        }
    }

    //update message function
    public function UpdateMessage(Request $request){

        $response['error']=true;
        $response['message']='Message cannot be updated';

        $validator = Validator::make($request->all(), [
            'id' => ['required','numeric'],
            'msg_body' => ['required'],
        ]);
        //retrieve errors of validations
        $errors=$validator->errors();
        if ($validator->fails()) {
            $response['errors']=$errors;
            return response()->json($response,422);
        }

        $message = new \App\Models\Message();
        $tkn = $request->header('token');
        //get respective logged in user from the token
        $user= User::where('remember_token', $tkn)->first();
        $messageid=$request->id;
        $getmessage= Message::where('id',$messageid)->first();

        //check if the message id exists in db if not return negative response
        if (empty($getmessage)){
            $response['detail']="Message id does not exists";
            return response()->json($response,422);
        }

        // if message id exists..... check the message is sent by the logged in user
        if ($getmessage->sender_id!=$user->id){
            $response['detail']="Message is not sent by you";
            return response()->json($response,422);
        }

        //checking if the msg is file type or text type
        if ($request->hasFile('msg_body')) {
            $validator = Validator::make($request->all(), [
                'msg_body' => ['mimes:mp3'],        //-- check the file type is only mp3
            ]);
            $errors=$validator->errors();  //retrieve errors of validations
            if ($validator->fails()) {
                $response['errors']=$errors;
                return response()->json($response,422);
            }
            $fileName = time().'.'.$request->msg_body->extension();
            //---- upload file to the directory/server
            $path= $request->msg_body->move(public_path('uploads'), $fileName);
            $path=public_path('uploads')."/".$fileName;
            $body=$path;
        }
        else{       //--- if the msg_body type is not file then simply assign the text in msg_body to $body
            $body=$request->msg_body;
        }
        //update the message for the given message id
        $msg = Message::find($messageid);
        $msg->body = $body;
        if ($msg->save()){
            $response['error']=false;
            $response['message']='Message is updated successfully';
            $response['data']=$msg;
            return  response()->json($response);
        }
        return response()->json($response);
    }

    //delete message
    public function DeleteMessage(Request $request){
        $response['error']=true;
        $response['message']='Message cannot be deleted';

        $validator = Validator::make($request->all(), [
            'id' => ['required','numeric'],
        ]);

        //retrieve errors of validations
        $errors=$validator->errors();
        if ($validator->fails()) {
            $response['errors']=$errors;
            return response()->json($response,422);
        }

        $message = new \App\Models\Message();
        $tkn = $request->header('token');
        //get respective logged in user from the token
        $user= User::where('remember_token', $tkn)->first();

        $messageid=$request->id;
        $getmessage= Message::where('id',$messageid)->first();

        //check if the message id exists in db
        if (empty($getmessage)){
            $response['detail']="Message id does not exists";
            return response()->json($response,422);
        }

        // if message id exists..... check the message is sent by
        // the logged in user,
        // if not simply return negative response
        if ($getmessage->sender_id!=$user->id){
            $response['detail']="Message is not sent by you";
            return response()->json($response,422);
        }

        $del_message = Message::find($messageid);

        if ($del_message->delete()){
            $response['error']=false;
            $response['message']='Message is deleted successfully';
            $response['data']=$del_message;
            return  response()->json($response);
        }
        return response()->json($response);

    }
}
