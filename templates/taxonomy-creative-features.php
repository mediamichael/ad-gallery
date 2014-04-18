<?php
get_header(); ?>
	<link href="<?php bloginfo('template_url');?>/css/bootstrap.css" type="text/css" rel="stylesheet"/>
	<link href="<?php bloginfo('template_url');?>/css/bootstrap-responsive.css" type="text/css" rel="stylesheet"/>
	<link href="<?php bloginfo('template_url');?>/css/ad_set.css" type="text/css" rel="stylesheet"/>
	
	<div id="content" class="full-width creative gallery-four-column">
	<?php while(have_posts()): the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="post-content">
				<?php //the_content(); ?>
				<?php //wp_link_pages(); ?>
			</div>
		</div>
	<?php endwhile; ?>
	
		<div class="row-fluid gallery-wrapper">
		
	<?php 
		while ( have_posts() ) : the_post();
	?>
		
			<div class="span3 gallery-item">
				<?php //if(has_the_title()): ?>
				<div class="creative-title">
					<a href="<?php the_permalink(); ?>">&nbsp;&nbsp;
						<?php the_title(); ?>
					</a>
				</div>
				<?php// endif; ?>
				<?php if(has_post_thumbnail()): 
					$domsxe = simplexml_load_string(get_the_post_thumbnail());
					$thumbnailsrc = $domsxe->attributes()->src;					
				?>
				<div class="image">
					<a href="<?php the_permalink(); ?>">
						<img class="attachment-post-thumbnail wp-post-image" src="<?php echo $thumbnailsrc;?>">
					</a>
				</div>
				<?php endif; ?>
			</div>
			
			<?php endwhile; ?>
		</div>
	</div>
<?php get_footer(); ?>