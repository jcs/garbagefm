<?php

class EpisodesController extends ApplicationController {
	public function index() {
		$this->episodes = Episode::find("all",
			array("order" => "episode DESC"));

		$this->render(array("action" => "index"));
	}

	public function rss() {
		$this->episodes = Episode::find("all",
			array("order" => "episode DESC"));

		header("Content-type: application/rss+xml; charset=UTF-8");
		$this->render(array("action" => "rss"), array("layout" => false));
	}

	public function show() {
		$this->episode = Episode::find_by_episode($this->params["id"]);
	}
}

?>
