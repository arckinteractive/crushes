<?php

namespace MFP\Crushes;

/**
 * Shows all of a user's crushes
 */

gatekeeper();

$crusher = elgg_get_page_owner_entity();
$section = get_input('section', 'all');

$logged_in_user = elgg_get_logged_in_user_entity();

// only show crushes for logged in user
if (!$logged_in_user->isAdmin() && !($crusher->guid == $logged_in_user->guid)) {
	forward('', 403);
}

$title = elgg_echo('crushes:all_crushes');
$content .= elgg_view('navigation/tabs', array(
	'tabs' => array(
		array(
			'text' => elgg_echo('crushes:all_crushes'),
			'href' => '/crushes/owner/' . $crusher->username,
			'selected' => $section == 'all'
		),
		array(
			'text' => elgg_echo('crushes:mutual_crushes'),
			'href' => '/crushes/owner/' . $crusher->username . '?section=mutual',
			'selected' => $section == 'mutual'
		)
	),
));

$options = array(
	'type' => 'user',
	'relationship' => 'crushing_on',
	'relationship_guid' => $crusher->getGUID(),
	'count' => true
);

// add secondary clause for mutual relationships
if ($section == 'mutual') {
	$options['wheres'][] = get_mutual_crush_where_clause();
}

$count = elgg_get_entities_from_relationship($options);
if ($count) {
	unset ($options['count']);
	$options['full_view'] = false;

	$content .= elgg_list_entities_from_relationship($options);
} else {
	if ($section == 'mutual') {
		$content .= elgg_echo('crushes:no_mutual_crushes');
	} else {
		$content .= elgg_echo('crushes:no_crushes');
	}
}

$body = elgg_view_layout('one_sidebar', array(
	'content' => $content
));

echo elgg_view_page($title, $body);
