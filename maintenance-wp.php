<?php
/**
 * Plugin Name: Maintenance Mode
 * Description: Redirect unauthenticated users to a chosen maintenance page.
 * Version: 1.0.0
 * Author: Tristan Brewster
 * License: MIT
 */

if (!defined("ABSPATH")) {
  exit();
}

/**
 * Register the settings page under Tools > Maintenance.
 */
add_action("admin_menu", function () {
  add_management_page(
    "Maintenance Mode",
    "Maintenance",
    "manage_options",
    "maintenance-mode",
    "maintenance_mode_render_page",
  );
});

/**
 * Register plugin settings.
 */
add_action("admin_init", function () {
  register_setting("maintenance_mode_settings", "maintenance_mode_enabled", [
    "type" => "boolean",
    "sanitize_callback" => "rest_sanitize_boolean",
    "default" => false,
  ]);

  register_setting("maintenance_mode_settings", "maintenance_mode_page_id", [
    "type" => "integer",
    "sanitize_callback" => "absint",
    "default" => 0,
  ]);
});

/**
 * Render the settings page.
 */
function maintenance_mode_render_page()
{
  $enabled = get_option("maintenance_mode_enabled", false);
  $page_id = (int) get_option("maintenance_mode_page_id", 0);

  $pages = get_pages(["sort_column" => "post_title", "sort_order" => "ASC"]);
  ?>
	<div class="wrap">
		<h1>Maintenance Mode</h1>
		<form method="post" action="options.php">
			<?php settings_fields("maintenance_mode_settings"); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Enable Maintenance Mode</th>
					<td>
						<label>
							<input type="checkbox" name="maintenance_mode_enabled" value="1" <?php checked(
         $enabled,
       ); ?> />
							Redirect unauthenticated visitors to the maintenance page
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="maintenance_mode_page_id">Maintenance Page</label></th>
					<td>
						<select name="maintenance_mode_page_id" id="maintenance_mode_page_id">
							<option value="0">— Select a page —</option>
							<?php foreach ($pages as $page): ?>
								<option value="<?php echo esc_attr($page->ID); ?>" <?php selected(
  $page_id,
  $page->ID,
); ?>>
									<?php echo esc_html($page->post_title); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">Choose the page visitors will see while maintenance mode is active.</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Show a warning indicator in the admin bar when maintenance mode is active.
 */
add_action(
  "admin_bar_menu",
  function ($wp_admin_bar) {
    if (!get_option("maintenance_mode_enabled", false)) {
      return;
    }

    $wp_admin_bar->add_node([
      "id" => "maintenance-mode-notice",
      "title" => "&#9888; Maintenance Mode",
      "href" => admin_url("tools.php?page=maintenance-mode"),
      "meta" => [
        "title" => "Maintenance mode is active — click to manage",
      ],
    ]);
  },
  100,
);

/**
 * Style the admin bar indicator.
 */
add_action("wp_head", "maintenance_mode_admin_bar_css");
add_action("admin_head", "maintenance_mode_admin_bar_css");
function maintenance_mode_admin_bar_css()
{
  if (
    !get_option("maintenance_mode_enabled", false) ||
    !is_admin_bar_showing()
  ) {
    return;
  } ?>
	<style>
		#wpadminbar #wp-admin-bar-maintenance-mode-notice > .ab-item {
			background: #d63638 !important;
			color: #fff !important;
		}
		#wpadminbar #wp-admin-bar-maintenance-mode-notice:hover > .ab-item {
			background: #b32d2e !important;
		}
	</style>
	<?php
}

/**
 * Force a bare template on the maintenance page to bypass theme layouts.
 */
add_filter("template_include", function ($template) {
  if (!get_option("maintenance_mode_enabled", false)) {
    return $template;
  }

  $page_id = (int) get_option("maintenance_mode_page_id", 0);

  if ($page_id && is_page($page_id)) {
    return __DIR__ . "/template-maintenance.php";
  }

  return $template;
});

/**
 * Redirect unauthenticated users to the maintenance page.
 */
add_action("template_redirect", function () {
  if (!get_option("maintenance_mode_enabled", false)) {
    return;
  }

  if (is_user_logged_in()) {
    return;
  }

  $page_id = (int) get_option("maintenance_mode_page_id", 0);

  if (!$page_id) {
    return;
  }

  // Don't redirect if already on the maintenance page.
  if (is_page($page_id)) {
    return;
  }

  wp_safe_redirect(get_permalink($page_id), 302);
  exit();
});
