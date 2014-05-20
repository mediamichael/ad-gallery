<?php get_header(); ?>
<div id="content" class="single-creative container-fluid">
	<?php $creative_id = $post->ID; while(have_posts()): the_post(); ?>	
		<SCRIPT LANGUAGE=JavaScript>
			//function to reload all the SWFs
			function reloadSet(ad_set_id) {
				//copy it
				var copy = document.getElementById(ad_set_id).innerHTML;
				// Remove the div first
 		 		var parent = document.getElementById(ad_set_id).parentNode;

 				// Recreate the div
				var div = document.createElement('div');
 		 		div.setAttribute('id', ad_set_id);

				// Append the div to the parent
 				parent.appendChild(div);
		
			    document.getElementById(ad_set_id).innerHTML = copy;
			}
		</SCRIPT>
	<?php
		echo  get_post_meta($creative_id, 'vantagelocal-creative-retargeting-script', true) ; 
		echo  get_post_meta($creative_id, 'vantagelocal-creative-other-script', true) ; 
	?>
	<div class="project-content row">
		<div class="tabbable">
			<ul class="nav nav-tabs" id="app_tab_ul">
				<?php if( get_post_meta($creative_id, 'vantagelocal-creative-tag-320', true) != '' ): ?>
	  			<li class="tab_colored active" onclick="reloadSet('tabs');"><a class="preview_pane_tab" href="#standard_ads" data-toggle="tab">Standard Ad Units</a></li>
	  			<li class="tab_colored" onclick="reloadSet('tabs');"><a class="preview_pane_tab" href="#mobile_ads" data-toggle="tab">Mobile Ad Units</a></li>
	  			<?php endif; ?>
	 			<li class="pull-right"><button type="button" class="btn btn-success btn-small" id="reload" onclick="reloadSet('tabs');"><i class="icon-refresh icon-white"></i></button></li>
			</ul>
			<div class="tab-content" id="tabs">
	  			<div class="tab-pane active" id="standard_ads">
	  				<div class="ad-set">
 						<div class="row">
 							<div class="ad span2 skyscraper">
								<div id="vl-160x600"><iframe id="iframe160" src="<?php echo get_post_meta($creative_id, 'vantagelocal-creative-tag-160', true); ?>" width=160 height=600 marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=no bordercolor='#000000'></iframe></div>
 							</div>
 							<div class="ad span5 leaderboard">
  								<div id="vl-728x90"><iframe id="iframe728" src="<?php echo get_post_meta($creative_id, 'vantagelocal-creative-tag-728', true); ?>" width=728 height=90 marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=no bordercolor='#000000'></iframe></div>
 							</div>
							<div class="ad span3 large_rectangle">
								<div id="vl-336x280"><iframe id="iframe336" src="<?php echo get_post_meta($creative_id, 'vantagelocal-creative-tag-336', true); ?>" width=336 height=280 marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=no bordercolor='#000000'></iframe></div>
 							</div>
							<div class="ad span3 medium_rectangle">
								<div id="vl-300x250"><iframe id="iframe300" src="<?php echo get_post_meta($creative_id, 'vantagelocal-creative-tag-300', true); ?>" width=300 height=250 marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=no bordercolor='#000000'></iframe></div>
							</div>
						</div>
					</div>	
 				</div>
 				<?php if( get_post_meta($creative_id, 'vantagelocal-creative-tag-320', true) != '' ): ?>
 				<div class="tab-pane" id="mobile_ads">
 					<div class="phone">
 						<div class="mobile">
  							<div id="vl-320x50"><iframe id="iframe320" src="<?php echo get_post_meta($creative_id, 'vantagelocal-creative-tag-320', true); ?>" width=320 height=50 marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=no bordercolor='#000000'></iframe></div>
 						</div>
 					</div>
 				</div>
 				<?php endif; ?>
 			</div>
			<div class="clear" ></div>
		</div>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="post-content">
				<?php the_content(); ?>
				<?php //wp_link_pages(); ?>
			</div>
		</div>
		<div class="approval">
			<div class="love"></div>
			<div class="portfolio_comments">
 				<?php if($data['blog_comments']): ?>
				<?php wp_reset_query(); ?>
				<?php comments_template(); ?>
				<?php endif; ?>
			</div>
		</div>
 	</div>
	<?php endwhile; ?>
</div>
<?php get_footer(); ?>