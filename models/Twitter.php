<?php

class Twitter {
	static $TWITTER_HOST = "https://api.twitter.com";

	static function oauth_consumer() {
		if (!defined("TWITTER_CONSUMER_KEY") || !TWITTER_CONSUMER_KEY ||
		!defined("TWITTER_CONSUMER_SECRET") || !TWITTER_CONSUMER_SECRET)
			throw new Exception("TWITTER_CONSUMER_KEY and "
				. "TWITTER_CONSUMER_SECRET must be defined");

		$oauth = new OAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
		$oauth->enableDebug();
		return $oauth;
	}

	static function new_request_token($callback = null) {
		return Twitter::oauth_consumer()->getRequestToken(
			static::$TWITTER_HOST . "/oauth/request_token", $callback);
	}

	static function new_authorize_url($token) {
		return static::$TWITTER_HOST . "/oauth/authorize?oauth_token="
			. $token;
	}

	static function oauth_request($req, $method = "GET", $post_data = array()) {
		$settings = Settings::fetch();

		$oauth_consumer = Twitter::oauth_consumer();
		$oauth_consumer->setToken($settings->twitter_oauth_token,
			$settings->twitter_oauth_secret);
		$oauth_consumer->fetch(static::$TWITTER_HOST . $req, $post_data,
			$method);

		return $oauth_consumer->getLastResponse();
	}

	static function verify_oauth_credentials($token, $secret, $in_verifier) {
		$consumer = Twitter::oauth_consumer();
		$consumer->setToken($token, $secret);

		$at = $consumer->getAccessToken(static::$TWITTER_HOST
			. "/oauth/access_token", null, $in_verifier);

		if (empty($at["screen_name"]))
			throw new Exception("no access token returned");

		$consumer->setToken($at["oauth_token"], $at["oauth_token_secret"]);
		$consumer->fetch(static::$TWITTER_HOST
			. "/1.1/account/verify_credentials.json");
		$json = json_decode($consumer->getLastResponse(), true);

		if (empty($json["screen_name"]))
			throw new Exception("no screen name in json from verification?");

		$settings = Settings::fetch();
		$settings->twitter_oauth_token = $at["oauth_token"];
		$settings->twitter_oauth_secret = $at["oauth_token_secret"];
		$settings->save();

		return true;
	}
}

?>
