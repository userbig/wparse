<?php

namespace Main\Threaded\Workers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;

class AllWarsWorker extends \Threaded
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function run()
    {
        $host = getenv('DB_HOST');

        $dbname = getenv('DB_DATABASE');

        $dbuser = getenv('DB_USERNAME');

        $dbpass = getenv('DB_PASSWORD');

        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $dbuser, $dbpass);

        $client = new Client();

        do {
            $max = null;
            $min = null;
            $array = null;

            $start = microtime(true);

            $log = new Logger('name');
            $log->pushHandler(new StreamHandler('work.log', Logger::DEBUG));

            $provider = $this->worker->getProvider();

            $provider->synchronized(function ($provider) use (&$max, &$min, &$array) {
                $array = $provider->getNext();
                $max = $array['maxWarId'];
                $min = ($max < 2000) ? 0 : $max - 2000;
            }, $provider);

            if ($max === null) {
                continue;
            }

            $log->info("work started on thread $this->id " . microtime(true) . "processed pages atm: " . $array['processed'] . ' of ' . $array['totalPages']);


            for ($j = $max; $j > $min; $j--) {

                $url = 'https://esi.evetech.net/latest/wars/' . $j . '/?datasource=tranquility';


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
                        echo PHP_EOL . 'ERROR repeat in 5sec' . $try . PHP_EOL;
                        echo PHP_EOL . $e . PHP_EOL;
                        if ($e->getCode() === 422) {
                            $war = null;
                            break;
                        }
                        $att++;
                        sleep(1);
                        continue;

                    }
                    break;
                } while ($att < $try);


                if ($war === null) {
                    $log->warning("this was not in list; war_id: $j ");
                    continue;
                }


                $values = [
                    'war_id' => $war->id,
                    'aggressor_id' => isset($war->aggressor->alliance_id) ? $war->aggressor->alliance_id : $war->aggressor->corporation_id,
                    'aggressor' => $aggressor = json_encode($war->aggressor),
                    'allies' => isset($war->allies) ? json_encode($war->allies) : null,
                    'defender_id' => isset($war->defender->alliance_id) ? $war->defender->alliance_id : $war->defender->corporation_id,
                    'defender' => json_encode($war->defender),
                    'mutual' => json_encode($war->mutual),
                    'open' => json_encode($war->open_for_allies),
                    'declared' => date("Y-m-d H:i:s", strtotime($war->declared)),
                    'started' => date("Y-m-d H:i:s", strtotime($war->started)),
                    'finished' => isset($war->finished) ? date("Y-m-d H:i:s", strtotime($war->finished)) : null,
                    'last' => date("Y-m-d H:i:s")

                ];

                $stmt = $pdo->prepare("
              
              INSERT INTO wars(aggressor_id, aggressor, allies, declared, defender_id,
                      defender, finished, war_id, mutual, open_for_allies, started, last_api_update)
                    VALUES (:aggressor_id, :aggressor, :allies, :declared, :defender_id, :defender, :finished, :war_id, :mutual, :open_for_allies, :started, :last_api_update)
                    ON CONFLICT (war_id) DO UPDATE 
                        SET aggressor_id=excluded.aggressor_id, aggressor=excluded.aggressor, allies=excluded.allies, declared=excluded.declared, defender_id=excluded.defender_id,
                            defender=excluded.defender, finished=excluded.finished, mutual=excluded.mutual, open_for_allies=excluded.open_for_allies, started=excluded.started, 
                            last_api_update=excluded.last_api_update;
              
              ");


                $stmt->bindValue(':aggressor_id', $values['aggressor_id']);
                $stmt->bindValue(':aggressor', $values['aggressor']);
                $stmt->bindValue(':allies', $values['allies']);
                $stmt->bindValue(':declared', $values['declared']);
                $stmt->bindValue(':defender_id', $values['defender_id']);
                $stmt->bindValue(':defender', $values['defender']);
                $stmt->bindValue(':finished', $values['finished']);
                $stmt->bindValue(':war_id', $values['war_id']);
                //        bool
                $stmt->bindValue(':mutual', $values['mutual']);
                $stmt->bindValue(':open_for_allies', $values['open']);
                //        timestamp
                $stmt->bindValue(':finished', $values['finished']);
                $stmt->bindValue(':started', $values['started']);
                $stmt->bindValue(':last_api_update', $values['last']);
                $r = $stmt->execute();
//                dd($stmt);

                $stmt->closeCursor();

//                die();

            }
            $end = microtime(true);
            $log->info("Thread $this->id finished work in " . ($end - $start) / 60 . ' mins: ' . microtime(true));
            echo PHP_EOL . 'ITERATION COMPLETE' . PHP_EOL;
        } while ($max !== null);

        $log->info("Thread $this->id is done");

    }


}