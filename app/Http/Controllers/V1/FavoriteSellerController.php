<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\FavoriteSeller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteSellerController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'seller_id' => ['required', 'integer'],
        ]);

        $userId = Auth::id();

        if (! $userId) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        FavoriteSeller::firstOrCreate([
            'user_id' => $userId,
            'seller_id' => $data['seller_id'],
        ]);

        return response()->json(['status' => true, 'message' => 'Seller followed']);
    }
}
