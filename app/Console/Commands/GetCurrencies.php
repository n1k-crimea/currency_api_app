<?php

namespace App\Console\Commands;

use App\Models\Currency;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GetCurrencies extends Command
{
    private $path;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get currencies from cbr.ru/scripts/XML_daily.asp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->path = config('currency.url_external_api');
        parent::__construct();
    }

    public function handle()
    {
        $currencies = [];
        $json = json_encode($this->getData());
        $phpArray = json_decode($json, true);
        $this->check($phpArray);
        foreach ($phpArray['Valute'] as $keyElem => $arrDataElem) {
            $currencies[$keyElem]['name'] = $arrDataElem['Name'];
            $currencies[$keyElem]['rate'] = $this->toFloat($arrDataElem['Nominal']) === 1 ?
                $this->toFloat($arrDataElem['Value']) : $this->toFloat($arrDataElem['Value']) / $this->toFloat($arrDataElem['Nominal']);
            $currencies[$keyElem]['created_at'] = Carbon::create($phpArray['@attributes']['Date']);
        }
        Currency::insert($currencies);
        $this->info('The command was successful!');
    }

    public function getData()
    {
        $guzzleClient = new Client();
        $response = $guzzleClient->get($this->path);
        $body = $response->getBody();
        $body->seek(0);
        $size = $body->getSize();
        $file = $body->read($size);
        $xml = simplexml_load_string($file);
        return $xml;
    }

    public function toFloat($stringValue)
    {
       return floatval(str_replace(',', '.', $stringValue));
    }

    public function check($phpArray)
    {
        if (Carbon::today()->format('d.m.Y') !== Carbon::create($phpArray['@attributes']['Date'])) {
            $this->info('Currency exchange rate not updated!');
            die();
        }
    }
}


