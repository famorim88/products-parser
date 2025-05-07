<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ImportHistory;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class ImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando importação de produtos...');

        $indexUrl = 'https://challenges.coode.sh/food/data/json/index.txt';
        $baseUrl = 'https://challenges.coode.sh/food/data/json/';

        try {
            $response = Http::get($indexUrl);

            if (!$response->successful()) {
                $this->error('Erro ao acessar o index.txt');
                return 1;
            }

            $files = array_filter(explode("\n", $response->body()));

            $importedCount = 0;

            foreach ($files as $file) {
                $jsonUrl = $baseUrl . $file;
                $this->info("Baixando: $jsonUrl");

                $dataResponse = Http::get($jsonUrl);

                if (!$dataResponse->successful()) {
                    Log::warning("Erro ao baixar arquivo $file");
                    continue;
                }

                $jsonLines = explode("\n", $dataResponse->body());
                $products = [];

                foreach ($jsonLines as $line) {
                    if (empty(trim($line))) continue;

                    $product = json_decode($line, true);
                    if (!isset($product['code'])) continue;

                    $product['imported_t'] = now();
                    $product['status'] = 'draft';

                    $products[] = $product;

                    if (count($products) === 100) break;
                }

                foreach ($products as $product) {
                    Product::updateOrCreate(
                        ['code' => $product['code']],
                        $product
                    );
                }

                $importedCount += count($products);
                sleep(1); // Evita sobrecarga
            }

            ImportHistory::create([
                'imported_at' => now(),
                'total_products' => $importedCount,
            ]);

            $this->info("Importação finalizada. Total importado: $importedCount");

            return 0;

        } catch (\Exception $e) {
            Log::error("Erro na importação: " . $e->getMessage());
            $this->error("Erro: {$e->getMessage()}");
            return 1;
        }
    }
}
