<?php get_header(); ?>
	<div id="content" class="creative-gallery full-width search">
		<div id="search">
        	<?php get_search_form(); ?>
		</div>
		<?php
		if (have_posts()) :
		while(have_posts()): the_post();				 
			if('creative' == $post->post_type){			
		?>
		<div id="post-<?php the_ID(); ?>" <?php post_class('post'); ?> style="margin-bottom:0;">			
			<!--h2 style="margin-bottom:0;"><a href="<?php bloginfo('url');  ?>/creative">Creative</a></h2-->
		</div>
		<?php 
				break;
			}	
			endwhile; ?>
		<?php endif; rewind_posts(); ?>
		
		
		<div class="row-fluid gallery-wrapper" style="margin-bottom: 15px;">
		<?php if (have_posts()) : ?>
		<?php while(have_posts()): the_post(); ?>
		<?php
				if('creative' != $post->post_type)
					continue;
				else{
					
		?>
					<div class="span3 gallery-item">
						<div class="creative-title">
							<a href="<?php the_permalink(); ?>">&nbsp;&nbsp;
								<?php the_title(); ?>
							</a>
						</div>
						<?php
							$thumbnailsrc = '';	
							if(has_post_thumbnail()){						
								$thumbnailsrc = get_the_post_thumbnail($post->ID, 'full');
							}
							else{
								$all_creative_meta = get_post_custom($post->ID);
								$gallery_thumbnail_url = $all_creative_meta["vantagelocal-creative-gallery-thumbnail-url"][0];
								$thumbnailsrc = '<img class="attachment-post-thumbnail wp-post-image" src="'.$gallery_thumbnail_url.'">' ;
							}			
						?>
						<div class="image">
							<a href="<?php the_permalink(); ?>">
								<?php echo $thumbnailsrc;?>
							</a>
						</div>
					</div>
		<?php
				}
		?>				
		<?php endwhile; ?>
		<!-- hiding other posts
		<?php endif; rewind_posts(); ?>
		</div>
		
		
		
		<?php
		if (have_posts()) :
		while(have_posts()): the_post();				 
			if('creative' == $post->post_type)
				continue;
		?>
		<div id="post-<?php the_ID(); ?>" <?php post_class('post'); ?>>			
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div class="post-content">

			<?php			
				if(($data['content_length'] == 'Excerpt') && ('creative' != $post->post_type)) {
					//the_excerpt('');
				} 
			?>
				<?php the_content(''); ?>
			</div>

		</div>
		<?php endwhile; ?>
		-->
		<?php endif; ?>

	</div>

	<div id="sidebar" style="<?php echo $sidebar_css; ?>">
		<?php
		if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('Blog Sidebar')): 
		endif;
		?>
	</div>

<?php get_footer(); ?>