<div class="wrap">
  <h2>Safety in Numbers Settings</h2>
  <form method="post" action="options.php">
    <?php settings_fields('canvas'); ?>
    <?php do_settings_sections('canvas'); ?>
    <?php submit_button(); ?>
  </form>
</div>