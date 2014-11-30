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
    add_action('wp_insert_comment', array('Canvas', 'action_wp_insert_comment'), 99, 2);
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
      array(
        'ajaxURL' => admin_url('admin-ajax.php'),
        'tokenURL' => get_option('network_url') . 'user/token'
      )
    );
  }

  private static function update_comment_meta($id, $cred) {
    // TODO: based on the cred object that comes back enqueue it.
    // TODO: based on a threshold list it as positive or negative.
    
    update_comment_meta($id, 'cred', serialize($cred));
  }

  public static function action_wp_ajax_vote() {
    $id = $_POST['id'];
    $user_token = $_POST['userToken'];
    $type = $_POST['type'];

    $comment_guid = get_comment_meta($id, 'guid', true);
    if (is_array($comment_guid)) return;

    $network =  get_option('network_url');
    $network .= ($type == 'up') ? "comment/$comment_guid/upvote"
                                : "comment/$comment_guid/downvote";

    $response = wp_remote_post(
      $network,
      array(
        'body' => json_encode(array(
          'user_token' => get_comment_meta($id, 'user_token', true),
          'site_token' => get_option('network_sitetoken')
        )),
        'headers' => array('Content-Type' => 'application/json')
      )
    );

    if (!is_wp_error($response)) {
      $response_details = json_decode($response['body']);
      self::update_comment_meta($id, $response_details);
    }
  }

  public static function action_wp_ajax_flag() {
    $id = $_POST['id'];
    $user_token = $_POST['userToken'];
    $type = $_POST['type'];
    $details = $_POST['details'];

    $comment_guid = get_comment_meta($id, 'guid', true);
    if (is_array($comment_guid)) return;

    $response = wp_remote_post(
      get_option('network_url') . "comment/$comment_guid/flag",
      array(
        'body' => json_encode(array(
          'user_token' => get_comment_meta($id, 'user_token', true),
          'site_token' => get_option('network_sitetoken'),
          'flag_id' => $type,
          'description' => $details
        )),
        'headers' => array('Content-Type' => 'application/json')
      )
    );

    if (!is_wp_error($response)) {
      $response_details = json_decode($response['body']);
      self::update_comment_meta($id, $response_details);
    }

  }

  public static function action_wp_insert_comment($id, $comment) {
    add_comment_meta($id, 'user_token', $_POST['userToken']);
    add_comment_meta($id, 'overridden', false);

    $response = wp_remote_post(
      get_option('network_url') . 'comment',
      array(
        'body' => json_encode(array(
          'user_token' => $_POST['userToken'],
          'site_token' => get_option('network_sitetoken'),
          'comment' => $comment->comment_content,
          'ip' => $comment->comment_author_IP
        )),
        'headers' => array('Content-Type' => 'application/json')
      )
    );

    if (!is_wp_error($response)) {
      $response_details = json_decode($response['body']);
      add_comment_meta($id, 'guid', $response_details->comment_id);
      self::update_comment_meta($id, $response_details->comment_cred);
    }
  }
}