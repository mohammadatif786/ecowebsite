<?php

namespace App\Http\Controllers\User\Gifts;

use App\Domain\Vibes\Actions\ConvertGiftCoinsToCashAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GiftCashOutController extends Controller
{
    public function store(Request $request, ConvertGiftCoinsToCashAction $action): JsonResponse
    {
        $data = $request->validate(['coins' => ['required', 'integer', 'min:1', 'max:100000000']]);

        try {
            $result = $action->execute($request->user(), (int) $data['coins']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $e) {
            Log::error('Gift conversion failed: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Conversion failed. Please try again later.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully converted '.number_format($result->coinsConverted).' coins to $'.number_format($result->payout, 2),
            'data' => $result->toArray(),
        ]);
    }
}
