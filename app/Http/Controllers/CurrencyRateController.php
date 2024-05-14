<?php

namespace App\Http\Controllers;

use App\Services\CurrencyRateService;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use App\Models\CurrencyRate;
use Illuminate\Http\Response;

class CurrencyRateController extends Controller
{
    public function index(Request $request)
    {
        try {
            $on_date = $request->on_date ?? today()->format('d-m-Y');
            $currencyRates = CurrencyRate::whereDate('on_date', $on_date);
            return $this->responseSuccess($currencyRates, "Currency rates on date: $on_date!");
        }catch (\Exception $e){
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function fetchFromCbr()
    {
        try {
            $currencyRateService = app(CurrencyRateService::class);
            $result = $currencyRateService->fetchAndSaveCurrencyRates();
            return $this->responseSuccess($result, 'Service has fineshed successfully!');
        } catch (ConnectException $e) {
            // Обработка ошибок подключения, таких как таймаут или недоступность сервера
            return $this->responseError(null, $e->getMessage(), Response::HTTP_BAD_GATEWAY);
        } catch (RequestException $e){
            // Обработка ошибок запроса к внешнему API
            return $this->responseError(null, $e->getMessage(), Response::HTTP_REQUEST_TIMEOUT);
        } catch (\Exception $e){
            // Обработка других исключений
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
