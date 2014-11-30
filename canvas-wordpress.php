<?php
/**
 * Plugin Name: canvas-wordpress
 * Plugin URI: https://github.com/hellojwilde/canvas-wordpress
 * Description: Fight harassment in comments.
 * Author: Team "Also Awesome"
 * Author URI: https://github.com/hellojwilde/canvas-wordpress
 * Version: 0.1.0
 * License: MIT
 */

if (!function_exists('plugin_dir_path')) {
  echo 'Can\'t call this plugin directly.';
  exit;
}

define('CANVAS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CANVAS_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once(CANVAS_PLUGIN_DIR . 'class.canvas.php');
add_action('init', array('Canvas', 'init'));

if (is_admin()) {
  require_once(CANVAS_PLUGIN_DIR . 'class.canvas-admin.php');
  add_action('init', array('Canvas_Admin', 'init'));
}
