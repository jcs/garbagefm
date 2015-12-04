<?php

class EpisodesController extends ApplicationController {
	static $caches_page = array("home", "index", "show", "rss");

	static $episodes_per_page = 5;

	public function home() {
		$page = 0;
		if (array_key_exists("page", $this->params))
			$page = intval($this->params["page"]);

		$this->find_episodes($page);
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

	protected function find_episodes($page = -1) {
		$count = Episode::count_by_is_pending(false);
		$this->pages = ceil($count / static::$episodes_per_page) - 1;

		$conds = array("conditions" => "is_pending = 0",
			"order" => "episode DESC");

		if ($page == -1) {
			/* return all episodes */
			/* TODO: should we still limit it to something? */
		} else {
			$this->page = intval($page);

			if ($this->page < 0 || $this->page > $this->pages)
				throw new \ActiveRecord\RecordNotFound("invalid page "
					. $this->page);

			$conds["limit"] = static::$episodes_per_page;
			$conds["offset"] = (static::$episodes_per_page * $this->page);
		}

		$this->episodes = Episode::find("all", $conds);
	}
}

?>
