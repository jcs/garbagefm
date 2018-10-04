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
		"custom_author",
	);

	static $after_destroy = array("delete_mp3_file");

	static $after_save = array("possibly_update_chapters");

	public $needs_chapter_updating;

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

	public function get_secure_mp3_url() {
		return Settings::fetch()->secure_url . $this->file_path;
	}

	public function get_mp3_path() {
		return HALFMOON_ROOT . "/public/" . $this->file_path;
	}

	/* publicly viewable page, not mp3 */
	public function get_absolute_url() {
		return Settings::fetch()->url . "episodes/" . $this->episode;
	}

	public function get_guid() {
		/* return http for legacy reasons */
		if ($this->is_pending)
			return preg_replace("/https:/", "http:", $this->absolute_url) .
				"?" . $this->updated_at->getTimestamp();
		else
			return preg_replace("/https:/", "http:", $this->absolute_url);
	}

	public function get_artwork_url() {
		if ($this->custom_artwork_url)
			return $this->custom_artwork_url;
		else
			return Settings::fetch()->logo_url;
	}

	public function get_is_comment() {
		return !!(preg_match("/\/* .* *\//", $this->title));
	}

	public function get_full_title() {
		$t = "";

		if (!$this->is_comment)
			$t .= "garbage[" . $this->episode . "]: ";

		$t .= $this->title;

		if ($this->is_pending)
			$t .= " [PENDING]";

		return $t;
	}

	public function get_secure_artwork_url() {
		if ($this->custom_artwork_url)
			return preg_replace("/^http:/i", "https:",
				$this->custom_artwork_url);
		else
			return Settings::fetch()->secure_logo_url;
	}

	public function possibly_update_chapters() {
		if ($this->needs_chapter_updating && file_exists($this->mp3_path) &&
		count($this->chapters)) {
			$args = "/garbage.fm/bin/mp3chap " .
				escapeshellarg($this->mp3_path);

			foreach ($this->chapters as $time => $content) {
				$secs = 0;

				if (preg_match("/^(\d+):(\d+):(\d+)$/", $time, $m))
					$secs = intval($m[3]) + (intval($m[2]) * 60) +
						(intval($m[1]) * 60 * 60);
				elseif (preg_match("/^(\d+):(\d+)$/", $time, $m))
					$secs = intval($m[2]) + (intval($m[1]) * 60);
				else
					$secs = intval($time);

				$args .= " " . $secs . " " . escapeshellarg($content);
			}

			error_log("Running mp3chap: " . $args);
			system($args);
		} else {
			$this->needs_chapter_updating = false;
		}
	}

	public function set_notes($notes) {
		$old_notes = $this->notes;
		$this->assign_attribute("notes", $notes);

		$parsedown = new Parsedown();
		$this->notes_html = $parsedown->text($notes);

		if ($old_notes != $notes)
			$this->needs_chapter_updating = true;
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
				$chapters[$m[3]] = html_entity_decode(strip_tags($m[2]));
		}

		return $chapters;
	}

	public function take_new_mp3($file) {
		move_uploaded_file($file, $this->mp3_path);
		$this->filesize = filesize($this->mp3_path);
		$this->needs_chapter_updating = true;
	}

	public function delete_mp3_file() {
		unlink($this->mp3_path);
	}
}

?>
