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

	public function get_can_tweet() {
		return (defined(TWITTER_CONSUMER_KEY) && TWITTER_CONSUMER_KEY &&
			defined(TWITTER_CONSUMER_SECRET) && TWITTER_CONSUMER_SECRET &&
			$this->twitter_oauth_token != "" &&
			$this->twitter_oauth_secret != "");
	}

	public function get_secure_url() {
		return preg_replace("/^http:/i", "https:", $this->url);
	}

	public function get_secure_logo_url() {
		return preg_replace("/^http:/i", "https:", $this->logo_url);
	}
}

?>
