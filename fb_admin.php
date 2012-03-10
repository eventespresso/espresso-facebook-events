<?php

//Facebook Event Integration Settings
function espresso_add_fb_to_admin_menu($espresso_manager) {
	add_submenu_page('events', __('Event Espresso - Facebook Settings', 'event_espresso'), __('Facebook', 'event_espresso'), 'administrator', 'espresso_facebook', 'espresso_fb_settings');
}

add_action('action_hook_espresso_add_new_submenu_to_group_settings', 'espresso_add_fb_to_admin_menu', 10);