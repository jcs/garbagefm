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
		$this->flush_cache();
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

	public function twitter_auth() {
		$rt = Twitter::new_request_token(ADMIN_ROOT_URL . "twitter_verify");

		$_SESSION["twitter_token"] = $rt["oauth_token"];
		$_SESSION["twitter_secret"] = $rt["oauth_token_secret"];

		try {
			return $this->redirect_to(Twitter::new_authorize_url(
				$rt["oauth_token"]));
		}
		catch (Exception $e) {
			\HalfMoon\Log::error("couldn't get url for twitter authorization: "
				. $e->getMessage());
			$this->add_flash_error("Could not add Twitter account.");
			return $this->redirect_to(ADMIN_ROOT_URL . "show_settings");
		}
	}

	/* we'll get back to here from a twitter.com redirect */
	public function twitter_verify() {
		$tt = @$_SESSION["twitter_token"];
		$ts = @$_SESSION["twitter_secret"];

		unset($_SESSION["twitter_token"]);
		unset($_SESSION["twitter_secret"]);

		if (!empty($this->params["denied"])) {
			/* user clicked cancel */
			$this->add_flash_notice("Twitter authentication canceled.");
			return $this->redirect_to(ADMIN_ROOT_URL . "show_settings");
		}

		try {
			if (empty($tt) || $this->params["oauth_token"] !== $tt)
				throw new Exception("invalid oauth token received back");

			if (Twitter::verify_oauth_credentials($tt, $ts,
			$this->params["oauth_verifier"])) {
				$this->add_flash_success("Twitter account has been "
					. "authenticated.");
				return $this->redirect_to(ADMIN_ROOT_URL . "show_settings");
			}
			else
				throw new Exception("verification failed");
		}
		catch (Exception $e) {
			$this->add_flash_error("Could not authenticate Twitter account.");
			return $this->redirect_to(ADMIN_ROOT_URL . "show_settings");
		}
	}

	public function update_notes() {
		$this->user->upcoming_notes = $this->params["upcoming_notes"];
		$this->user->private_notes = $this->params["private_notes"];
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
