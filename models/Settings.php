<?php

class Settings extends ActiveRecord\Model {
	static $attr_accessible = array(
		"name", "hosts", "url", "description", "keywords", "contact_email",
		"logo_url", "itunes_url", "twitter_username", "prompts",
		"pocketcasts_url", "overcast_url",
	);

	static public function fetch() {
		return Settings::find("first");
	}

	public function get_replaced_prompts() {
		$prompts = $this->prompts;
		$prompts = preg_replace("/##DATE##/", date("l, F jS, Y"), $prompts);
		$prompts = preg_replace("/##NEXTEPISODE##/", Episode::next_episode(),
			$prompts);

		return $prompts;
	}
}

?>
