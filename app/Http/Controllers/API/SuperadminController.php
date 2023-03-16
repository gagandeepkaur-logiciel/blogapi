<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Transformers\AdminListTransformer;

class SuperadminController extends Controller
{
    /**
     * Admin Listing
     */
    public function listadmin()
    {
        try {
            $type = auth()->user()->type;
            if ($type == 2)
                $user = USER::where('type', 1 || 'type', 0)->get();
            else
                return response()->json(['success' => false, 'message' => 'You have no access']);

            return  fractal($user, new AdminListTransformer())->toArray();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
