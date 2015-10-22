<?php

class AdminController extends ApplicationController {
	static $session = "on";
	static $before_filter = array(
		"authenticate_user",
		"require_logged_in_user" => array("except" => array("login", "auth")),
	);

	static $verify = array(
		array("method" => "post",
			"only" => array("auth", "logout", "update_notes",
				"update_show_settings"),
			"redirect_to" => ADMIN_ROOT,
		),
	);

	static $filter_parameter_logging = array("password");

	public function index() {
		$this->episodes = Episode::find("all");
	}

	public function login() {
		$this->page_title = "Login";
	}

	public function auth() {
		if ($user = User::find_by_username($this->params["username"])) {
			if ($user->password_matches($this->params["password"])) {
				$_SESSION["user_id"] = $user->id;
				HalfMoon\Log::info("logged in as " . $user->username);
				return $this->redirect_to(ADMIN_ROOT);
			}

			HalfMoon\Log::error("logged failed for " . $user->username);
		}

		$this->add_flash_error("Invalid username and/or password.");
		return $this->render(array("action" => "login"));
	}

	public function logout() {
		if ($this->user)
			session_destroy();

		$this->add_flash_notice("You have been logged out.");

		return $this->redirect_to("/");
	}

	public function update_notes() {
		$this->user->upcoming_notes = $this->params["upcoming_notes"];
		$this->user->save();

		$this->add_flash_success("Your upcoming show notes have been saved.");
		return $this->redirect_to(ADMIN_ROOT);
	}

	public function show_settings() {
		$this->settings = $this->settings();
	}

	public function update_show_settings() {
		$this->settings = $this->settings();
		if ($this->settings->update_attributes($this->params["settings"])) {
			$this->add_flash_success("Show settings have been updated.");
			return $this->redirect_to(ADMIN_ROOT);
		}
		else
			$this->render(array("action" => "show_settings"));
	}
}

?>
