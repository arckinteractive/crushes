<?php

namespace MFP\Crushes;

/**
 * Add a user to the crush list
 */

$crushing_on = get_entity(get_input('crushing_on_guid'));
$crusher = elgg_get_logged_in_user_entity();

if ($crusher == $crushing_on) {
	register_error(elgg_echo('crushes:add:cannot_add_crush'));
	forward(REFERRER);
}

if (!$crushing_on instanceof \ElggUser) {
	register_error(elgg_echo('crushes:add:cannot_add_crush'));
	forward(REFERRER);
}

if (add_entity_relationship($crusher->getGUID(), 'crushing_on', $crushing_on->getGUID())) {
	if (check_entity_relationship($crushing_on->getGUID(), 'crushing_on', $crusher->getGUID())) {
		system_message(elgg_echo('crushes:add:mutual_crush'));
	} else {
		system_message(elgg_echo('crushes:add:added_crush'));
	}
} else {
	register_error(elgg_echo('crushes:add:cannot_add_crush'));
}

forward(REFERRER);