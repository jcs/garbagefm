<?php
/*
	url-to-controller mapping file.  first-matching route wins.

	for empty urls ("/"), only root routes (added with addRootRoute) will be
	matched against.

	a specific route example to only match ids that are valid numbers:

		HalfMoon\Router::addRoute(array(
			"url" => "posts/:id",
			"controller" => "posts",
			"action" => "show",
			"conditions" => array("id" => '/^\d+$/'),
		));

	a root route to match "/":

		HalfMoon\Router::addRootRoute(array(
			"controller" => "posts"
		));

	another root route on a specific virtual host to map to a different action
	(this would have to be defined before the previous root route, since the
	previous one has no conditions and would match all root urls):

		HalfMoon\Router::addRootRoute(array(
			"controller" => "posts",
			"action" => "devindex",
			"conditions" => array("hostname" => "dev"),
		));
*/

/* admin */
HalfMoon\Router::addRoute(array(
	"url" => ADMIN_ROOT_PATH . "episodes/:action/:id",
	"controller" => "admin_episodes",
	"conditions" => array("hostname" => ADMIN_ROOT_DOMAIN),
));
HalfMoon\Router::addRoute(array(
	"url" => ADMIN_ROOT_PATH . "profile/:action",
	"controller" => "admin_profile",
	"conditions" => array("hostname" => ADMIN_ROOT_DOMAIN),
));
HalfMoon\Router::addRoute(array(
	"url" => ADMIN_ROOT_PATH . ":action/:id",
	"controller" => "admin",
	"conditions" => array("hostname" => ADMIN_ROOT_DOMAIN),
));
HalfMoon\Router::addRoute(array(
	"url" => ADMIN_ROOT_PATH,
	"controller" => "admin",
	"conditions" => array("hostname" => ADMIN_ROOT_DOMAIN),
));


HalfMoon\Router::addRoute(array(
	"url" => "episodes.rss",
	"controller" => "episodes",
	"action" => "rss",
));

HalfMoon\Router::addRoute(array(
	"url" => "episodes/:id",
	"controller" => "episodes",
	"action" => "show",
	"conditions" => array("id" => '/^\d+$/'),
));

HalfMoon\Router::addRoute(array(
	"url" => "page/:page",
	"controller" => "episodes",
	"action" => "home",
	"conditions" => array("page" => '/^\d+$/'),
));

HalfMoon\Router::addRoute(array(
	"url" => "episodes/:action/:id",
	"controller" => "episodes",
));

HalfMoon\Router::addRootRoute(array(
	"controller" => "episodes",
	"action" => "home",
));

?>
