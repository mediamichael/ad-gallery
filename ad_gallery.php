<?php
/*
Plugin Name: Ad Gallery
Plugin URI: http://www.medialocal.com
Description: A network gallery plugin for creative ad galleries.
Version: 0.4.0
Author: Michael McConnell and Wang Fu (shortcode by Bill Erickson, http://www.billerickson.net, https://github.com/billerickson/display-posts-shortcode/wiki)
Author URI: http://www.medialocal.com
Author Email: contact@medialocal.com
License: Copyright 2013-2014 Michael McConnell

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

/**
 * This plugin allows you to include templates with your plugin so that they can
 * be added with any theme.
 *
 * @package Ad Gallery
 * @version 0.4.0
 * @since 	0.4.0
 */

class ad_gallery {
	/**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   0.4.0
     *
     * @var     string
     */
    const VERSION = '0.4.0';

    /**
     * Unique identifier for the plugin.
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text.
     *
     * @since    0.4.0
     *
     * @var      string
     */
    protected $plugin_slug;

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 0.4.0
	 *
	 * @var   Page_Template_Plugin
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 *
	 * @var      array
	 */
	protected $templates;


	/**
	 * Returns an instance of this class. An implementation of the singleton design pattern.
	 *
	 * @return   Page_Templae_Example    A reference to an instance of this class.
	 * @since    0.4.0
	 */
	public static function get_instance() {

		if( null == self::$instance ) {
			self::$instance = new ad_gallery();
		} // end if

		return self::$instance;

	} // end getInstance

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 *
	 * @version		0.4.0
     * @since 		0.4.0
	 */

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// ---------- Register templates ------------ //

		$this->templates = array();
		$this->plugin_locale = 'pte';

		// Grab the translations for the plugin
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add a filter to the page attributes metabox to inject our template into the page template cache.
		add_filter('page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ) );

		// Add a filter to the save post in order to inject out template into the page cache
		add_filter('wp_insert_post_data', array( $this, 'register_project_templates' ) );

		// Add a filter to the template include in order to determine if the page has our template assigned and return it's path
		add_filter('template_include', array( $this, 'view_project_template') );

		// Add your templates to this array.
		$this->templates = array(
			'dev-slate.php' => __( 'Dev Slate', $this->plugin_slug ),
			'full-wide.php' => __( 'Full Wide', $this->plugin_slug ),
			'gallery-page-template.php' => __( 'Ad Gallery', $this->plugin_slug )
		);

		// adding support for theme templates to be merged and shown in dropdown
		$templates = wp_get_theme()->get_page_templates();
		$templates = array_merge( $templates, $this->templates );

		// ---------- end templates ------------ //

		// Load plugin text domain
		add_action( 'init', array( $this, 'plugin_textdomain' ) );

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		//register_activation_hook( __FILE__, array( $this, 'activate_creative' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
		//add_action('switch_theme', array( $this, 'activate_creative' ));

	    //Call custom page templates
	    add_filter( 'archive_template', array( $this, 'add_page_template_filter' ) );
	    add_filter( 'search_template', array( $this, 'add_page_template_filter' ) );
	    add_filter( 'single_template', array( $this, 'add_page_template_filter' ) );
	    // Add filter for to catch search query
	    //add_filter( 'pre_get_posts', array( $this, 'query_post_type' ) );
	    //Register the Custom Post Type and Taxonomies
		add_action('init', array( $this, 'ad_gallery_register' ) );
		//Add the Admin Custom Fields
		add_action('add_meta_boxes', array( $this, 'ad_gallery_meta_boxes' ) );
		//Save the post meta
		add_action('save_post', array( $this, 'ad_gallery_save_meta' ) );

	} // end constructor

	/**
     * Load the plugin text domain for translation.
     *
     * @since    0.4.0
     */
    public function load_plugin_textdomain() {

	    $domain = $this->plugin_slug;
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

    } // end load_plugin_textdomain

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 *
	 * @param   array    $atts    The attributes for the page attributes dropdown
	 * @return  array    $atts    The attributes for the page attributes dropdown
	 * @verison	0.4.0
	 * @since	0.4.0
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		$templates = array_merge( $templates, $this->templates );
		if ( empty( $templates ) ) {
			$templates = array();
		} // end if

