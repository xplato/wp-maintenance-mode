<?php
/**
 * Bare maintenance page template.
 *
 * Renders only the page content — no theme headers, footers,
 * sidebars, or global sections.
 */

if (!defined("ABSPATH")) {
  exit();
} ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo("charset"); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class("maintenance-mode-page"); ?>>
	<?php wp_body_open(); ?>

	<?php while (have_posts()):
   the_post(); ?>
		<div class="maintenance-mode-content">
			<?php the_content(); ?>
		</div>
	<?php
 endwhile; ?>

	<?php wp_footer(); ?>
</body>
</html>
