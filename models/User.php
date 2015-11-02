<?php

class User extends ActiveRecord\Model {
	public $new_password;
	public $new_password_confirmation;

	static $attr_accessible = array(
		"username", "full_name", "twitter_username", "url", "upcoming_notes",
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

	public function validate() {
		if (trim($this->new_password) == "") {
			if (!$this->id)
				$this->errors->add("new_password", "cannot be blank.");
		}
		else {
			if ($this->new_password === $this->new_password_confirmation)
				$this->set_password($this->new_password);
			else
				$this->errors->add("new_password", "does not match "
					. "confirmation.");
		}
	}

	public function get_preferred_url() {
		if (empty($this->url))
			return "https://twitter.com/" . $this->twitter_username;
		else
			return $this->url;
	}

	public function get_new_totp() {
		$secret = openssl_random_pseudo_bytes(40);
		$this->totp_secret = \Base32\Base32::encode($secret);
		return $this->totp;
	}

	public function get_totp() {
		$totp = new \OTPHP\TOTP;
		$totp->setLabel($this->username)
			->setDigits(6)
			->setDigest("sha1")
			->setInterval(30)
			->setIssuer(Settings::fetch()->name . " Admin")
			->setSecret($this->totp_secret);

		return $totp;
	}
}

?>
