<?php
/*
	application controller from which all other controllers will extend.  use
	this for site-wide functions like authentication, before_filters, etc.
*/

class ApplicationController extends HalfMoon\ApplicationController {
	/* sessions are off by default to allow caching */
	static $session = "off";

	public function hosts() {
		$users = User::find("all");

		usort($users, function ($a, $b) {
			return strnatcasecmp($a->full_name, $b->full_name);
		});

		return $users;
	}

	protected function authenticate_user() {
		if (isset($_SESSION["user_id"]))
			$this->user = User::find_by_id($_SESSION["user_id"]);

		return true;
	}

	protected function require_logged_in_user() {
		if ($this->user)
			return true;

		return $this->redirect_to(ADMIN_ROOT_URL . "login");
	}

	protected function settings() {
		if (!$this->_settings)
			$this->_settings = Settings::fetch();

		return $this->_settings;
	}

	protected function flush_cache() {
		if (\HalfMoon\Utils::is_blank(\HalfMoon\Config::instance()->cache_store_path))
			return false;

		$deleted = 0;

		$fs = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(\HalfMoon\Config::instance()->cache_store_path),
			RecursiveIteratorIterator::SELF_FIRST);
		foreach($fs as $name => $object) {
			if (is_file($name)) {
				unlink($name);
				$deleted++;
			}
		}

		$this->add_flash_success("Deleted " . $deleted . " cached file"
			. ($deleted == 1 ? "" : "s"));

		return true;
	}
}

?>
