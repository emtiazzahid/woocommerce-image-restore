<?php

namespace App\Jobs;

use App\Models\UploadRecord;
use Automattic\WooCommerce\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class UploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    /**
     * @var
     */
    public $sourceProduct;
    /**
     * @var array
     */
    public $destinationProduct;
    /**
     * @var Client
     */
    private $destination;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product)
    {
        $this->sourceProduct = $product;

        $this->destination = new Client(
            config('woocommerce.destination.site_url'),
            config('woocommerce.destination.key'),
            config('woocommerce.destination.secret'),
            [
                'version' => config('woocommerce.destination.version'),
                'timeout' => config('woocommerce.destination.timeout')
            ]
        );

        $this->verifyProduct();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        if (!$this->destinationProduct || !$this->sourceProduct->images || count($this->sourceProduct->images) < 1) {
            logger('skipped for: ');
            logger($this->sourceProduct->id);
            return;
        }

        $imageIds = [];
        foreach ($this->sourceProduct->images as $image) {
            $imageIds[]['id'] = $this->uploadMedia($image);
        }
        $data = [
            'images' => $imageIds
        ];

        $this->destination->put('products/' . $this->destinationProduct->id, $data);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function uploadMedia($file) {
        /*
         * check if image already uploaded
         */
        $record = UploadRecord::where('source_image_id', $file->id)->first();
        if ($record) {
            return $record->destination_image_id;
        }
        /*
         * TO GET NONCE: browser console: wpApiSettings : nonce
         * TO GET COOKIE: console > application > cookies > copy key value: wordpress_logged_in_*********
         * Ref: oasisworkflow.com/how-to-authenticate-wp-rest-apis-with-postman
         */
        $client = new \GuzzleHttp\Client([
            'base_uri' => config('woocommerce.destination.site_url') . "wp-json/mu_helper/v1/",
            'timeout'  => config('woocommerce.destination.timeout'),
            "Accept" => "application/json",
            "Content-Type" => "application/json",
        ]);

        $data = $client->post('media', [
            'form_params' => [
                'file' => $file
            ]
        ]);

        $result = json_decode($data->getBody() ,true);

        $imageId = Arr::get($result, 'id');

        UploadRecord::create([
            'product_id' => $this->sourceProduct->id,
            'source_image_id' => $file->id,
            'destination_image_id' => $imageId
        ]);

        return $imageId;
    }

    public function verifyProduct()
    {
        $result = $this->destination->get('products',[
            'slug' => $this->sourceProduct->slug
        ]);

        $this->destinationProduct = Arr::get($result,'0');
    }
}
