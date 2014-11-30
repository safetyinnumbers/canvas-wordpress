<?php

class Canvas_Admin_Moderate {
  private static $inited = false;

  public static function init() {
    if (self::$inited) {
      return;
    }

    add_action('admin_menu', array('Canvas_Admin_Moderate', 'hook_admin_menu'));
  }

  public static function hook_admin_menu() {
    add_comments_page(
      'Canvas Flagged',
      'Canvas Flagged',
      'manage_options',
      'options_page_slug',
      array('Canvas_Admin_Moderate', 'render')
    );
  }

  public static function render() {
    echo 'Moderate';
  }
}