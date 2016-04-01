<?php

class Episode extends ActiveRecord\Model {
	static $validates_presence_of = array(
		array("episode"),
		array("title"),
		array("notes"),
		array("duration"),
		array("air_date"),
	);
	static $validates_uniqueness_of = array(
		array("episode"),
	);

	static $attr_accessible = array(
		"episode", "is_pending", "air_date", "title", "is_explicit",
		"duration", "notes", "summary", "custom_artwork_url",
	);

	static $after_destroy = array("delete_mp3_file");

	static public function next_episode() {
		$sth = Episode::query("SELECT MAX(episode) FROM episodes");
		$row = $sth->fetch(PDO::FETCH_NUM);
		$max = intval($row[0]);
		return $max + 1;
	}

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
		if ($this->custom_artwork_url)
			return $this->custom_artwork_url;
		else
			return Settings::fetch()->logo_url;
	}

	public function set_notes($notes) {
		$this->assign_attribute("notes", $notes);

		$parsedown = new Parsedown();
		$this->notes_html = $parsedown->text($notes);
	}

	public function set_duration($duration) {
		$secs = 0;

		if (preg_match("/^(\d+):(\d+):(\d+)$/", $duration, $m))
			$secs = intval($m[3]) + (intval($m[2]) * 60) +
				(intval($m[1]) * 60 * 60);
		elseif (preg_match("/^(\d+):(\d+)$/", $duration, $m))
			$secs = intval($m[2]) + (intval($m[1]) * 60);
		else
			$secs = intval($duration);

		$this->assign_attribute("duration_secs", $secs);

		$hours = floor($secs / 3600);
		$mins = floor(($secs - ($hours * 3600)) / 60);
		$secs = floor($secs % 60);

		$tms = "";
		if ($hours > 0)
			$tms = sprintf("%d:%02d:%02d", $hours, $mins, $secs);
		else
			$tms = sprintf("%d:%02d", $mins, $secs);

		$this->assign_attribute("duration", $tms);
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
