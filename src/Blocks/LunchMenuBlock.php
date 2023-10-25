<?php
/**
 * Copyright (c) 2023. Hion Digital Oy
 */

namespace TMS\Plugin\LunchMenus\Blocks;

use Geniem\ACF\Block;
use Geniem\ACF\Field;
use Geniem\ACF\Renderer\CallableRenderer;
use Geniem\ACF\Renderer\Dust;
use TMS\Plugin\LunchMenus\Plugin;

/**
 * Class LunchMenuBlock
 *
 * @package TMS\Plugin\LunchMenus\Blocks
 */
class LunchMenuBlock {

    /**
     * The block name (slug, not shown in admin).
     *
     * @var string
     */
    const NAME = 'lunch-menu';

    /**
     * The block description. Used in WP block navigation.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The block category. Used in WP block navigation.
     *
     * @var string
     */
    protected $category = 'common';

    /**
     * The block acf-key.
     *
     * @var string
     */
    const KEY = 'lunch-menu';

    /**
     * The block icon
     *
     * @var string
     */
    protected $icon = 'image-filter';

    /**
     * The block mode. ACF has a few different options.
     * Edit opens the block always in edit mode for example.
     *
     * @var string
     */
    protected $mode = 'edit';

    /**
     * The block supports. You can add all ACF support attributes here.
     *
     * @var array
     */
    protected $supports = [
        'align'  => false,
        'anchor' => true,
    ];


    /**
     * Getter for block name.
     *
     * @return string
     */
    public function get_name() {
        return static::NAME;
    }

    /**
     * Create the block and register it.
     */
    public function __construct() {
        $this->title = 'Päivän lounaslista';

        $block = new Block( $this->title, static::KEY );
        $block->set_category( $this->category );
        $block->set_icon( $this->icon );
        $block->set_description( $this->description );
        $block->set_mode( $this->mode );
        $block->set_supports( $this->supports );
        $block->set_renderer( $this->get_renderer() );

        if ( method_exists( static::class, 'fields' ) ) {
            $block->add_fields( $this->fields() );
        }

        if ( method_exists( static::class, 'filter_data' ) ) {
            $block->add_data_filter( [ $this, 'filter_data' ] );
        }

        $block->register();
    }

    /**
     * Get the renderer.
     * If dust partial is not found in child theme, we will use the parent theme partial.
     *
     * @param string $name Dust partial name, defaults to block name.
     *
     * @return Dust|CallableRenderer
     * @throws Exception Thrown if template is not found.
     */
    protected function get_renderer( string $name = '' ) {
        $name              = $name ?: $this->get_name();
        $partial_file_name = 'block-' . $name . '.dust';
        $file              = get_theme_file_path( '/partials/blocks/' . $partial_file_name );

        if ( ! file_exists( $file ) ) {
            $file = Plugin::get_instance()->get_plugin_path() . '/src/Partials/' . $partial_file_name;
        }

        return new Dust( $file );
    }

    /**
     * Create block fields.
     *
     * @return array
     */
    protected function fields() : array {
        $strings = [
            'image' => [
                'label'        => 'Kuva',
                'instructions' => '',
            ],
            'link' => [
                'label'        => 'Linkki',
                'instructions' => '',
            ],
        ];

        $key = self::KEY;

        $image_field = ( new Field\Image( $strings['image']['label'] ) )
            ->set_key( "{$key}_image" )
            ->set_name( 'image' )
            ->set_instructions( $strings['image']['instructions'] );

        $link_field = ( new Field\Link( $strings['link']['label'] ) )
            ->set_key( "{$key}_link" )
            ->set_name( 'link' )
            ->set_instructions( $strings['link']['instructions'] );

        return apply_filters(
            'tms/block/' . self::KEY . '/fields',
            [
                $image_field,
                $link_field,
            ]
        );
    }

    /**
     * This filters the block ACF data.
     *
     * @param array  $data       Block's ACF data.
     * @param Block  $instance   The block instance.
     * @param array  $block      The original ACF block array.
     * @param string $content    The HTML content.
     * @param bool   $is_preview A flag that shows if we're in preview.
     * @param int    $post_id    The parent post's ID.
     *
     * @return array The block data.
     */
    public function filter_data( $data, $instance, $block, $content, $is_preview, $post_id ) : array { // phpcs:ignore

        $data['menu']            = Plugin::get_menu_of_the_day();
        $data['no_results']      = __( 'No results', 'tms-plugin-lunch-menus' );
        $data['today_for_lunch'] = __( 'Today for lunch', 'tms-plugin-lunch-menus' );

        return apply_filters( 'tms/acf/block/' . self::KEY . '/data', $data );
    }
}
