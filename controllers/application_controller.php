<?php
/*
	application controller from which all other controllers will extend.  use
	this for site-wide functions like authentication, before_filters, etc.
*/

class ApplicationController extends HalfMoon\ApplicationController {
	/* sessions are off by default to allow caching */
	static $session = "off";

	protected function authenticate_user() {
		if (isset($_SESSION["user_id"]))
			$this->user = User::find_by_id($_SESSION["user_id"]);

		return true;
	}

	protected function require_logged_in_user() {
		if ($this->user)
			return true;

		return $this->redirect_to(ADMIN_ROOT . "login");
	}

	protected function settings() {
		if (!$this->_settings)
			$this->_settings = Settings::fetch();

		return $this->_settings;
	}
}

?>
