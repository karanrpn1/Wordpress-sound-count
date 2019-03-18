<?php
function custom_woo_sound_menu(){
  	add_menu_page('Sound Impression', 'Sound Impression', 'manage_options', 'wordpress-sound-count', 'wordpress_sound_count_field_settings_page');
	
	add_submenu_page( 'wordpress-sound-count', 'Dashboard', 'Dashboard', 'manage_options', 'wordpress-sound-count', 'wordpress_sound_count_field_settings_page');
	
	add_submenu_page( 'wordpress-sound-count', 'Settings', 'Settings', 'manage_options', 'wordpress-sound-settings', 'wordpress_sound_settings_page');
	
		
}
add_action('admin_menu', 'custom_woo_sound_menu');
 
function wordpress_sound_count_field_settings_page() {
	ob_start();
	require CUS_WOO_SOUND_PLUGIN_DIR.'/admin/sound-impression.php'; 
	ob_end_flush();
}

function wordpress_sound_settings_page() {
	ob_start();
	require CUS_WOO_SOUND_PLUGIN_DIR.'/admin/admin_settings.php'; 
	ob_end_flush();
	
	
}