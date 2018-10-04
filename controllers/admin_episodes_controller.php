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
			"redirect_to" => ADMIN_ROOT_URL,
		),
	);

	static $filter_parameter_logging = array("preview_text");

	public function destroy() {
		$this->episode = Episode::find_by_episode($this->params["id"]);
		$this->episode->delete();

		$this->add_flash_success("Successfully deleted episode "
			. h($this->episode->episode));
		return $this->redirect_to(ADMIN_ROOT_URL);
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
			return $this->redirect_to(ADMIN_ROOT_URL);
		}
		else {
			return $this->render(array("action" => "build"));
		}
	}

	public function edit() {
		$this->episode = Episode::find_by_episode($this->params["id"]);
	}

	public function preview() {
		$this->episode = Episode::find_by_episode($this->params["id"]);
		if (!$this->episode)
			throw new \ActiveRecord\RecordNotFound("can't find episode "
				. $this->params["id"]);

		$this->next_episode = Episode::find_by_episode_and_is_pending(
			$this->episode->episode + 1, false);
		if ($this->episode->episode > 0)
			$this->prev_episode = Episode::find_by_episode(
				$this->episode->episode - 1);

		$this->page_title = $this->episode->episode . ": "
			. $this->episode->title;

		return $this->render(array("html" =>
			"<div class=\"episodes\">"
			. $this->render_to_string(array("action" => HALFMOON_ROOT
			. "/views/episodes/_episode"), array("episode" => $this->episode))
			. "</div><hr>", "layout" => "application"));
	}

	public function update() {
		$this->episode = Episode::find_by_episode($this->params["id"]);

		if ($_FILES["episode"]["name"]["new_mp3"])
			$this->episode->take_new_mp3($_FILES["episode"]["tmp_name"]["new_mp3"]);

		if ($this->episode->update_attributes($this->params["episode"])) {
			$this->add_flash_success("Successfully updated episode " .
				h($this->episode->episode));

			if ($this->episode->needs_chapter_updating)
				$this->add_flash_success("Updated chapters in MP3 file");

			$this->flush_cache();

			return $this->redirect_to(ADMIN_ROOT_URL . "episodes/edit/"
				. $this->episode->episode);
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

	public function tweet() {
		$json = json_decode(Twitter::oauth_request("/1.1/statuses/update.json?"
			. "include_entities=1&wrap_links=true", "POST",
			array("status" => $this->params["tweet"])), true);

		if ($json["id"]) {
			$tweet = "https://twitter.com/"
				. Settings::fetch()->twitter_username . "/status/"
				. $json["id"];

			$this->add_flash_success(raw("Successfully Tweeted: <a href=\""
				. $tweet . "\" target=\"_blank\">" . $tweet . "</a>"));
		}
		else {
			\HalfMoon\Log::error_log_r($json);
			$this->add_flash_error("Could not send tweet");
		}

		return $this->redirect_to(ADMIN_ROOT_URL);
	}
}

?>
