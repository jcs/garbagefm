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
		"duration_secs", "notes", "summary",
	);

	static $after_destroy = array("delete_mp3_file");

	public function get_file_path() {
		return "episodes/garbage" . intval($this->episode) . ".mp3";
	}

	public function get_mp3_url() {
		return Settings::fetch()->url . $this->file_path;
	}

	public function get_mp3_path() {
		return HALFMOON_ROOT . "/public/" . $this->file_path;
	}

	/* publicly viewable page, not mp3 */
	public function get_absolute_url() {
		return Settings::fetch()->url . "episodes/" . $this->episode;
	}

	public function get_artwork_url() {
		return Settings::fetch()->logo_url;
	}

	public function get_notes_html() {
		$parsedown = new Parsedown();
		return $parsedown->text($this->notes);
	}

	public function get_chapters() {
		$chapters = array();

		$lines = explode("\n", $this->notes_html);
		foreach ($lines as $line) {
			if (preg_match("/^(<p>)?(.+?) <!-- ([0-9:]+) -->/", $line, $m))
				$chapters[$m[3]] = strip_tags($m[2]);
		}

		return $chapters;
	}

	public function take_new_mp3($file) {
		move_uploaded_file($file, $this->mp3_path);
		$this->filesize = filesize($this->mp3_path);
	}

	public function delete_mp3_file() {
		unlink($this->mp3_path);
	}
}

?>
