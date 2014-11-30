<?php

require_once(CANVAS_PLUGIN_DIR . 'class.canvas-admin-config.php');

class Canvas_Admin {
  private static $inited = false;

  public static function init() {
    if (self::$inited) {
      return;
    }

    add_action('admin_init', array('Canvas_Admin', 'action_admin_init'));

    Canvas_Admin_Config::init();
  }

  public static function action_admin_init() {
    register_setting('canvas', 'moderation_quality_strictness');
    register_setting('canvas', 'moderation_flagging_strictness');
    register_setting('canvas', 'network_url');
    register_setting('canvas', 'network_sitetoken');
  }
}
