<?php

class ApplicationHelper extends HalfMoon\Helper {
	public function bytes_h($size, $precision = 1) {
		$base = log($size, 1024);
		$suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');

		return round(pow(1024, $base - floor($base)), $precision) .
			$suffixes[floor($base)];
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
}

?>
