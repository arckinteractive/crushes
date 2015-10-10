<?php

namespace MFP\Crushes;

/**
 * Crushes
 *
 * Users maintain a private list of other users they have crushes on. If two users are mutual crushes,
 * a notification is sent to both revealing the name of the crush.
 *
 * Crushes are stored as the relationships: User A "crushing_on" User B.
 */

const PLUGIN_ID = 'crushes';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 * Init
 */
function init() {
	elgg_register_page_handler('crushes', __NAMESPACE__ . '\\page_handler');

	elgg_extend_view('css', 'crushes/css');

	// menus
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', __NAMESPACE__ . '\\setup_hover_menu');

	if (elgg_is_logged_in()) {
		$item = new \ElggMenuItem('crushes', elgg_echo('crushes'), '/crushes/owner/' . elgg_get_logged_in_user_entity()->username);
		elgg_register_menu_item('site', $item);
	}

	// actions
	elgg_register_action('crushes/add', __DIR__ . "/actions/crushes/add.php");
	elgg_register_action('crushes/remove', __DIR__ . "/actions/crushes/remove.php");

	// check relationship to see if we have mutual crushes
	elgg_register_event_handler('create', 'crushing_on', __NAMESPACE__ . '\\check_mutual_crushes');

	// widget for crushes.
	elgg_register_widget_type('mutual_crushes', elgg_echo("crushes:mutual_crushes"), elgg_echo("crushes:widget:mutual:description"));
	elgg_register_widget_type('crushes', elgg_echo("crushes"), elgg_echo("crushes:widget:description"));
}

/**
 * Serve pages. URLs in the form:
 *
 * crushes/owner/<username> - Crushes landing page for <username>. If ?section=mutual shows only mutual
 *
 * @param array $page
 * @return bool Depending on success
 */
function page_handler($page) {
	gatekeeper();

	if (!isset($page[0])) {
		$page[0] = 'owner';
	}

	if (!isset($page[1])) {
		$user = elgg_get_logged_in_user_entity();
		$page[1] = $user->username;
	}

	$username = elgg_extract(1, $page);
	$user = get_user_by_username($username);
	elgg_set_page_owner_guid($user->getGUID());
	
	if (!$user) {
		forward(REFERER, 404);
	}

	include dirname(__FILE__) . '/pages/crushes/owner.php';

	return true;
}

/**
 * Send notifications on adding a mutual crushes
 *
 * @param type $event
 * @param type $type
 * @param type $return
 * @return type
 */
function check_mutual_crushes($event, $type, $object) {
	$mutual_crusher = get_entity($object->guid_one);
	$original_crusher = get_entity($object->guid_two);

	// mutuals!
	if (check_entity_relationship($original_crusher->getGUID(), 'crushing_on', $mutual_crusher->getGUID())) {
		$msg_url = elgg_normalize_url('messages/compose/?send_to=');
		// original
		$subject = elgg_echo('crushes:notifications:mutual_match_subject');
		$body = elgg_echo('crushes:notifications:mutual_match_body', array(
			$original_crusher->name,
			$mutual_crusher->name,
			$mutual_crusher->getURL(),
			$msg_url . $mutual_crusher->guid
			
		));

		notify_user($original_crusher->getGUID(), $mutual_crusher->getGUID(), $subject, $body);

		// late comer
		$subject = elgg_echo('crushes:notifications:mutual_match_subject');
		
		$body = elgg_echo('crushes:notifications:mutual_match_body', array(
			$mutual_crusher->name,
			$original_crusher->name,
			$original_crusher->getURL(),
			$msg_url . $original_crusher->guid
		));

		notify_user($mutual_crusher->getGUID(), $original_crusher->getGUID(), $subject, $body);
	}
}

/**
 * Returns a where clause to find mutual relationships
 *
 * @return str
 */
function get_mutual_crush_where_clause() {
	$db_prefix = get_config('dbprefix');
	return "EXISTS (
		SELECT 1 FROM {$db_prefix}entity_relationships r2
			WHERE r2.guid_one = r.guid_two
			AND r2.relationship = 'crushing_on'
			AND r2.guid_two = r.guid_one)
	";
}

/**
 * Add a menu item to for crushes
 *
 * @param type $hook
 * @param type $type
 * @param type $return
 * @param type $params
 * @return \ElggMenuItem
 */
function setup_hover_menu($hook, $type, $return, $params) {
	$user = $params['entity'];

	if (!elgg_is_logged_in() || elgg_get_logged_in_user_guid() == $user->guid) {
		return $return;
	}

	$logged_in_user = elgg_get_logged_in_user_entity();

	if (elgg_in_context('profile') && !elgg_in_context('widgets')) {
		$class = 'elgg-button elgg-button-action';
	}

	if (!check_entity_relationship($logged_in_user->getGUID(), 'crushing_on', $user->getGUID())) {
		$link = elgg_view('output/url', array(
			'href' => 'action/crushes/add?crushing_on_guid=' . $user->getGUID(),
			'text' => elgg_echo('crushes:add_crush'),
			'confirm' => true,
			'class' => $class
		));
	} else {
		$link = elgg_view('output/url', array(
			'href' => 'action/crushes/remove?crushing_on_guid=' . $user->getGUID(),
			'text' => elgg_echo('crushes:remove_crush'),
			'class' => $class,
			'confirm' => true
		));
	}

	$item = new \ElggMenuItem('crushes', $link, false);
	$item->setSection('action');
	$return[] = $item;

	return $return;
}
