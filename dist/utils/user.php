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
						  id,
						  username, 
						  homepage,
						  is_user_admin,
						  is_site_admin
						FROM
						  Users u
						WHERE
						  id = :id";
			$results = $f->fetchAndExecute($query, array(":id"=>$id));
			
            return count($results) > 0 ? $results[0] : NULL;
        }
		
		public static function getUsers($f) {
			$query = "SELECT
						  id,
						  username,
						  is_user_admin,
						  is_site_admin
						FROM
						  Users u";
			$results = $f->fetchAndExecute($query);
			
            return $results;
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
		
		public static function createUser($user, $f) {
			$user->id = 0;
			self::validateCreate($user);
			if(self::usernameInUse($user, $f))
				throw new Exception("Username is already in use");
			
			$queryParams = array(
							":username"=>$user->username, 
							":homepage"=>$user->homepage, 
							":password"=>PasswordStorage::create_hash($user->password), 
							":is_user_admin"=>$user->is_user_admin);
			
			$query = "
					INSERT INTO Users 
						(username, homepage, password, is_user_admin, is_site_admin)
					VALUES
						(:username, :homepage, :password, :is_user_admin";
			if(isset($user->is_site_admin)) {	
				$queryParams[":is_site_admin"] = $user->is_site_admin;
				$query .= ", :is_site_admin)";
			} else {
				$query .= ")";
			}
			
			$user->id = $f->queryLastInsertId($query, $queryParams);
        }
		
		public static function updateUser($user, $f) {
			self::validateUpdate($user);
			if(self::usernameInUse($user, $f))
				throw new Exception("Username is already in use");
			
			$queryParams = array(":username"=>$user->username);
			$query = "
					UPDATE Users SET
						username = :username";
			
			if(isset($user->homepage)) {
				$queryParams[":homepage"] = $user->homepage;
				
				$query .= ",
						homepage = :homepage";
			}
			if(isset($user->is_user_admin)) {
				$queryParams[":is_user_admin"] = $user->is_user_admin;
				
				$query .= ",
						is_user_admin = :is_user_admin";
			}
			if(isset($user->is_site_admin)) {
				$queryParams[":is_site_admin"] = $user->is_site_admin;
				
				$query .= ",
						is_site_admin = :is_site_admin";
			}
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
		
		private static function validateUpdate($user) {
			if($user->id == NULL || $user->id < 0) {
                throw new Exception('User information missing');
            }
			
			if(isset($user->is_user_admin)) {
				$user->is_user_admin = $user->is_user_admin ? 1 : 0;				
			}
			if(isset($user->is_site_admin)) {
				$user->is_site_admin = $user->is_site_admin ? 1 : 0;
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
		
		private static function validateCreate($user) {
			$user->username = trim($user->username);
			$user->homepage = trim($user->homepage);
						
			if(isset($user->is_user_admin)) {
				$user->is_user_admin = $user->is_user_admin ? 1 : 0;				
			}
			if(isset($user->is_site_admin)) {
				$user->is_site_admin = $user->is_site_admin ? 1 : 0;
			}
			
            $msg = "";
            if($user->username === "")
                $msg .= 'Username cannot be empty<br />';
			else if(strlen($user->username) > 100)
                $msg .= 'Username cannot exceed 100 characters<br />';
			if($user->homepage === "")
                $msg .= 'Homepage cannot be empty<br />';
			else if(strlen($user->homepage) > 100)
                $msg .= 'Homepage cannot exceed 100 characters<br />';
			else if($user->password != $user->confirm_password) 
				$msg .= 'Passwords do not match<br />';
			
			if(strlen($msg) > 0)
                throw new Exception($msg);
		}
		
		public static function resetPassword($userId, $f) {
			$query = "
					UPDATE Users SET
						password = :password
					WHERE
						id = :id";
			
			$passwordText = self::generateRandomString();
			$password = hash('sha256', $passwordText);
			$passwordHash = PasswordStorage::create_hash($password);
			$queryParams = array(":id"=>$userId, ":password"=>$passwordHash);
			$f->executeAndReturnResult($query, $queryParams);
			return $passwordText;
        }
		
		private static function generateRandomString($length = 8) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}
		
		public static function deleteUser($userId, $f) {
			$queryParams = array(":id"=>$userId);
			$query = "
					UPDATE People SET modified_by = 0 WHERE modified_by = :id";
			$f->executeAndReturnResult($query, $queryParams);
			$query = "
					UPDATE People SET created_by = 0 WHERE created_by = :id";
			$f->executeAndReturnResult($query, $queryParams);
			$query = "
					UPDATE FollowUps SET modified_by = 0 WHERE modified_by = :id";
			$f->executeAndReturnResult($query, $queryParams);
			$query = "
					UPDATE FollowUps SET created_by = 0 WHERE created_by = :id";
			$f->executeAndReturnResult($query, $queryParams);
			$query = "
					DELETE FROM Users WHERE id = :id";
			$f->executeAndReturnResult($query, $queryParams);
        }
    }
?>
