<?php

class AdminProfileController extends ApplicationController {
	static $session = "on";
	static $before_filter = array(
		"authenticate_user",
		"require_logged_in_user",
	);

	static $verify = array(
		array("method" => "post",
			"only" => array("update"),
			"redirect_to" => ADMIN_ROOT_URL,
		),
	);

	static $filter_parameter_logging = array("password");

	static $layout = "admin";

	public function index() {
		$this->page_title = "Profile";
	}

	public function update() {
		if ($this->user->update_attributes($this->params["user"])) {
			$this->add_flash_success("Successfully updated profile.");
			return $this->redirect_to(ADMIN_ROOT_URL);
		}
		else
			$this->render(array("action" => "index"));
	}
}

?>
