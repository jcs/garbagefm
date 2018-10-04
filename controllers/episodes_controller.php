<?php

class EpisodesController extends ApplicationController {
	static $caches_page = array("home", "index", "show", "rss");

	static $episodes_per_page = 10;

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

	public function rss_with_pending() {
		$this->find_episodes(-1, true);

		header("Content-type: application/rss+xml; charset=UTF-8");
		$this->render(array("action" => "rss"), array("layout" => false,
			"with_pending" => true));
	}

	public function show() {
		$this->episode = Episode::find_by_episode_and_is_pending(
			$this->params["id"], false);
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

		$this->meta_headers = array(
			"og:title" => strtolower(Settings::fetch()->name) . "["
				. $this->episode->episode . "]: " . $this->episode->title,
			"og:image" => $this->episode->secure_artwork_url,
			"twitter:card" => "player",
			"twitter:site" => "@" . Settings::fetch()->twitter_username,
			"twitter:player" => Settings::fetch()->secure_url
				. "episodes/twitter_card/" . $this->episode->episode,
			"twitter:player:width" => "290",
			"twitter:player:height" => "58",
			"twitter:player:stream" => $this->episode->secure_mp3_url,
			"twitter:player:stream:content_type" => "audio/mpeg",
		);
	}

	public function twitter_card() {
		$this->show();

		$this->render(array("action" => "twitter_card", "layout" => false));
	}

	protected function find_episodes($page = -1, $pending = false) {
		$count = 0;
		if ($pending)
			$count = Episode::count();
		else
			$count = Episode::count_by_is_pending(false);

		$this->pages = ceil($count / static::$episodes_per_page) - 1;

		$conds = array("order" => "episode DESC");
		if (!$pending)
			$conds["conditions"] = "is_pending = 0";

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
