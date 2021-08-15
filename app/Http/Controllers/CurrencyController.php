<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    public function currencies(): \Illuminate\Http\JsonResponse
    {
        $perPage = config('currency.currencies_pagination');
        try {
            $currencies = Cache::remember('currencies', now()->addMinutes(150), function () use ($perPage) {
                $dataCurrencies = Currency::select(['name', 'rate'])->simplePaginate($perPage);
                return $dataCurrencies;
            });
            if($currencies) {
                return response()->json($currencies, 200);
            } else {
                return response()->json(['message' => 'Failed: no data'], 520);
            }
        } catch (\Throwable $error) {
            return response()->json(['message' => $error], 500);
        }
    }

    public function currencyOnDate($date): \Illuminate\Http\JsonResponse
    {
        $date = Carbon::create($date);
        try {
            $currency = Cache::remember('$currency', now()->addMinutes(150), function () use ($date) {
                $dataCurrency = Currency::where('created_at','=', $date)->select(['name', 'rate'])->get();
                return $dataCurrency;
            });
            if($currency) {
                return response()->json($currency, 200);
            } else {
                return response()->json(['message' => 'Failed: no data'], 520);
            }
        } catch (\Throwable $error) {
            return response()->json(['message' => $error], 500);
        }
    }
}
