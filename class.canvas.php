<?php

class Canvas {
  private static $inited = false;

  public static function init() {
    if (self::$inited) {
      return;
    }

    add_action('wp_enqueue_scripts', array('Canvas', 'action_wp_enqueue_scripts'));
    add_action('wp_ajax_canvas_vote', array('Canvas', 'action_wp_ajax_vote'));
    add_action('wp_ajax_nopriv_canvas_vote', array('Canvas', 'action_wp_ajax_vote'));
    add_action('wp_ajax_canvas_flag', array('Canvas', 'action_wp_ajax_flag'));
    add_action('wp_ajax_nopriv_canvas_flag', array('Canvas', 'action_wp_ajax_flag'));
  }

  public static function action_wp_enqueue_scripts() {
    wp_enqueue_script(
      'canvas-script-comment', 
      CANVAS_PLUGIN_URL . 'public/js/Comment.js',
      array('jquery'),
      '0.1.0',
      true
    );

    wp_localize_script(
      'canvas-script-comment',
      'CanvasConstants',
      array('ajaxURL' => admin_url('admin-ajax.php'))
    );
  }

  public static function action_wp_ajax_vote() {

  }

  public static function action_wp_ajax_flag() {

  }
}