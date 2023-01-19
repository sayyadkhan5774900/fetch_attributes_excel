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

        $message = "Sears: product status job started";
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

        $attr_string = 's:5:"brand";s:5:"Intel";s:15:"processor_model";s:12:"CORE I7-2600";s:3:"mpn";a:3:{i:0;s:13:"BX80623I72600";i:1;s:15:"CM8062300834302";i:2;s:14:"BXC80623I72600";}s:14:"processor_type";s:16:"Core i7 2nd Gen.";s:11:"socket_type";s:18:"LGA 1155/Socket H2";s:15:"number_of_cores";i:4;s:9:"bus_speed";s:16:"Dmi SPEED-5 Gt/S";s:11:"clock_speed";s:6:"3.4GHz";s:8:"l2_cache";s:5:"256KB";s:8:"l3_cache";s:4:"8 MB";';

        $data = unserialize($attr_string);

        dd($data);

        @file_put_contents("C:\\Users\\sayyad\\Desktop\\aspects_old.json", $data);
        exit;
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
                        // dd($sheet);
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
