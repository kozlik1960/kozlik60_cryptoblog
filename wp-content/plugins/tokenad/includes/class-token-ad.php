<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://token.ad
 * @since      1.0.0
 *
 * @package    Token_Ad
 * @subpackage Token_Ad/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Token_Ad
 * @subpackage Token_Ad/includes
 * @author     TokenAd
 */
class Token_Ad {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Token_Ad_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
    {
		$this->plugin_name = 'token-ad';
		$this->version = '1.0.0';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->add_areas();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Token_Ad_Loader. Orchestrates the hooks of the plugin.
	 * - Token_Ad_i18n. Defines internationalization functionality.
	 * - Token_Ad_Admin. Defines all hooks for the admin area.
	 * - Token_Ad_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
    {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-token-ad-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-token-ad-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-token-ad-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-token-ad-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-token-ad-add-area.php';

		if( !defined('WP_CONTENT_DIR') )
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		
		if( is_file( WP_CONTENT_DIR . '/wp-cache-config.php' ) ) {
		    require_once WP_CONTENT_DIR . '/wp-cache-config.php';
		}
		
		$this->loader = new Token_Ad_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Token_Ad_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
    {
		$plugin_i18n = new Token_Ad_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
    {
		$plugin_admin = new Token_Ad_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );
		$this->loader->add_action( 'admin_menu' , $plugin_admin, 'make_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_setting' );
				

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
    {
		$plugin_public = new Token_Ad_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
    {
		$this->loader->run();
	}

	public function add_areas()
    {
		$plugin_area = new Token_Ad_Add_Area;
		$this->loader->add_action( 'admin_bar_menu', $plugin_area, 'modify_admin_bar', 999 );
		$this->loader->add_action( 'wp_head', $plugin_area, 'wp_head_area' );
		$this->loader->add_action( 'comment_form_before', $plugin_area, 'comment_form_before_area' );
		$this->loader->add_action( 'comment_form_after', $plugin_area, 'comment_form_after_area' );
		$this->loader->add_action( 'dynamic_sidebar_before', $plugin_area, 'dynamic_sidebar_before_area');
		$this->loader->add_action( 'dynamic_sidebar_after', $plugin_area, 'dynamic_sidebar_after_area');
		$this->loader->add_filter('the_content',  $plugin_area, 'content_after_area');
		$this->loader->add_filter('the_content',  $plugin_area, 'content_before_area');
		$this->loader->add_filter('the_excerpt',  $plugin_area, 'excerpt_after_area');
		$this->loader->add_filter('widget_text_content',  $plugin_area, 'widget_text_content_area');
        $this->loader->add_filter('widget_custom_html_content',  $plugin_area, 'widget_custom_html_content_area');
		$this->loader->add_action( 'get_footer', $plugin_area, 'get_footer_area' );
		$this->loader->add_action( 'wp_footer', $plugin_area, 'wp_footer_area' );

		$plugin_area->empty_povt();
		$this->loader->add_action( 'wp_footer', $plugin_area, 'add_obhod' );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
    {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Token_Ad_Loader    Orchestrates the hooks of the plugin.
	 * @return    Token_Ad_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
    {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
    {
		return $this->version;
	}

}
