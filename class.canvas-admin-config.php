<?php

class Canvas_Admin_Config {
  private static $inited = false;

  public static function init() {
    if (self::$inited) {
      return;
    }

    add_action('admin_init', array('Canvas_Admin_Config', 'action_admin_init'));
    add_action('admin_menu', array('Canvas_Admin_Config', 'action_admin_menu'));
  }

  public static function action_admin_init() {
    add_settings_section(
      'canvas_network',
      'Network',
      array('Canvas_Admin_Config', 'section_network_callback'),
      'canvas'
    );

    add_settings_field(
      'canvas_field_network_url', 
      'Network URL',
      array('Canvas_Admin_Config', 'field_network_url_callback'),
      'canvas',
      'canvas_network'
    );

    add_settings_field(
      'canvas_field_network_sitetoken', 
      'Network Site Token',
      array('Canvas_Admin_Config', 'field_network_sitetoken_callback'),
      'canvas',
      'canvas_network'
    );
  }

  public static function action_admin_menu() {
    add_options_page(
      'Canvas Settings',
      'Canvas Settings',
      'manage_options',
      'options_page_slug',
      array('Canvas_Admin_Config', 'render')
    );
  }

  public static function section_network_callback() {}

  public static function field_network_url_callback() {
    $setting = esc_attr(get_option('network_url'));
    echo "<input type='text' name='network_url' value='$setting' />";
  }

  public static function field_network_sitetoken_callback() {
    $setting = esc_attr(get_option('network_sitetoken'));
    echo "<input type='text' name='network_sitetoken' value='$setting' />";
  }

  public static function render() {
    include(CANVAS_PLUGIN_DIR . 'templates/admin-config.php');
  }
}