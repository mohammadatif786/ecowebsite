<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppPlolicyController extends Controller
{

    public function getSpecificAppPolocies($key)
    {
        $alldescriptions = Settings::whereIn('key', [$key])->get();
        if ($alldescriptions->isEmpty()) {
            return Inertia::render("User/AppSetting");
        }


        return Inertia::render("User/AppSetting", [
            'app_setting' => $alldescriptions
        ]);
    }
}
