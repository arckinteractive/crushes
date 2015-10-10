<?php

namespace MFP\Crushes;

/**
 * Shows users you've marked as crushes and they've marked you.
 */

// default setting
if (!isset($vars['entity']->max_display)) {
	$vars['entity']->max_display = 4;
}

$options = array(
	'type' => 'user',
	'relationship' => 'crushing_on',
	'relationship_guid' => elgg_get_page_owner_guid(),
	'full_view' => false,
	'wheres' => array(get_mutual_crush_where_clause()),
	'list_type' => 'gallery',
	'item_class' => 'pas',
	'pagination' => false
);

elgg_set_context($context);
$content = elgg_list_entities_from_relationship($options);

if ($content) {
	echo $content;

	$url = "crushes/owner/" . elgg_get_page_owner_entity()->username . '?section=mutual';
	$more_link = elgg_view('output/url', array(
		'href' => $url,
		'text' => elgg_echo('crushes:mutual_crushes'),
		'is_trusted' => true,
	));
	echo "<span class=\"elgg-widget-more\">$more_link</span>";

} else {
	echo elgg_echo('crushes:no_mutual_crushes');
}