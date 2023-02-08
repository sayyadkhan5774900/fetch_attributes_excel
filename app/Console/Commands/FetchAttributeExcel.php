<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class FetchAttributeExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attribute:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $allAspects = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $message = "Attribute: fetcher Command Started";
        Log::debug($message); echo $message ."\n";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();
        //Import the Excel Data using API

        $dir = "C:\laragon\www\FetchAttributeExcel\public\attributes";

        $files = scandir($dir);

        // $files = ["Abdominal_Exercisers_15274_AspectFinder_Basic_176514.csv"];
        foreach ($files as $key => $filename) {
            $file = "C:\laragon\www\FetchAttributeExcel\public\attributes/" . $filename;

            if(file_exists($file)) {
                $this->processFile($file);
            } else {
                $message = "Sears: File Not Found !!";
                Log::debug($message); echo $message ."\n";
            }
        }

        $allAspectsJson = json_encode($this->allAspects);

        @file_put_contents("C:\\Users\\sayyad\\Desktop\\aspects.json", $allAspectsJson);

        $message = "Sears: product status job Ended";
        Log::debug($message); echo $message ."\n";
    }


    public function processFile($file)
    {
        $fileExtension = (new \SplFileInfo($file))->getExtension();
        if ($fileExtension == 'xsl' || $fileExtension == 'xlsx' || $fileExtension == 'csv') {
            Excel::filter('chunk')->load($file)->chunk(1000, function($sheets) use ($file){
                $product_ids = 0;
                $aspects = [];
                foreach ($sheets as $key => $sheet) {
                    $product_id = strtok($sheet['sku'], '.');
                    if(is_numeric($product_id) && strlen($product_id) == 6)
                    {
                        foreach ($sheet as $key => $value) {
                            if($key == "item_id" || $key == "sku"){
                                continue;
                            } else {
                                if($value != null){
                                    $aspects[$key] = $value;
                                }
                            }
                        }

                        $product_ids++;
                        $this->allAspects[$product_id] = $aspects;
                    }
                }

                if($product_ids){
                    $message = "Attributes: Total products whose aspects are found are = " . $product_ids;
                    Log::debug($message); echo $message ."\n";
                    $message = $file;
                    Log::debug($message); echo $message ."\n";
                }

            },false);
        }
    }
}
