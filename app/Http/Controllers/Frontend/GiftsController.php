<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\GiftPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GiftsController extends Controller
{
    public function index(Request $request)
    {
        $gifts = Gift::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->sort, function ($query, $sort) {
                switch ($sort) {
                    case 'name_asc':
                        $query->orderBy('name', 'asc');
                        break;
                    case 'name_desc':
                        $query->orderBy('name', 'desc');
                        break;
                    case 'coins_asc':
                        $query->orderBy('coins', 'asc');
                        break;
                    case 'coins_desc':
                        $query->orderBy('coins', 'desc');
                        break;
                    default: // "featured"
                        $query->latest(); // Or your own "featured" logic
                        break;
                }
            })
            ->get();

        $gift_purchases = GiftPurchase::where('user_id', Auth::user()->id)->with('gift')->get();
        return Inertia::render('User/Gifts/Index', [
            'gifts'   => $gifts,
            'gift_purchases' => $gift_purchases,
            'filters' => $request->only(['search', 'category', 'sort']),
        ]);
    }


    public function buyGifts(Request $request)
    {
        $request->validate([
            'gift_id'  => 'required|exists:gifts,id',
        ]);

        $user = Auth::user();
        $gift = Gift::find($request->gift_id);

        // Default quantity = 1
        $quantity   = $request->quantity ?? 1;
        $net_total  = $gift->coins * $quantity;

        // Check balance
        $balance = $user->coins;
        if ($net_total > $balance) {
            return redirect()->back()->withError('Wallet has insufficient balance.');
        }

        // Store purchase record
        GiftPurchase::create([
            'user_id'        => $user->id,
            'gift_id'        => $gift->id,
            'quantity'       => $quantity,
            'total_coins'    => $net_total,
            'payment_method' => 'coins',
        ]);

        $user->coins = $user->coins - $net_total;
        $user->save();
        return redirect()->back()->withSuccess('Gift purchased successfully.');
    }
}
