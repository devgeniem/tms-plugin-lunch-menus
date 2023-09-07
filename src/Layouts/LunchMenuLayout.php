<?php
/**
 * Copyright (c) 2023. Hion Digital Oy
 */

namespace TMS\Plugin\LunchMenus\Layouts;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Flexible\Layout;
use TMS\Plugin\LunchMenus\PostType\LunchMenu;
use TMS\Theme\Base\Logger;

/**
 * Class LunchMenuLayout
 *
 * @package TMS\Theme\Base\ACF\Layouts
 */
class LunchMenuLayout extends Layout {

    /**
     * Layout key
     */
    const KEY = '_lunch_menu';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( string $key ) {
        parent::__construct(
            'Päivän lounaslista',
            $key . self::KEY,
            'lunch_menu'
        );

        $this->add_layout_fields();
    }

    /**
     * Add layout fields
     *
     * @return void
     */
    private function add_layout_fields() : void {
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

        $key = $this->get_key();

        try {
            $image_field = ( new Field\Image( $strings['image']['label'] ) )
                ->set_key( "{$key}_image" )
                ->set_name( 'image' )
                ->set_instructions( $strings['image']['instructions'] );

            $link_field = ( new Field\Link( $strings['link']['label'] ) )
                ->set_key( "{$key}_link" )
                ->set_name( 'link' )
                ->set_instructions( $strings['link']['instructions'] );

            $this->add_fields(
                apply_filters(
                    'tms/acf/layout/' . $this->get_key() . '/fields',
                    [ $image_field ]
                )
            );

            $this->add_fields(
                apply_filters(
                    'tms/acf/layout/' . $this->get_key() . '/fields',
                    [ $link_field ]
                )
            );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}
