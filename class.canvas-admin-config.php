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
      'canvas_moderation',
      'Moderation',
      array('Canvas_Admin_Config', 'section_moderation_callback'),
      'canvas'
    );

    add_settings_field(
      'canvas_field_moderation_quality_enqueue',
      'Quality Strictness',
      array('Canvas_Admin_Config', 'field_moderation_quality_callback'),
      'canvas',
      'canvas_moderation'
    );

    add_settings_field(
      'canvas_field_moderation_enqueue',
      'Flag Strictness',
      array('Canvas_Admin_Config', 'field_moderation_flagging_callback'),
        'canvas',
      'canvas_moderation'
    );

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

  public static function section_moderation_callback() {
    echo "<p>Move the sliders <strong>to the right</strong> to make the system more strict about marking content for review with various metrics.</p>";
  }

  public static function field_moderation_quality_callback() {
    $setting = esc_attr(get_option('moderation_quality_strictness'));
    echo "<input type='range' min='0' max='1' step='0.05' name='moderation_quality_strictness' value='$setting' />";
    echo "<p>Good quality comments have lots of upvotes and fewer downvotes.</p>";
  }

  public static function field_moderation_flagging_callback() {
    $setting = esc_attr(get_option('moderation_flagging_strictness'));
    echo "<input type='range' min='0' max='1' step='0.05' name='moderation_flagging_strictness' value='$setting' />";
    echo "<p>Highly flagged comments will have more flags from more credible people.</p>";
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