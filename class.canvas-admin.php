<?php

require_once(CANVAS_PLUGIN_DIR . 'class.canvas-admin-config.php');
require_once(CANVAS_PLUGIN_DIR . 'class.canvas-admin-moderate.php');

class Canvas_Admin {
  private static $inited = false;

  public static function init() {
    if (self::$inited) {
      return;
    }

    Canvas_Admin_Config::init();
    Canvas_Admin_Moderate::init();
  }
}