		// Since we've updated the cache, we need to delete the old cache
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	} // end register_project_templates

	/**
	 * Checks if the template is assigned to the page
	 *
	 * @version	0.4.0
	 * @since	0.4.0
	 */
	public function view_project_template( $template ) {

		global $post;

		if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_wp_page_template', true ) ] ) ) {
			return $template;
		} // end if

		$file = plugin_dir_path( __FILE__ ) . 'templates/' . get_post_meta( $post->ID, '_wp_page_template', true );

		// Just to be safe, we check if the file exist first
		if( file_exists( $file ) ) {
			return $file;
		} // end if

		return $template;

	} // end view_project_template


	/*--------------------------------------------*
	 * deactivate the plugin
	*---------------------------------------------*/
	static function deactivate( $network_wide ) {
		foreach($this as $value) {
			ad_gallery::delete_template( $value );
		}
		
	} // end deactivate

	/*--------------------------------------------*
	 * Delete Templates from Theme
	*---------------------------------------------*/
	public function delete_template( $filename ){				
		$theme_path = get_template_directory();
		$template_path = $theme_path . '/' . $filename;  
		if( file_exists( $template_path ) ) {
			unlink( $template_path );
		}

		// we should probably delete the old cache
		wp_cache_delete( $cache_key , 'themes');
	}

	/**
	 * Retrieves and returns the slug of this plugin. This function should be called on an instance
	 * of the plugin outside of this class.
	 *
	 * @return  string    The plugin's slug used in the locale.
	 * @version	0.4.0
	 * @since	0.4.0
	 */
	public function get_locale() {
		return $this->plugin_slug;
	} // end get_locale

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function uninstall( $network_wide ) {
		// TODO:	Define uninstall functionality here
	} // end uninstall

	/**
	 * Loads the plugin text domain for translation
	 */
	public function plugin_textdomain() {
		$domain = 'ad-gallery';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
        load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
        load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	} // end plugin_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		// TODO:	Change 'plugin-name' to the name of your plugin
		wp_enqueue_style( 'ad-gallery-admin-css', plugins_url( 'ad-gallery/css/admin.css' ) );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		// TODO:	Change 'plugin-name' to the name of your plugin
		wp_enqueue_script( 'ad-gallery-admin-admin-js', plugins_url( 'ad-gallery/js/admin.js' ), array('jquery') );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles() {

		wp_register_style( 'bootstrap', plugins_url( 'ad-gallery/css/bootstrap.css' ), array() );
		wp_register_style( 'bootstrap-responsive', plugins_url( 'ad-gallery/css/bootstrap-responsive.css' ), array('bootstrap') );
		
		wp_register_style( 'ad-gallery-plugin-display', plugins_url( 'ad-gallery/css/display.css' ), array(), 1.0, 'screen' );
		wp_register_style( 'ad-gallery-plugin-adset', plugins_url( 'ad-gallery/css/adset.css' ), array(), 1.0, 'screen' );
		wp_register_style( 'ad-gallery-plugin-style', plugins_url( 'ad-gallery/css/style.css' ), array(), 1.0, 'screen' );
		wp_register_style( 'ad-gallery-plugin-media', plugins_url( 'ad-gallery/css/media.css' ), array(), 1.0, 'media' );
		
		wp_enqueue_style( 'bootstrap' );
		wp_enqueue_style( 'bootstrap-responsive' );
		
		wp_enqueue_style( 'ad-gallery-plugin-display' );
		wp_enqueue_style( 'ad-gallery-plugin-adset' );
		wp_enqueue_style( 'ad-gallery-plugin-style' );
		wp_enqueue_style( 'ad-gallery-plugin-media' );
		
	} // end register_plugin_styles

	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts() {

		wp_enqueue_script( 'bootstrap', plugins_url( 'ad-gallery/js/bootstrap.js' ), array('jquery') );
		wp_enqueue_script( 'ad-gallery-plugin-display-js', plugins_url( 'ad-gallery/js/display.js' ), array('jquery') );

	} // end register_plugin_scripts

	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/

	/**
 	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *		  WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *		  Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 */
	 
	/**
 	 * Register a custom post type "creative"
	 * Regitster taxonomies
	 */
	function ad_gallery_register() {
		$labels = array(
			'name' => _x( 'Creative', 'creative' ),
			'singular_name' => _x( 'Creative', 'creative' ),
			'add_new' => _x( 'Add New', 'creative' ),
			'add_new_item' => _x( 'Add New Creative', 'creative' ),
			'edit_item' => _x( 'Edit Creative', 'creative' ),
			'new_item' => _x( 'New Creative', 'creative' ),
			'view_item' => _x( 'View Creative', 'creative' ),
			'search_items' => _x( 'Search Creatives', 'creative' ),
			'not_found' => _x( 'No Creatives found', 'creative' ),
			'not_found_in_trash' => _x( 'No Creatives found in Trash', 'creative' ),
			'parent_item_colon' => _x( 'Parent Creative:', 'creative' ),
			'menu_name' => _x( 'Creatives', 'creative' ),
		);
		
		$args = array(
			'labels' => $labels,
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => true,
			'supports' => array('title', 'editor', 'thumbnail'),
			'description' => 'Creative',
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'has_archive' => true,
			'query_var' => true,
			'can_export' => true,
			'taxonomies' => array('category','post_tag')
		);
		

    	register_post_type( 'creative' , $args );
    	register_taxonomy("creative-categories", array( 'Creative', 'creative' ), array("hierarchical" => true, "label" => "Creative Categories", "singular_label" => "Creative Category", "rewrite" => true));
    	register_taxonomy("creative-features", array( 'Creative', 'creative' ), array("hierarchical" => true, "label" => "Creative Features", "singular_label" => "Creative Feature", "rewrite" => true));
    	register_taxonomy("creative-management", array( 'Creative', 'creative' ), array("hierarchical" => true, "label" => "Creative Management", "singular_label" => "Creative Management", "rewrite" => true));
    	register_taxonomy("creative-placements", array( 'Creative', 'creative' ), array("hierarchical" => true, "label" => "Placement Categories", "singular_label" => "Placement Category", "rewrite" => true));
		
		//ensure Thumbnail Image is Supported
    	if ( function_exists( 'add_theme_support' ) ) {   
    		add_theme_support( 'post-thumbnails' );  
		}
	}
	
	/**
 	 * This function adds the custom fields to the admin, registering the meta boxes
	 */
	function ad_gallery_meta_boxes(){
		add_meta_box("ad-gallery-info-meta", "Creative Details", array($this,'ad_gallery_meta_options'), "creative", "normal", "high");
	}
	/**
 	 * This function sets the options for the meta boxes
	 */
	function ad_gallery_meta_options(){
		global $post;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
		$all_creative_meta = get_post_custom($post->ID);
		$version_number = $all_creative_meta["ad-gallery-version-number"][0];
		$gallery_thumbnail_url = $all_creative_meta["ad-gallery-gallery-thumbnail-url"][0];
		$tag_300 = $all_creative_meta["ad-gallery-tag-300"][0];
		$tag_728 = $all_creative_meta["ad-gallery-tag-728"][0];
		$tag_160 = $all_creative_meta["ad-gallery-tag-160"][0];
		$tag_336 = $all_creative_meta["ad-gallery-tag-336"][0];
		$tag_320 = $all_creative_meta["ad-gallery-tag-320"][0];
		$lp_url_confirmation = $all_creative_meta["ad-gallery-landing-page-url-confirmation"][0];
		$retargeting_script = $all_creative_meta["ad-gallery-retargeting-script"][0];
		$other_script = $all_creative_meta["ad-gallery-other-script"][0];
		$white_label_image_url = $all_creative_meta["ad-gallery-white-label-image-url"][0];

		$html =	'<div class="ad-gallery-meta-box">';
		$html .= '<p><label id="version-number">Creative Version Number<br /></label><input id="version-number-field" type="number" name="ad-gallery-version-number" style="height: 40px;" value='.$version_number.' /></p>';
		$html .= '<p><label id="gallery-thumbnail-url">Gallery Thumbnail Image URL<br /></label><input id="gallery-thumbnail-url" type="url" name="ad-gallery-gallery-thumbnail-url" style="height: 40px; width: 823px;" value='.$gallery_thumbnail_url.' /></p>';
		$html .= '<p><label id="tag-160">160x600 Tag<br /></label><input id="tag-field-160" type="url" name="ad-gallery-tag-160" style="height: 40px; width: 823px;" value='.$tag_160.' /></p>';		
		$html .= '<p><label id="tag-300">300x250 Tag<br /></label><input id="tag-field-300" type="url" name="ad-gallery-tag-300" style="height: 40px; width: 823px;" value='.$tag_300.' /></p>';
		$html .= '<p><label id="tag-336">336x280 Tag<br /></label><input id="tag-field-336" type="url" name="ad-gallery-tag-336" style="height: 40px; width: 823px;" value='.$tag_336.' /></p>';
		$html .= '<p><label id="tag-728">728x90 Tag<br /></label><input id="tag-field-728" type="url" name="ad-gallery-tag-728" style="height: 40px; width: 823px;" value='.$tag_728.' /></p>';
		$html .= '<p><label id="tag-320">320x50 Tag<br /></label><input id="tag-field-320" type="url" name="ad-gallery-tag-320" style="height: 40px; width: 823px;" value='.$tag_320.' /></p>';
		$html .= '<p><label id="url-confirmation">Landing Page URL Confirmation<br /></label><input id="url-confirmation-field" type="url" name="ad-gallery-landing-page-url-confirmation" style="height: 40px; width: 823px;" value='.$lp_url_confirmation.' /></p>';
		$html .= '<p><label id="retargeting-script">Retargeting Script<br /></label><textarea id="retargeting-script-field" name="ad-gallery-retargeting-script" style="height: 74px; width: 823px;">'.$retargeting_script.'</textarea></p>';
		$html .= '<p><label id="other-script">Other Script<br /></label><textarea id="other-script-field" type="text" name="ad-gallery-other-script" style="height: 74px; width: 823px;">'.$other_script.'</textarea></p>';
		$html .= '<p><label id="img-url">White Label Image URL<br /></label><input id="white-label-image-url" type="url" name="ad-gallery-white-label-image-url" style="height: 40px; width: 823px;" value='.$white_label_image_url.' /></p>';
		$html .= '</div>';
		
		print_r($html);
    }
    /**
 	 * This function determines how to save the custom meta data
	 */
    function ad_gallery_save_meta(){
		global $post;
    	
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
			return $post_id;
		}else{
			update_post_meta($post->ID, "ad-gallery-version-number", $_POST["ad-gallery-version-number"]);
			update_post_meta($post->ID, "ad-gallery-gallery-thumbnail-url", $_POST["ad-gallery-gallery-thumbnail-url"]);
			update_post_meta($post->ID, "ad-gallery-tag-300", $_POST["ad-gallery-tag-300"]);
			update_post_meta($post->ID, "ad-gallery-tag-728", $_POST["ad-gallery-tag-728"]);
			update_post_meta($post->ID, "ad-gallery-tag-160", $_POST["ad-gallery-tag-160"]);
			update_post_meta($post->ID, "ad-gallery-tag-336", $_POST["ad-gallery-tag-336"]);
			update_post_meta($post->ID, "ad-gallery-tag-320", $_POST["ad-gallery-tag-320"]);
			update_post_meta($post->ID, "ad-gallery-landing-page-url-confirmation", $_POST["ad-gallery-landing-page-url-confirmation"]);
			update_post_meta($post->ID, "ad-gallery-retargeting-script", $_POST["ad-gallery-retargeting-script"]);
			update_post_meta($post->ID, "ad-gallery-other-script", $_POST["ad-gallery-other-script"]);
			update_post_meta($post->ID, "ad-gallery-white-label-image-url", $_POST["ad-gallery-white-label-image-url"]);
		}
	}
	
	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *		  WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *		  Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 */
	function filter_method_name() {
	    // TODO:	Define your filter method here
	} // end filter_method_name
	
	function add_page_template_filter() {
		if ( is_singular( 'creative' ) ) {
        	$page_template = WP_PLUGIN_DIR . '/ad-gallery/templates/single-creative.php';
    	}
		else if ( is_page_template( 'gallery-page-template.php' ) ) {
        	$page_template = WP_PLUGIN_DIR . '/ad-gallery/templates/gallery-page-template.php';
    	}
    	else if ( is_archive( 'creative' ) ) {
        	$page_template = WP_PLUGIN_DIR . '/ad-gallery/templates/archive-creative.php';
    	}
    	else if ( is_search( 'creative' ) ) {
        	$page_template = WP_PLUGIN_DIR . '/ad-gallery/templates/search.php';
    	}

    	return $page_template;
	}
	
	// Include the tags in the search query
	/*
	function query_post_type($query) {
		var_dump( 'QUERY_FILTER_ATTEMPTED' );	//XXX
		$post_types = get_post_types();
    	if ( is_category() || is_tag()) {
			$post_type = get_query_var('creative');
			if ( $post_type )
            	$post_type = $post_type;
        	else
            	$post_type = $post_types;
        	$query->set('post_type', $post_type);
		
    	return $query;
    	}
	}
	*/
} // end class

