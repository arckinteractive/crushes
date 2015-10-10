<?php

namespace MFP\Crushes;

/**
 * Remove a user from the crush list
 */

$crushing_on = get_entity(get_input('crushing_on_guid'));
$crusher = elgg_get_logged_in_user_entity();

if ($crusher == $crushing_on) {
	register_error(elgg_echo('crushes:remove:cannot_remove_crush'));
	forward(REFERRER);
}

if (!$crushing_on instanceof \ElggUser) {
	register_error(elgg_echo('crushes:remove:cannot_remove_crush'));
	forward(REFERRER);
}

if (remove_entity_relationship($crusher->getGUID(), 'crushing_on', $crushing_on->getGUID())) {
	system_message(elgg_echo('crushes:remove:removed_crush'));
} else {
	register_error(elgg_echo('crushes:remove:cannot_remove_crush'));
}

forward(REFERRER);