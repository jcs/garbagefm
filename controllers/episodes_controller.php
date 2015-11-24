<?php

class EpisodesController extends ApplicationController {
	static $caches_page = array("home", "index", "show", "rss");

	public function home() {
		$this->find_episodes(6);
		$this->render(array("action" => "index"));
	}

	public function index() {
		$this->find_episodes();
		$this->page_title = "Episodes";
	}

	public function rss() {
		$this->find_episodes();

		header("Content-type: application/rss+xml; charset=UTF-8");
		$this->render(array("action" => "rss"), array("layout" => false));
	}

	public function show() {
		$this->episode = Episode::find_by_episode($this->params["id"]);
		if (!$this->episode)
			throw new \ActiveRecord\RecordNotFound("can't find episode "
				. $this->params["id"]);

		$this->page_title = $this->episode->episode . ": "
			. $this->episode->title;
	}

	protected function find_episodes($limit = 0) {
		$this->episodes = Episode::find("all",
			array("conditions" => "is_pending = 0",
			"order" => "episode DESC", "limit" => $limit));
	}
}

?>
