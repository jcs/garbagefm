<?php

class AdminEpisodesController extends ApplicationController {
	static $session = "on";
	static $before_filter = array(
		"authenticate_user",
		"require_logged_in_user",
	);
	static $layout = "admin";

	static $filter_parameter_logging = array("preview_text");

	public function index() {
		$this->episodes = Episode::find("all",
			array("order" => "episode DESC"));
	}

	public function build() {
		$this->episode = new Episode;

		$sth = Episode::query("SELECT MAX(episode) FROM episodes");
		$row = $sth->fetch(PDO::FETCH_NUM);
		$max = intval($row[0]);
		$this->episode->episode = $max + 1;

		$this->episode->is_pending = true;

		$this->episode->air_date = new DateTime(date("Y-m-d 20:00:00"));
	}

	public function create() {
		$this->episode = new Episode($this->params["episode"]);
		if ($this->episode->save()) {
			$this->add_flash_success("Successfully created episode " .
				$this->episode->episode);
			return $this->redirect_to(ADMIN_ROOT);
		}
		else {
			return $this->render(array("action" => "build"));
		}
	}

	public function edit() {
		$this->episode = Episode::find_by_episode($this->params["id"]);
	}

	public function update() {
		$this->episode = Episode::find_by_episode($this->params["id"]);
		if ($this->episode->update_attributes($this->params["episode"])) {
			$this->add_flash_success("Successfully updated episode " .
				h($this->episode->episode));
			return $this->redirect_to(ADMIN_ROOT);
		}
		else {
			return $this->render(array("action" => "edit"));
		}
	}

	public function md_preview() {
		$parsedown = new Parsedown();
		$this->render(array("html" =>
			$parsedown->text($this->params["preview_text"]),
			"layout" => false));
	}
}

?>
