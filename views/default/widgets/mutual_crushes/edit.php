<?php

namespace MFP\Crushes;

/**
 * Shows users you've marked as crushes and they've marked you.
 */

// default setting
if (!isset($vars['entity']->max_display)) {
	$vars['entity']->max_display = 4;
}
?>

<p>
<?php 
	echo elgg_echo('crushes:widgets:max_display');
?>:
	<select name="params[max_display]">
<?php

for ($i=1; $i<=12; $i++) {
	$selected = '';
	if ($vars['entity']->max_display == $i) {
		$selected = "selected='selected'";
	}

	echo "<option value='{$i}' $selected >{$i}</option>\n";
}
?>
	</select>
</p>