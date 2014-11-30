<?php

class Canvas_Admin_Config {
  private static $inited = false;

  public static function init() {
    if (self::$inited) {
      return;
    }

    add_action('admin_menu', array('Canvas_Admin_Config', 'hook_admin_menu'));
  }

  public static function hook_admin_menu() {
    add_options_page(
      'Canvas Settings',
      'Canvas Settings',
      'manage_options',
      'options_page_slug',
      array('Canvas_Admin_Config', 'render')
    );
  }

  public static function render() {
    echo 'Config';
  }
}