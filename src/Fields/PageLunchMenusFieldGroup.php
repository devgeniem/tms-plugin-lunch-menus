<?php
/**
 * Copyright (c) 2023. Hion Digital Oy
 */

namespace TMS\Plugin\LunchMenus\Fields;

use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\ACF\Field;
use TMS\Plugin\LunchMenus\PostType\LunchMenu;
use TMS\Theme\Base\Logger;

/**
 * Class PageLunchMenusFieldGroup
 *
 * @package TMS\Theme\Base\ACF
 */
class PageLunchMenusFieldGroup {

    /**
     * PageGroup constructor.
     */
    public function __construct() {
        add_action(
            'init',
            \Closure::fromCallable( [ $this, 'register_fields' ] )
        );
    }

    /**
     * Register fields
     */
    protected function register_fields() : void {
        try {
            $group_title = _x( 'Lounaslistan asetukset', 'plugin ACF', 'tms-plugin-lunch-menus' );

            $field_group = ( new Group( $group_title ) )
                ->set_key( 'fg_lunch_menus_page_fields' );

            $rule_group = ( new RuleGroup() )
                ->add_rule( 'page_template', '==', LunchMenu::TEMPLATE );

            $field_group
                ->add_rule_group( $rule_group )
                ->set_hidden_elements(
                    [
                        'discussion',
                        'comments',
                        'format',
                        'send-trackbacks',
                    ]
                );

            $field_group->add_fields(
                apply_filters(
                    'tms/acf/group/' . $field_group->get_key() . '/fields',
                    [
                        $this->get_lunchmenu_page_options( $field_group->get_key() ),
                    ]
                )
            );

            $field_group = apply_filters(
                'tms/acf/group/' . $field_group->get_key(),
                $field_group
            );

            $field_group->register();
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }
    }

    /**
     * Get lunchmenu page weeks option field
     *
     * @param string $key Field group key.
     *
     * @return Field\Select
     * @throws Exception In case of invalid option.
     */
    protected function get_lunchmenu_page_options( string $key ) : Field\Select {
        $strings = [
            'weeks' => [
                'label'        => _x( 'Lounasmenu', 'plugin ACF', 'tms-plugin-lunch-menus' ),
                'instructions' => 'Aseta monenko viikon lounaslistat haluat listattavaksi.',
            ],
        ];

        $weeks_field = ( new Field\Select( $strings['weeks']['label'] ) )
                ->set_key( "{$key}_week_count" )
                ->set_name( 'week_count' )
                ->set_choices( [
                    '1' => '1 viikon',
                    '2' => '2 viikon',
                    '3' => '3 viikon',
                    '4' => '4 viikon',
                ] )
                ->set_default_value( 1 )
                ->set_wrapper_width( 50 )
                ->set_instructions( $strings['weeks']['instructions'] );

        return $weeks_field;
    }
}
