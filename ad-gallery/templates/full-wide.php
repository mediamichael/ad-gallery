<?php
/**
 * Template Name: Full Wide
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
<?php
/*
// TODO: Full Width Wide Template
*/
get_header(); ?>
		<div id="primary" class="site-content">
			<div id="content" role="main">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content-page', get_post_format() ); ?>
				<?php endwhile; ?>
			</div>
		</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>