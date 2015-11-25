<?php
    require_once("validation_exception.php");
    class Person {
        public $id = -1;
        private $adult = 0;
        public $first = 0;
        public $second = 0;
        private $first_name = NULL;
        private $last_name = NULL;
        private $display = NULL;
        private $has_name = FALSE;
        private $f = NULL;
        public function __construct($person, $func) {
            $this->id = $person->id;
            // Translate TRUE/FALSE to 1/0 so that log statements
            // are easier to read since FALSE does not display
            $this->adult = $person->adult ? 1 : 0;
            $this->first = $person->first ? 1 : 0;
            $this->second = $person->second ? 1 : 0;
            $this->first_name = isset($person->first_name) ? trim($person->first_name) : "";
            $this->last_name = isset($person->last_name) ? trim($person->last_name) : "";
            $this->display = isset($person->display) ? trim($person->display) : "";
            
            $this->f = $func;
            
            $this->getNameFromDisplay();
        }
        
        public function save($user_id) {
            if($this->id < 0)
                $this->insert($user_id);
            else
                $this->update();
        }
        
        public function insert($user_id) {
            $this->validate();
            if($this->has_name) {
                $query = "INSERT INTO People (last_name, first_name, adult, last_modified_dt, modified_by, creation_dt, created_by) VALUES 
                (:last_name, :first_name, :adult, NOW(), :user_id, NOW(), :user_id)";
                $this->id = $this->f->queryLastInsertId($query, array(":last_name"=>$this->last_name, ":first_name"=>$this->first_name, ":adult"=>$this->adult, ":user_id"=>$user_id));
            } else {
                $query = "INSERT INTO People (description, adult, last_modified_dt, modified_by, creation_dt, created_by) VALUES 
                (:description, :adult, NOW(), :user_id, NOW(), :user_id)";
                $this->id = $this->f->queryLastInsertId($query, array(":description"=>$this->display, ":adult"=>$this->adult, ":user_id"=>$user_id));
            }
        }
        
        public function update() {
            $query = "UPDATE People SET active=true WHERE id=:id";
            $this->f->executeAndReturnResult($query, array(":id"=>$this->id));
        }
        
        private function validate() {
            if(strlen($this->first_name) == 0 && strlen($this->last_name) == 0 && strlen($this->display) == 0)
                throw new ValidationException('Must specify a name or display value for a person');
            if(strlen($this->first_name) > 50)
                throw new ValidationException('First name cannot exceed 50 characters');
            if(strlen($this->last_name) > 50)
                throw new ValidationException('Last name cannot exceed 50 characters');
            if(strlen($this->display) > 250)
                throw new ValidationException('Display cannot exceed 250 characters');
        }
        
        private function getNameFromDisplay() {
            if(strlen($this->first_name) > 0 || strlen($this->last_name) > 0) {
                $this->has_name = TRUE;
                return;
            }
            if(strlen($this->display) == 0) return;
            $pos = strpos($this->display, ",");
            if($pos == FALSE) return;
            
            $names = explode(",", $this->display);
            $this->last_name = trim($names[0]);
            $this->first_name = trim($names[1]);
            $this->has_name = TRUE;
        }
    }
?>