<?php
get_header(); ?>
	<div id="content" class="creative-gallery full-width">
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
			
			<?php endwhile; ?>
		</div>
	</div>
<?php get_footer(); ?>