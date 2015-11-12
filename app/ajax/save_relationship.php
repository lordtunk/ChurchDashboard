<?php
    class ValidationException extends Exception { }
    
    session_start();
    include("func.php");
    $f = new Func();
    $relationship = json_decode($_POST['relationship']);
    $dict = array();
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
        $dict['success'] = FALSE;
        $f->logMessage('Session information missing');
    } else {
        $session_id = $_SESSION['session_id'];
        $user_id = $_SESSION['user_id'];

        try {
          $dict['success'] = $f->isLoggedIn($user_id, $session_id);
        } catch (Exception $e) {
          $dict['success'] = FALSE;
          $f->logMessage($e->getMessage());
        }
    }
    if($dict['success'] == TRUE) {
        $dict['success'] = FALSE;
        try {
            $f->useTransaction = FALSE;
            $f->beginTransaction();
            if($relationship->id < 0) {
                if(hasRelationship($f, $relationship->person_id, $relationship->relation_id))
                    throw new ValidationException('Relationship already exists');
                $query = "INSERT INTO Relationships 
                            (person_id, relation_id, type)
                          VALUES
                            (:person_id, :relation_id, :type)";
                $relationship->id = $f->queryLastInsertId($query, 
                    array(":person_id"=>$relationship->person_id,
                          ":relation_id"=>$relationship->relation_id,
                          ":type"=>$relationship->typeCd));
                if($relationship->spouseId !== '') {
                    if(hasRelationship($f, $relationship->spouseId, $relationship->relation_id))
                        throw new ValidationException('Spouse already has this relationship');
                    $f->queryLastInsertId($query, 
                        array(":person_id"=>$relationship->spouseId,
                              ":relation_id"=>$relationship->relation_id,
                              ":type"=>$relationship->typeCd));
                } else if($relationship->typeCd == "1") {
                    if(hasRelationship($f, $relationship->relation_id, $relationship->person_id))
                        throw new ValidationException('Spouse already has this relationship');
                    $f->queryLastInsertId($query, 
                        array(":person_id"=>$relationship->relation_id,
                              ":relation_id"=>$relationship->person_id,
                              ":type"=>$relationship->typeCd));
                } 
                if($relationship->typeCd == "2") {
                    if(hasRelationship($f, $relationship->relation_id, $relationship->person_id))
                        throw new ValidationException('Child already has this relationship');
                    $f->queryLastInsertId($query, 
                        array(":person_id"=>$relationship->relation_id,
                              ":relation_id"=>$relationship->person_id,
                              ":type"=>3));
                    
                    if($relationship->spouseId !== '') {
                        if(hasRelationship($f, $relationship->relation_id, $relationship->spouseId))
                            throw new ValidationException('Spouse already has this relationship');
                        $f->queryLastInsertId($query, 
                            array(":person_id"=>$relationship->relation_id,
                                  ":relation_id"=>$relationship->spouseId,
                                  ":type"=>3));
                    }
                } else if($relationship->typeCd == "3") {
                    if(hasRelationship($f, $relationship->relation_id, $relationship->person_id))
                        throw new ValidationException('Parent already has this relationship');
                    $f->queryLastInsertId($query, 
                        array(":person_id"=>$relationship->relation_id,
                              ":relation_id"=>$relationship->person_id,
                              ":type"=>2));
                }
            } else {
                $query = "
                    UPDATE Relationships SET 
                        relation_id = :relation_id,
                        type = :type
                      WHERE
                        id=:relationship_id";
                $results = $f->executeAndReturnResult($query, 
                    array(":relation_id"=>$relationship->relation_id,
                      ":type"=>$relationship->typeCd,
                      ":relationship_id"=>$relationship->id));
            }
            $f->commit();
            $dict['relationship_id'] = $relationship->id;
            $dict['success'] = TRUE;
        } catch (ValidationException $ve) {
          $dict['success'] = FALSE;
          $dict['msg']= $ve->getMessage();
          $f->rollback();
        } catch (Exception $e) {
          $dict['success'] = FALSE;
          $dict['exception']= $e->getMessage();
          $f->rollback();
        }
    } else {
        $dict['error'] = 1;
    }
    echo json_encode($dict);
    
    function hasRelationship($f, $personId, $relationId) {
        $query = "SELECT 
                    count(*) cnt
                  FROM 
                    Relationships 
                  WHERE 
                    person_id=:person_id 
                    AND relation_id=:relation_id";
        $results = $f->fetchAndExecute($query, array(":person_id"=>$personId, ":relation_id"=>$relationId));
        return $results[0]['cnt'] == 0 ? FALSE : TRUE;
    }
?>