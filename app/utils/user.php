<?php
    require_once("func.php");
    class User {
        private $f = NULL;
        public function __construct($f=null) {
			if($f != null) {
				$this->f = $f;
			} else {
				$this->f = new Func();
			}
        }
		
		public function getUserPermissions($id) {
			$query = "SELECT
						  is_user_admin, is_site_admin
						FROM
						  Users u
						WHERE
						  id = :id";
			$results = $this->f->fetchAndExecute($query, array(":id"=>$id));
			
            return count($results) > 0 ? $results[0] : NULL;
        }
    }
?>
