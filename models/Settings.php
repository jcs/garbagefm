<?php

class Settings extends ActiveRecord\Model {
	static $attr_accessible = array(
		"name", "hosts", "url", "description", "keywords", "contact_email",
		"logo_url", "itunes_url", "twitter_username",
	);

	static public function fetch() {
		return Settings::find("first");
	}
}

?>
