<?php


namespace Main\Models;


class War extends BaseModel
{

    public function updateOrCreate($values)
    {
        $stmt = $this->connection->prepare("
              
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


        $stmt->closeCursor();

    }

}