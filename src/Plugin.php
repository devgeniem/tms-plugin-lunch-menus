<?php
/**
 * Copyright (c) 2021 Geniem Oy.
 */

namespace TMS\Plugin\LunchMenus;

use TMS\Plugin\LunchMenus\PostType\LunchMenu;
use TMS\Plugin\LunchMenus\Layouts\LunchMenuLayout;
use TMS\Plugin\LunchMenus\Fields\PageLunchMenusFieldGroup;
use TMS\Plugin\LunchMenus\Blocks\LunchMenuBlock;

/**
 * Class Plugin
 *
 * @package TMS\Plugin\LunchMenus
 */
final class Plugin {

    /**
     * Holds the singleton.
     *
     * @var Plugin
     */
    protected static $instance;

    /**
     * Current plugin version.
     *
     * @var string
     */
    protected $version = '';
    /**
     * Path to assets distribution versions.
     *
     * @var string
     */
    protected string $dist_path = '';
    /**
     * Uri to assets distribution versions.
     *
     * @var string
     */
    protected string $dist_uri = '';

    /**
     * Get the instance.
     *
     * @return Plugin
     */
    public static function get_instance() : Plugin {
        return self::$instance;
    }

    /**
     * The plugin directory path.
     *
     * @var string
     */
    protected $plugin_path = '';

    /**
     * The plugin root uri without trailing slash.
     *
     * @var string
     */
    protected $plugin_uri = '';

    /**
     * Get the version.
     *
     * @return string
     */
    public function get_version() : string {
        return $this->version;
    }

    /**
     * Get the plugin directory path.
     *
     * @return string
     */
    public function get_plugin_path() : string {
        return $this->plugin_path;
    }

    /**
     * Get the plugin directory uri.
     *
     * @return string
     */
    public function get_plugin_uri() : string {
        return $this->plugin_uri;
    }

    /**
     * Initialize the plugin by creating the singleton.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    public static function init( $version = '', $plugin_path = '' ) {
        if ( empty( self::$instance ) ) {
            self::$instance = new self( $version, $plugin_path );
            self::$instance->hooks();
        }
    }

    /**
     * Get the plugin instance.
     *
     * @return Plugin
     */
    public static function plugin() {
        return self::$instance;
    }

    /**
     * Initialize the plugin functionalities.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    protected function __construct( $version = '', $plugin_path = '' ) {
        $this->version     = $version;
        $this->plugin_path = $plugin_path;
        $this->plugin_uri  = plugin_dir_url( $plugin_path ) . basename( $this->plugin_path );
        $this->dist_path   = $this->plugin_path . '/assets/dist/';
        $this->dist_uri    = $this->plugin_uri . '/assets/dist/';
    }

    /**
     * Add plugin hooks and filters.
     */
    protected function hooks() {
        add_action( 'init', \Closure::fromCallable( [ $this, 'load_localization' ] ), 0 );
        add_action( 'init', \Closure::fromCallable( [ $this, 'init_classes' ] ), 0 );
        add_filter( 'dustpress/models', \Closure::fromCallable( [ $this, 'dustpress_models' ] ) );
        add_filter( 'dustpress/partials', \Closure::fromCallable( [ $this, 'dustpress_partials' ] ) );
        add_filter( 'page_template', \Closure::fromCallable( [ $this, 'register_page_template_path' ] ) );
        add_filter( 'theme_page_templates', \Closure::fromCallable( [ $this, 'register_page_template' ] ) );
        add_filter(
            'tms/acf/field/fg_page_components_components/layouts',
            \Closure::fromCallable( [ $this, 'append_lunch_menu_layout' ] )
        );
        add_filter(
            'tms/acf/layout/lunch_menu/data',
            \Closure::fromCallable( [ $this, 'format_lunch_menu_data' ] )
        );
    }

    /**
     * Load plugin localization
     */
    public function load_localization() {
        \load_plugin_textdomain(
            'tms-plugin-lunch-menus',
            false,
            dirname( plugin_basename( __DIR__ ) ) . '/languages/'
        );
    }

    /**
     * Init classes
     */
    protected function init_classes() {
        ( new LunchMenu() );
        ( new LunchMenuBlock() );
        ( new PageLunchMenusFieldGroup() );
    }

    /**
     * Add this plugin's models directory to DustPress.
     *
     * @param array $models The original array.
     *
     * @return array
     */
    protected function dustpress_models( array $models = [] ) : array {
        $models[] = $this->plugin_path . '/src/Models/';

        return $models;
    }

    /**
     * Register page-combined-events-list.php template path.
     *
     * @param string $template Page template name.
     *
     * @return string
     */
    private function register_page_template_path( string $template ) : string {
        if ( get_page_template_slug() === 'page-lunch-menus-list.php' ) {
            $template = $this->plugin_path . '/src/Models/page-lunch-menus-list.php';
        }

        return $template;
    }

    /**
     * Register page-lunch-menus-list.php making it accessible via page template picker.
     *
     * @param array $templates Page template choices.
     *
     * @return array
     */
    private function register_page_template( $templates ) : array {
        $templates['page-lunch-menus-list.php'] = __( 'Lounaslista', 'tms-plugin-lunch-menus' );

        return $templates;
    }

    /**
     * Add this plugin's partials directory to DustPress.
     *
     * @param array $partials The original array.
     *
     * @return array
     */
    protected function dustpress_partials( array $partials = [] ) : array {
        $partials[] = $this->plugin_path . '/src/Partials/';

        return $partials;
    }

    /**
     * Append lunch menu layout
     *
     * @param array $layouts Flexible Content layouts.
     *
     * @return array
     */
    protected function append_lunch_menu_layout( array $layouts ) : array {

        $layouts[] = LunchMenuLayout::class;

        return $layouts;
    }

    /**
     * Format lunch menu file data.
     *
     * @param array $data Layout data.
     *
     * @return array
     */
    protected function format_lunch_menu_data( array $data ) : array {

        $data['menu']            = static::get_menu_of_the_day();
        $data['no_results']      = __( 'No results', 'tms-plugin-lunch-menus' );
        $data['today_for_lunch'] = __( 'Today for lunch', 'tms-plugin-lunch-menus' );

        return $data;
    }

    /**
     * Get menu of the day.
     *
     * @return array
     */
    public static function get_menu_of_the_day() : array {
        $args = [
            'post_type'      => PostType\LunchMenu::SLUG,
            'posts_per_page' => 999,
            'orderby'        => 'post_date',
        ];

        $query = new \WP_Query( $args );

        if ( empty( $query->posts ) ) {
            return [];
        }

        $posts = $query->posts;
        $current_datetime = \current_datetime()->format('Y-m-d H:i:s');

        foreach ( $posts as $post ) {
            $start_datetime = \get_field( 'start_datetime', $post );
            $end_datetime   = \get_field( 'end_datetime', $post );

            // Skip menu if start-time is coming up or end-time is past
            if ( $current_datetime <= $start_datetime || $current_datetime >= $end_datetime ) {
                continue;
            }

            $menus = \get_field( 'days', $post );
        }

        if ( empty( $menus ) ) {
            return [];
        }

        $today = date( 'Y-m-d' );

        foreach ( $menus as $menu ) {
            if ( $menu['days'] === $today ) {
                return $menu;
            }
        }

        // No menu founds for current day.
        return [];
    }
}
