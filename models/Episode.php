<?php

class Episode extends ActiveRecord\Model {
	static $validates_presence_of = array(
		array("episode"),
		array("title"),
		array("notes"),
		array("duration_secs"),
		array("air_date"),
	);
	static $validates_uniqueness_of = array(
		array("episode"),
	);

	static $attr_accessible = array(
		"episode", "is_pending", "air_date", "title", "is_explicit",
		"duration_secs", "notes", "filesize", "summary",
	);

	public function mp3_url() {
		return Settings::fetch()->url . "episodes/garbage" . $this->episode
			. ".mp3";
	}

	public function absolute_url() {
		return Settings::fetch()->url . "episodes/" . $this->episode;
	}

	public function artwork_url() {
		return Settings::fetch()->logo_url;
	}

	public function notes_html() {
		$parsedown = new Parsedown();
		return $parsedown->text($this->notes);
	}
}

?>
