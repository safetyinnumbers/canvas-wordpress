<?php

class Canvas {
  private static $inited = false;

  public static function init() {
    if (self::$inited) {
      return;
    }

    add_action('wp_enqueue_scripts', array('Canvas', 'action_wp_enqueue_scripts'));
  }

  public static function action_wp_enqueue_scripts() {
    wp_enqueue_script(
      'canvas-script-comment', 
      CANVAS_PLUGIN_URL . 'public/js/Comment.js',
      array('jquery'),
      '0.1.0',
      true
    );
  }
}