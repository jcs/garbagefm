<?php
/*
	site-wide settings, loaded after framework

	should do per-environment setup like logging, tweaking php settings, etc.
*/

define("ADMIN_ROOT_DOMAIN", "garbage.fm");
define("ADMIN_ROOT_PATH", "/adm1n/");
define("ADMIN_ROOT_URL", "https://" . ADMIN_ROOT_DOMAIN . ADMIN_ROOT_PATH);

define("TWITTER_CONSUMER_KEY", $_ENV["TWITTER_CONSUMER_KEY"]);
define("TWITTER_CONSUMER_SECRET", $_ENV["TWITTER_CONSUMER_SECRET"]);

/* session settings, change according to your application requirements */
session_name("_garbagefm_session");
session_set_cookie_params($lifetime = (60 * 60 * 24 * 7), "/",
	ADMIN_ROOT_DOMAIN, true, true);

/* activate encrypted cookie storage; requires the mcrypt php extension */
HalfMoon\Config::set_session_store(
	"encrypted_cookie",

	/* you must define a random encryption key here of 32 characters.
	 * "openssl rand 16 -hex" will generate one for you. */
	array("encryption_key" => $_ENV["COOKIE_ENCRYPTION_KEY"]);
);

/* a timezone is required for DateTime functions */
date_default_timezone_set("US/Central");

/* environment-specific settings */
if (HALFMOON_ENV == "development") {
	/* be open and verbose during development */

	/* show errors in the browser */
	ini_set("display_errors", 1);

	/* log all activerecord queries and values */
	HalfMoon\Config::set_activerecord_log_level("full");

	/* log all halfmoon activity */
	HalfMoon\Config::set_log_level("full");
}

elseif (HALFMOON_ENV == "production") {
	/* be quiet in production */

	/* don't display actual php error messages to the user, just generic error
	 * pages (see skel/500.html) */
	ini_set("display_errors", 0);

	/* do not log any activerecord queries */
	HalfMoon\Config::set_activerecord_log_level("none");

	/* only log halfmoon processing times with urls */
	HalfMoon\Config::set_log_level("full");

	/* perform file caching for controllers that request it, and store files in
	 * this directory (must be writable by web server user running halfmoon */
	HalfMoon\Config::set_cache_store_path(HALFMOON_ROOT . "/public/cache");

	/* uncomment to send emails of error backtraces and debugging info */
	HalfMoon\Config::set_exception_notification_recipient(
		$_ENV["EXCEPTION_NOTIFICATION_RECIPIENT"]);
	HalfMoon\Config::set_exception_notification_subject("[garbagefm]");
}

require_once("lib/parsedown/Parsedown.php");
require_once("lib/PasswordHash.php");

require_once("lib/hash_equals/lib/hash_equals.php");
require_once("lib/base32/src/Base32.php");
require_once("lib/otphp/lib/Factory.php");
require_once("lib/otphp/lib/OTPInterface.php");
require_once("lib/otphp/lib/OTP.php");
require_once("lib/otphp/lib/TOTPInterface.php");
require_once("lib/otphp/lib/TOTP.php");

?>
