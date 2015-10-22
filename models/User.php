<?php

class User extends ActiveRecord\Model {
	public $new_password;
	public $new_password_confirmation;

	static $attr_accessible = array(
		"new_password", "new_password_confirmation",
	);

	public function set_password($password) {
		$pw = new PasswordHash(8, FALSE);
		$this->hashed_password = $pw->HashPassword($password);
	}

	public function password_matches($password) {
		$pw = new PasswordHash(8, FALSE);
		return (bool)($pw->CheckPassword($password, $this->hashed_password));
	}
}

?>
