<?php

namespace App\Jobs;

use Automattic\WooCommerce\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class CopyImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    /**
     * @var Client
     */
    private $source;

    private $perPage = 10;

    private $currentPage;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($currentPage = 1)
    {
        $this->source = new Client(
            config('woocommerce.source.site_url'),
            config('woocommerce.source.key'),
            config('woocommerce.source.secret'),
            [
                'version' => config('woocommerce.source.version'),
                'timeout' => config('woocommerce.source.timeout')
            ]
        );

        $this->currentPage = $currentPage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $params = [
            'page' => $this->currentPage,
            'per_page' => $this->perPage,
        ];

        $products = $this->source->get('products', $params);

        $count = 0;
        foreach ($products as $originalProduct) {
            UploadImage::dispatch($originalProduct);
            $count++;
        }

        if ($count > 0) {
            $this->currentPage++;
            CopyImages::dispatch($this->currentPage)->delay(now()->addSeconds(10));;
        }
    }
}
