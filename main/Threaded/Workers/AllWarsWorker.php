<?php

namespace Main\Threaded\Workers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Main\Models\War;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AllWarsWorker extends \Threaded
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function run()
    {
        $client = new Client();

        do {
            $max = null;
            $min = null;
            $array = null;

            $start = microtime(true);

            $log = new Logger('name');
            $log->pushHandler(new StreamHandler('logs/work.log', Logger::DEBUG));

            $provider = $this->worker->getProvider();

            $provider->synchronized(function ($provider) use (&$max, &$min, &$array) {
                $array = $provider->getNext();
                $max = $array['maxWarId'];
                $min = ($max < 2000) ? 0 : $max - 2000;
            }, $provider);

            if ($max === null) {
                continue;
            }

            $log->info("Work started on thread $this->id ".microtime(true).' processed pages atm: '.$array['processed'].' of '.$array['totalPages']);
            pecho("Work started on thread $this->id ".microtime(true).' processed pages atm: '.$array['processed'].' of '.$array['totalPages']);

            for ($j = $max; $j > $min; $j--) {
                $url = 'https://esi.evetech.net/latest/wars/'.$j.'/?datasource=tranquility';

                if ($j <= 473146 and $j >= 472166) {
                    $log->warning("this was not in list; war_id: $j ");
                    continue;
                }

                $try = 10;
                $att = 0;
                do {
                    try {
                        $req = $client->request('GET', $url);
                        $war = json_decode($req->getBody());
                    } catch (RequestException $e) {
                        if ($e->getCode() === 502) {
                            echo PHP_EOL."\e[1;37;42m  ERROR repeat in 1 second. Dont worry. ESI some times randomly throws 502 error; Tries: ".$try."\e[0m\n";
                            echo PHP_EOL.$e.PHP_EOL;
                        } elseif ($e->getCode() === 422) {
                            $war = null;
                            break;
                        } else {
                            echo PHP_EOL.'ERROR repeat in 1 second; Tries: '.$try.PHP_EOL;
                            echo PHP_EOL.$e.PHP_EOL;
                        }
                        $att++;
                        sleep(1);
                        continue;
                    }
                    break;
                } while ($att < $try);

                if ($war === null) {
                    $log->warning("this was not in list; war_id: $j ");
                    pecho("This was not in list; war_id: $j ");
                    continue;
                }

                $values = [
                    'war_id'       => $war->id,
                    'aggressor_id' => isset($war->aggressor->alliance_id) ? $war->aggressor->alliance_id : $war->aggressor->corporation_id,
                    'aggressor'    => $aggressor = json_encode($war->aggressor),
                    'allies'       => isset($war->allies) ? json_encode($war->allies) : null,
                    'defender_id'  => isset($war->defender->alliance_id) ? $war->defender->alliance_id : $war->defender->corporation_id,
                    'defender'     => json_encode($war->defender),
                    'mutual'       => json_encode($war->mutual),
                    'open'         => json_encode($war->open_for_allies),
                    'declared'     => date('Y-m-d H:i:s', strtotime($war->declared)),
                    'started'      => date('Y-m-d H:i:s', strtotime($war->started)),
                    'finished'     => isset($war->finished) ? date('Y-m-d H:i:s', strtotime($war->finished)) : null,
                    'last'         => date('Y-m-d H:i:s'),

                ];

                $model = (new War())->updateOrCreate($values);
            }
            $end = microtime(true);
            $log->info("Thread $this->id finished work in ".($end - $start) / 60 .' mins: '.microtime(true));
            pecho("Thread $this->id finished work in ".($end - $start) / 60 .' mins: '.microtime(true));
            echo PHP_EOL.'ITERATION COMPLETE'.PHP_EOL;
        } while ($max !== null);

        $log->info("Thread $this->id is done");
    }
}
