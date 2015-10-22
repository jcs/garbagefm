<?php

class ApplicationHelper extends HalfMoon\Helper {
	public function bytes_h($size, $precision = 1) {
		$base = log($size, 1024);
		$suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');

		return round(pow(1024, $base - floor($base)), $precision) .
			$suffixes[floor($base)];
	}
}

?>
