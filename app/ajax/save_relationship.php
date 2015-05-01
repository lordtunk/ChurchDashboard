<?php
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
          
            if($relationship->id < 0) {
                $query = "INSERT INTO Relationships 
                            (person_id, relation_id, type)
                          VALUES
                            (:person_id, :relation_id, :type)";
                $relationship->id = $f->queryLastInsertId($query, 
                    array(":person_id"=>$relationship->person_id,
                          ":relation_id"=>$relationship->relation_id,
                          ":type"=>$relationship->typeCd));
                if($relationship->spouseId !== '') {
                    $f->queryLastInsertId($query, 
                        array(":person_id"=>$relationship->spouseId,
                              ":relation_id"=>$relationship->relation_id,
                              ":type"=>$relationship->typeCd));
                } else if($relationship->typeCd == "1") {
                    $f->queryLastInsertId($query, 
                        array(":person_id"=>$relationship->relation_id,
                              ":relation_id"=>$relationship->person_id,
                              ":type"=>$relationship->typeCd));
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

            $dict['relationship_id'] = $relationship->id;
            $dict['success'] = TRUE;
        } catch (Exception $e) {
          $dict['success'] = FALSE;
          $dict['msg']= $e->getMessage();
          $f->rollback();
        }
    } else {
        $dict['error'] = 1;
    }
    echo json_encode($dict);
?>