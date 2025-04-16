<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ImportCurrencyFromAwesomeApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa as moedas da API AwesomeAPI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://economia.awesomeapi.com.br/xml/available';

        $this->info('Iniciando a importação de moedas...');

        try {
            $this->info('Buscando dados da API...');
            $response = Http::get($url);

            if (!$response->successful()) {
                $this->error('Erro ao buscar dados da API: ' . $response->status());
                return;
            }

            $xml = simplexml_load_string($response->body());

            if ($xml === false) {
                $this->error('Erro ao processar o XML: ' . implode(', ', libxml_get_errors()));
                return;
            }

            $this->info('Convertendo XML para array...');

            $currencies = collect(json_decode(json_encode($xml), true))->filter(
                function ($code, $index) {
                    return str_contains($index, '-BRL');
                })
                ->toArray();

            $total = 0;

            $this->withProgressBar(
                array_keys($currencies),
                function ($code) use ($currencies, &$total) {
                    $getCode = (explode('-', $code))[0];
                    $description = (explode('/', $currencies[$code]))[0];
                    $this->newLine();
                    $this->info('Importando moeda: ' . $getCode . ' - ' . $description);

                   Currency::updateOrCreate([
                        'code' => $getCode,
                        'name' => $description,
                    ]);

                    $this->output->write('Moeda importada com sucesso: ' . $getCode);
                    $total++;
                }
            );
            $this->line('Total de moedas importadas: ' . $total);

            $service = app()->make('App\Services\CurrencyService');
            $this->line('Atualizando as cotações de moedas...', 'info');
            $service->updateExchangeRates();

        } catch (ConnectionException $e) {
            $this->error('Erro de conexão: ' . $e->getMessage());
            return;
        } catch (\Exception $e) {
            $this->error('Erro inesperado: ' . $e->getMessage());
            return;
        }

        $this->output->write('Importação de moedas concluída com sucesso!');
    }
}
