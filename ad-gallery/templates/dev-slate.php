<?php
/**
 * Template Name: Dev Slate
 *
 *
 * @package PTE
 * @since 	1.0.1
 * @version	1.0.1
 */
?>

<?php

	$pte = Page_Template_Plugin::get_instance();
	$locale = $pte->get_locale();

?>
<div id="primary" class="site-content">
	<div id="content" role="main">
		<div class="entry-content">
			dev:
			<?php the_content(); ?>
		</div>
	</div>
</div>