<?php
    require_once("func.php");
	require_once("password_storage.php");
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
		
		public static function getUser($id, $f) {
			$query = "SELECT
						  username, 
						  homepage
						FROM
						  Users u
						WHERE
						  id = :id";
			$results = $f->fetchAndExecute($query, array(":id"=>$id));
			
            return count($results) > 0 ? $results[0] : NULL;
        }
		
		public static function usernameInUse($user, $f) {
			$query = "SELECT
						  id
						FROM
						  Users u
						WHERE
						  UPPER(username) = :username";
			$results = $f->fetchAndExecute($query, array(":username"=>$user->username));
			
			if(count($results) == 0)
				return FALSE;
			
			return $results[0]["id"] != $user->id;
        }
		
		private static function validatePassword($user, $f) {
			if(trim($user->oldPassword) == "") throw new Exception("Must verify existing password");
			if(trim($user->newPassword) == "") throw new Exception("Cannot set password to empty");
			
			$query = "SELECT password FROM Users WHERE id=:id";
			$results = $f->fetchAndExecute($query, array(":id"=>$user->id));
			if(count($results) == 0) {
				throw new Exception("Invalid user");
			} 
			if(!PasswordStorage::verify_password($user->oldPassword, $results[0]['password'])) {
				throw new Exception("Invalid password");
			}
        }
		
		public static function updateUser($user, $f) {
			self::validateUser($user);
			if(self::usernameInUse($user, $f))
				throw new Exception("Username is already in use");
			
			$queryParams = array(":username"=>$user->username, ":homepage"=>$user->homepage);
			$query = "
					UPDATE Users SET
						username = :username, 
						homepage = :homepage";
			
			if(isset($user->oldPassword) && isset($user->newPassword)) {
				self::validatePassword($user, $f);
				$queryParams[":password"] = PasswordStorage::create_hash($user->newPassword);
				
				$query .= ",
						password = :password";
			}
			
			$queryParams[":id"] = $user->id;
			$query .= "
					WHERE
						id = :id";
			$f->executeAndReturnResult($query, $queryParams);
        }
		
		private static function validateUser($user) {
			if($user->id == NULL || $user->id < 0) {
                throw new Exception('User information missing');
            }
			
			$user->username = trim($user->username);
			$user->homepage = trim($user->homepage);
			
            $msg = "";
            if($user->username === "")
                $msg .= 'Username cannot be empty<br />';
			else if(strlen($user->username) > 100)
                $msg .= 'Username cannot exceed 100 characters<br />';
			if($user->homepage === "")
                $msg .= 'Homepage cannot be empty<br />';
			else if(strlen($user->homepage) > 100)
                $msg .= 'Homepage cannot exceed 100 characters<br />';
			
			if(strlen($msg) > 0)
                throw new Exception($msg);
		}
    }
?>
