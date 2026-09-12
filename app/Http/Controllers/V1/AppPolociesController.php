<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;

class AppPolociesController extends Controller
{
    public function getAppPolocies()
    {
        $allKeys = ['legal', 'support', 'contact_us', 'help', 'privacy_policy', 'community_guidelines', 'safety_center'];
        $alldescriptions = Settings::whereIn('key', $allKeys)->get();

        if ($alldescriptions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'App polocies not found'
            ], 404);
        }

        return  response()->json([
            'status' => true,
            'data' => $alldescriptions
        ]);
    }
}
