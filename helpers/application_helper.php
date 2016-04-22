<?php

class ApplicationHelper extends HalfMoon\Helper {
	public function bytes_h($size, $precision = 1) {
		if ($size == 0)
			return "0";

		$base = log($size, 1024);
		$suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');

		return round(pow(1024, $base - floor($base)), $precision) .
			$suffixes[floor($base)];
	}

	public function time_to_secs($time) {
		$secs = 0;

		$pieces = explode(":", $time);

		$secs = floatval(array_pop($pieces));
		if (count($pieces))
			$secs += (intval(array_pop($pieces)) * 60);
		if (count($pieces))
			$secs += (intval(array_pop($pieces)) * 60 * 60);

		return $secs;
	}

	public function joined_host_names($C) {
		$hosts = array();
		foreach ($C->hosts() as $host)
			array_push($hosts, $host->full_name);

		return $this->comma_join($hosts);
	}

	public function comma_join($list) {
		$out = "";
		if (count($list) <= 1)
			$out = $list[0];
		elseif (count($list) == 2)
			$out = $list[0] . " and " . $list[1];
		else {
			for ($x = 0; $x < count($list); $x++) {
				if ($x > 0)
					$out .= ", ";

				if ($x == count($list) - 1)
					$out .= " and ";

				$out .= $list[$x];
			}
		}

		return $out;
	}

	public function settings() {
		if (!$this->_settings)
			$this->_settings = Settings::fetch();

		return $this->_settings;
	}
}

?>
