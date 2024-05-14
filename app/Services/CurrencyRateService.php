<?php
namespace App\Services;

use App\Models\CurrencyRate;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
class CurrencyRateService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.cbr_api_url'); // Параметр API URL из конфигурации
    }

    public function fetchAndSaveCurrencyRates($maxRetries = 3, $retryDelay = 1000)
    {
        if (!Cache::has('currency_rates')) {
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    // Отправка HTTP запроса с использованием прокси
                    $username = urlencode('AO.Abdujalilov');
                    $password = urlencode('erer!1212');
                    $url = "http://{$username}:{$password}@srv-proxy03.ngmk.uz:3128";
                    $response =  HTTP::withOptions(['proxy' => $url])->get($this->apiUrl);
                    // Проверка успешности ответа
                    dd($response);
                    if ($response->successful()) {
                        // Если запрос успешен
                        $xmlData = $response->body()->getContents();
                        $parsedData = simplexml_load_string($xmlData);

                        // Сохраняем данные в базу данных
                        foreach ($parsedData->Valute as $valute) {
                            CurrencyRate::updateOrCreate(
                                ['currency_code' => (string)$valute->CharCode],
                                ['rate' => (float)str_replace(',', '.', $valute->Value)]
                            );
                        }

                        // Кэшируем данные на сегодня
                        Cache::put('currency_rates', true, now()->endOfDay());
                        return $response->body();
                    }
                } catch (RequestException $e) {
                    // Обработка исключения, если запрос не удался
                    // Записываем в лог или просто ждем перед повторной попыткой

                    // Если это последняя попытка, выбрасываем исключение
                    if ($attempt === $maxRetries) {
                        throw $e;
                    }
                } catch (ConnectionException $e) {
                    // Ошибка подключения (таймаут, проблемы с сетью и т. д.)
                    // Записываем в лог или просто ждем перед повторной попыткой

                    // Если это последняя попытка, выбрасываем исключение
                    if ($attempt === $maxRetries) {
                        throw $e;
                    }
                } catch (\Exception $e) {
                    // Обработка других исключений
                    throw $e;
                }
                // Ждем перед следующей попыткой
                usleep($retryDelay * 1000); // Переводим миллисекунды в микросекунды
            }
        }
    }
}
