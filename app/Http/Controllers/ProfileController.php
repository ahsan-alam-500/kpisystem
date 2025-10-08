<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        return response()->json(
            $request->user('api')->load(['pub:id,name,pub_number','managedPubs:id,name,pub_number,manager_id'])
        );
    }
}
