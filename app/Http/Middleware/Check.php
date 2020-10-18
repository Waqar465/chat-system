<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Check
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response=[
            'error'=>true,
            'message'=>'Token Not found'
        ];

        $tkn =$request->header('token');
        $querycheck="select * from users where remember_token='$tkn'";
        $checktoken= DB::select($querycheck);
        if(count($checktoken)!=1)
        {
            return response()->json($response);
        }
        return $next($request);
    }
}
