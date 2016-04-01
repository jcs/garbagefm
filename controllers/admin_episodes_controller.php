<?php

class AdminEpisodesController extends ApplicationController {
	static $session = "on";
	static $before_filter = array(
		"authenticate_user",
		"require_logged_in_user",
	);
	static $layout = "admin";

	static $verify = array(
		array("method" => "post",
			"only" => array("create", "update", "destroy"),
			"redirect_to" => ADMIN_ROOT,
		),
	);

	static $filter_parameter_logging = array("preview_text");

	public function destroy() {
		$this->episode = Episode::find_by_episode($this->params["id"]);
		$this->episode->delete();

		$this->add_flash_success("Successfully deleted episode "
			. h($this->episode->episode));
		return $this->redirect_to(ADMIN_ROOT);
	}

	public function index() {
		$this->episodes = Episode::find("all",
			array("order" => "episode DESC"));
	}

	public function build() {
		$this->episode = new Episode;

		$this->episode->episode = Episode::next_episode();

		$this->episode->is_pending = true;
		$this->episode->air_date = new DateTime(date("Y-m-d 20:00:00"));

		$this->page_title = "Create New Episode";
	}

	public function create() {
		$this->episode = new Episode($this->params["episode"]);

		if ($_FILES["episode"]["name"]["new_mp3"])
			$this->episode->take_new_mp3($_FILES["episode"]["tmp_name"]["new_mp3"]);

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

		if ($_FILES["episode"]["name"]["new_mp3"])
			$this->episode->take_new_mp3($_FILES["episode"]["tmp_name"]["new_mp3"]);

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
		$this->episode = new Episode();
		$this->episode->notes = $this->params["preview_text"];

		return $this->render(array("partial" => "preview", "layout" => false));
	}
}

?>
