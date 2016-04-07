<?php

class AdminController extends ApplicationController {
	static $session = "on";
	static $before_filter = array(
		"authenticate_user",
		"require_logged_in_user" => array("except" => array("login", "auth",
			"auth2")),
	);

	static $verify = array(
		array("method" => "post",
			"only" => array("auth", "logout", "update_notes",
				"update_show_settings"),
			"redirect_to" => ADMIN_ROOT_URL,
		),
	);

	static $filter_parameter_logging = array("password", "totp_secret",
		"totp_code");

	public function auth() {
		if ($user = User::find_by_username($this->params["username"])) {
			if ($user->hashed_password == "" ||
			$user->password_matches($this->params["password"])) {
				$_SESSION["auth_user_id"] = $user->id;
				HalfMoon\Log::info("authenticated as " . $user->username);
				return $this->render(array("action" => "auth2"),
					array("auth_user" => $user));
			}

			HalfMoon\Log::error("logged failed for " . $user->username);
		}

		$this->add_flash_error("Invalid username and/or password.");
		return $this->render(array("action" => "login"));
	}

	public function auth2() {
		if (!$_SESSION["auth_user_id"])
			return $this->redirect_to(ADMIN_ROOT_URL . "login");

		$user = User::find($_SESSION["auth_user_id"]);
		if (empty($user->totp_secret) ||
		($this->params["totp_code"] &&
		$user->totp->verify($this->params["totp_code"]))) {
			if (empty($user->totp_secret)) {
				$user->totp_secret = $this->params["totp_secret"];
				$user->save();
			}

			$_SESSION["user_id"] = $_SESSION["auth_user_id"];
			unset($_SESSION["auth_user_id"]);
			return $this->redirect_to(ADMIN_ROOT_URL);
		}

		$this->add_flash_error("Invalid TOTP code");
		$this->render(array("action" => "auth2"), array("auth_user" => $user));
	}

	public function flushcache() {
		if (\HalfMoon\Utils::is_blank(\HalfMoon\Config::instance()->cache_store_path))
			return redirect_to(ADMIN_ROOT_URL);

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

		return $this->redirect_to(ADMIN_ROOT_URL);
	}

	public function index() {
		if ($this->user->hashed_password == "")
			return $this->redirect_to(ADMIN_ROOT_URL . "profile");

		$this->find_other_users();

		$this->episodes = Episode::find("all",
			array("order" => "episode DESC"));
	}

	public function login() {
		$this->page_title = "Login";
	}

	public function logout() {
		if ($this->user)
			session_destroy();

		$this->add_flash_notice("You have been logged out.");

		return $this->redirect_to("/");
	}

	public function show_settings() {
	}

	public function update_notes() {
		$this->user->upcoming_notes = $this->params["upcoming_notes"];
		$this->user->save();

		$this->add_flash_success("Your upcoming show notes have been saved.");
		return $this->redirect_to(ADMIN_ROOT_URL);
	}

	public function other_notes() {
		$this->find_other_users();

		return $this->render(array("partial" => "notes", "layout" => false));
	}

	public function update_show_settings() {
		$this->settings = $this->settings();
		if ($this->settings->update_attributes($this->params["settings"])) {
			$this->add_flash_success("Show settings have been updated.");
			return $this->redirect_to(ADMIN_ROOT_URL);
		}
		else
			$this->render(array("action" => "show_settings"));
	}

	protected function find_other_users() {
		$this->other_users = User::find("all",
			array("conditions" => array("id <> ?", $this->user->id)));
	}
}

?>