// TODO:	Update the instantiation call of your plugin to the name given at the class definition

$plugin_name = new ad_gallery();

	

/*--------------------------------------------*
	 * SHORTCODE
*---------------------------------------------*/
	 
// Register shortcode
add_shortcode( 'vlc', 'ad_gallery_gallery' );
	
function ad_gallery_gallery( $atts ) {
	
		// Original Attributes, for filters
		$original_atts = $atts;
	
		// Pull in shortcode attributes and set defaults
		$atts = shortcode_atts( array(
			'author'              => '',
			'category'            => '',
			'date_format'         => '(n/j/Y)',
			'id'                  => false,
			'ignore_sticky_posts' => false,
			'image_size'          => 'medium',
			'include_content'     => false,
			'include_date'        => false,
			'include_excerpt'     => false,
			'meta_key'            => '',
			'no_posts_message'    => '',
			'offset'              => 0,
			'order'               => 'DESC',
			'orderby'             => 'date',
			'post_parent'         => false,
			'post_status'         => 'publish',
			'post_type'           => 'creative',
			'posts_per_page'      => '40',
			'tag'                 => '',
			'tax_operator'        => 'IN',
			'tax_term'            => false,
			'taxonomy'            => false,
			'wrapper'             => 'div',
			'show_title'          => false,
		), $atts );
	
		$author = sanitize_text_field( $atts['author'] );
		$category = sanitize_text_field( $atts['category'] );
		$date_format = sanitize_text_field( $atts['date_format'] );
		$id = $atts['id']; // Sanitized later as an array of integers
		$ignore_sticky_posts = (bool) $atts['ignore_sticky_posts'];
		$image_size = sanitize_key( $atts['image_size'] );
		$include_content = (bool)$atts['include_content'];
		$include_date = (bool)$atts['include_date'];
		$include_excerpt = (bool)$atts['include_excerpt'];
		$meta_key = sanitize_text_field( $atts['meta_key'] );
		$no_posts_message = sanitize_text_field( $atts['no_posts_message'] );
		$offset = intval( $atts['offset'] );
		$order = sanitize_key( $atts['order'] );
		$orderby = sanitize_key( $atts['orderby'] );
		$post_parent = $atts['post_parent']; // Validated later, after check for 'current'
		$post_status = $atts['post_status']; // Validated later as one of a few values
		$post_type = sanitize_text_field( $atts['post_type'] );
		$posts_per_page = intval( $atts['posts_per_page'] );
		$tag = sanitize_text_field( $atts['tag'] );
		$tax_operator = $atts['tax_operator']; // Validated later as one of a few values
		$tax_term = sanitize_text_field( $atts['tax_term'] );
		$taxonomy = sanitize_key( $atts['taxonomy'] );
		$wrapper = sanitize_text_field( $atts['wrapper'] );
		$show_title = (bool)$atts['show_title'];
	
		
		// Set up initial query for post
		$args = array(
			'category_name'       => $category,
			'order'               => $order,
			'orderby'             => $orderby,
			'post_type'           => explode( ',', $post_type ),
			'posts_per_page'      => $posts_per_page,
			'tag'                 => $tag,
		);
		
		// Ignore Sticky Posts
		if( $ignore_sticky_posts )
			$args['ignore_sticky_posts'] = true;
		
		// Meta key (for ordering)
		if( !empty( $meta_key ) )
			$args['meta_key'] = $meta_key;
		
		// If Post IDs
		if( $id ) {
			$posts_in = array_map( 'intval', explode( ',', $id ) );
			$args['post__in'] = $posts_in;
		}
		
		// Post Author
		if( !empty( $author ) )
			$args['author_name'] = $author;
			
		// Offset
		if( !empty( $offset ) )
			$args['offset'] = $offset;
		
		// Post Status	
		$post_status = explode( ', ', $post_status );		
		$validated = array();
		$available = array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash', 'any' );
		foreach ( $post_status as $unvalidated )
			if ( in_array( $unvalidated, $available ) )
				$validated[] = $unvalidated;
		if( !empty( $validated ) )		
			$args['post_status'] = $validated;
		
		
		// If taxonomy attributes, create a taxonomy query
		if ( !empty( $taxonomy ) && !empty( $tax_term ) ) {
		
			// Term string to array
			$tax_term = explode( ', ', $tax_term );
			
			// Validate operator
			if( !in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) )
				$tax_operator = 'IN';
						
			$tax_args = array(
				'tax_query' => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => $tax_term,
						'operator' => $tax_operator
					)
				)
			);
			
			// Check for multiple taxonomy queries
			$count = 2;
			$more_tax_queries = false;
			while( 
				isset( $original_atts['taxonomy_' . $count] ) && !empty( $original_atts['taxonomy_' . $count] ) && 
				isset( $original_atts['tax_' . $count . '_term'] ) && !empty( $original_atts['tax_' . $count . '_term'] ) 
			):
			
				// Sanitize values
				$more_tax_queries = true;
				$taxonomy = sanitize_key( $original_atts['taxonomy_' . $count] );
				$terms = explode( ', ', sanitize_text_field( $original_atts['tax_' . $count . '_term'] ) );
				$tax_operator = isset( $original_atts['tax_' . $count . '_operator'] ) ? $original_atts['tax_' . $count . '_operator'] : 'IN';
				$tax_operator = in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) ? $tax_operator : 'IN';
				
				$tax_args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $terms,
					'operator' => $tax_operator
				);
		
				$count++;
				
			endwhile;
			
			if( $more_tax_queries ):
				$tax_relation = 'AND';
				if( isset( $original_atts['tax_relation'] ) && in_array( $original_atts['tax_relation'], array( 'AND', 'OR' ) ) )
					$tax_relation = $original_atts['tax_relation'];
				$args['tax_query']['relation'] = $tax_relation;
			endif;
			
			$args = array_merge( $args, $tax_args );
		}
		
		// If post parent attribute, set up parent
		if( $post_parent ) {
			if( 'current' == $post_parent ) {
				global $post;
				$post_parent = $post->ID;
			}
			$args['post_parent'] = intval( $post_parent );
		}
		
		// Set up html elements used to wrap the posts. 
		// Default is ul/li, but can also be ol/li and div/div
		$wrapper_options = array( 'ul', 'ol', 'div' );
		if( ! in_array( $wrapper, $wrapper_options ) )
			$wrapper = 'ul';
		$inner_wrapper = 'div' == $wrapper ? 'div' : 'li';
	
		$thum_image_size = 200;
		$temp_thum_image_type = $image_size . "_size_w";
		$thum_image_size = get_option($temp_thum_image_type)  ;
		$thum_image_per = $thum_image_size / get_option('large_size_w') * 0.84 * 100;
		
		$listing = new WP_Query( apply_filters( 'ad_gallery_shortcode_args', $args, $original_atts ) );
		if ( ! $listing->have_posts() )
			return apply_filters( 'ad_gallery_shortcode_no_results', wpautop( $no_posts_message ) );
			
		$inner = '';
		ob_start();
?>
		<div class="row-fluid gallery-wrapper">
		
<?php
		while ( $listing->have_posts() ): $listing->the_post(); global $post;
?>
			
			<div class="span3 gallery-item" style="width: <?php echo $thum_image_per ?>% ; min-width: <?php echo $thum_image_size * 0.75 ?>px;">
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
						$gallery_thumbnail_url = $all_creative_meta["ad-gallery-gallery-thumbnail-url"][0];
						$thumbnailsrc = '<img class="attachment-post-thumbnail wp-post-image" src="'.$gallery_thumbnail_url.'">' ;
					}		
				?>
				<div class="image">
					<a href="<?php the_permalink(); ?>">
						<?php echo $thumbnailsrc; ?>
					</a>
				</div>
			</div>
<?php
			
			
		endwhile; wp_reset_postdata();
?>
		</div>
<?php
		
		$output = ob_get_contents();
	    ob_end_clean();
		return $output;
	}
