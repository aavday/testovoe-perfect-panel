<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RatesController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function index(Request $request) {
        $originalRates = Http::get(config('services.rates.api_url'))->json();

        if ($currenciesFilter = $request->input('currency')) {
            if (gettype($currenciesFilter) === 'string') {
                $currenciesFilter = [$currenciesFilter];
            }

            $filtered = array_values(array_filter($originalRates, function ($item) use ($currenciesFilter) {
                return in_array(strtolower($item['symbol']), $currenciesFilter);
            }));

            $originalRates = $filtered;
        }

        $newRates = [];

        foreach ($originalRates as $rate) {
            $price = $rate['quotes']['USD']['price'];
            $priceWithCommission = $price * config('services.rates.commission');

            $newRates[$rate['symbol']] = $priceWithCommission;
        }

        asort($newRates);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $newRates
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function convert(Request $request) {
        $originalRates = Http::get(config('services.rates.api_url'))->json();
        $currencyFrom = strtoupper($request->input('currency_from'));
        $currencyTo = strtoupper($request->input('currency_to'));

        $areWeBuyingCoins = $currencyFrom === 'USD';
        $findCurrency = $areWeBuyingCoins ? $currencyTo : $currencyFrom;

        $value = round($request->input('value'), $areWeBuyingCoins ? 2 : 10);

        if ($areWeBuyingCoins && $value < 0.01 or !$areWeBuyingCoins && $value < 0.0000000001) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Value is too low'
            ], 400);
        }

        $rate = array_filter($originalRates, function ($item) use ($findCurrency) {
            return $item['symbol'] === $findCurrency;
        })[0]['quotes']['USD']['price'];
        $rateWithCommission = $rate * config('services.rates.commission');
        $rateWithCommissionRounded = round($rateWithCommission, 10);

        $convertedValue = $areWeBuyingCoins ? $rateWithCommissionRounded / $value : $rateWithCommissionRounded * $value;
        $convertedValueRounded = round($convertedValue, $areWeBuyingCoins ? 10 : 2);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => [
                'currency_from' => $currencyFrom,
                'currency_to' => $currencyTo,
                'value' => $value,
                'converted_value' => $convertedValueRounded,
                'rate' => $rateWithCommissionRounded,
            ]
        ]);
    }
}
