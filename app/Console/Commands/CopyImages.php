<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CopyImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy woocommerce images';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \App\Jobs\CopyImages::dispatch();
        return 0;
    }
}
