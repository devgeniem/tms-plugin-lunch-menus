<?php
/**
 *  Copyright (c) 2023. Geniem Oy
 */

namespace TMS\Plugin\LunchMenus\PostType;

use Geniem\ACF\Field;
use Geniem\ACF\Group;
use Geniem\ACF\Exception;
use Geniem\ACF\RuleGroup;
use TMS\Theme\Base\Logger;

/**
 * Class LunchMenu
 *
 * @package TMS\Plugin\LunchMenus\PostType
 */
class LunchMenu {

    /**
     * Template
     */
    const TEMPLATE = 'page-lunch-menus-list.php';

    /**
     * This defines the slug of this post type.
     */
    public const SLUG = 'lunch-menu-cpt';

    /**
     * This defines what is shown in the url. This can
     * be different than the slug which is used to register the post type.
     *
     * @var string
     */
    private $url_slug = 'lunch-menu';

    /**
     * Define the CPT description
     *
     * @var string
     */
    private $description = '';

    /**
     * This is used to position the post type menu in admin.
     *
     * @var int
     */
    private $menu_order = 41;

    /**
     * This defines the CPT icon.
     *
     * @var string
     */
    private $icon = 'dashicons-food';

    /**
     * Constructor
     */
    public function __construct() {
        // Make possible description text translatable.
        $this->description = _x( 'lunch-menu', 'theme CPT', 'tms-plugin-lunch-menus' );

        add_action( 'init', \Closure::fromCallable( [ $this, 'register' ] ), 100, 0 );
        add_action( 'acf/init', \Closure::fromCallable( [ $this, 'fields' ] ), 50, 0 );
    }

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
    }

    /**
     * This registers the post type.
     *
     * @return void
     */
    private function register() {
        $labels = [
            'name'                  => 'Lounaslistat',
            'singular_name'         => 'Lounaslista',
            'menu_name'             => 'Lounaslistat',
            'name_admin_bar'        => 'Lounaslistat',
            'archives'              => 'Arkistot',
            'attributes'            => 'Ominaisuudet',
            'parent_item_colon'     => 'Vanhempi:',
            'all_items'             => 'Kaikki',
            'add_new_item'          => 'Lisää uusi',
            'add_new'               => 'Lisää uusi',
            'new_item'              => 'Uusi',
            'edit_item'             => 'Muokkaa',
            'update_item'           => 'Päivitä',
            'view_item'             => 'Näytä',
            'view_items'            => 'Näytä kaikki',
            'search_items'          => 'Etsi',
            'not_found'             => 'Ei löytynyt',
            'not_found_in_trash'    => 'Ei löytynyt roskakorista',
            'featured_image'        => 'Kuva',
            'set_featured_image'    => 'Aseta kuva',
            'remove_featured_image' => 'Poista kuva',
            'use_featured_image'    => 'Käytä kuvana',
            'insert_into_item'      => 'Aseta julkaisuun',
            'uploaded_to_this_item' => 'Lisätty tähän julkaisuun',
            'items_list'            => 'Listaus',
            'items_list_navigation' => 'Listauksen navigaatio',
            'filter_items_list'     => 'Suodata listaa',
        ];

        $rewrite = [
            'slug'       => $this->url_slug,
            'with_front' => true,
            'pages'      => true,
            'feeds'      => true,
        ];

        $args = [
            'label'               => $labels['name'],
            'description'         => '',
            'labels'              => $labels,
            'supports'            => [ 'title', 'thumbnail', 'revisions', 'editor' ],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => $this->menu_order,
            'menu_icon'           => $this->icon,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'rewrite'             => $rewrite,
            'capability_type'     => 'lunch_menu',
            'map_meta_cap'        => true,
            'show_in_rest'        => true,
        ];

        $args = apply_filters(
            'tms/post_type/' . static::SLUG . '/args',
            $args
        );

        register_post_type( static::SLUG, $args );
    }

    /**
     * Register fields
     */
    protected function fields() {
        try {
            $group_title = _x( 'Tiedot', 'theme ACF', 'tms-plugin-lunch-menus' );

            $field_group = ( new Group( $group_title ) )
                ->set_key( 'fg_lunch_menu_fields' );

            $rule_group = ( new RuleGroup() )
                ->add_rule( 'post_type', '==', static::SLUG );

            $field_group
                ->add_rule_group( $rule_group )
                ->set_position( 'normal' )
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
                        $this->get_event_tab( $field_group->get_key() ),
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
     * Get event tab
     *
     * @param string $key Field group key.
     *
     * @return Field\Tab
     * @throws Exception In case of invalid option.
     */
    protected function get_event_tab( string $key ) : ?Field\Tab {
        $strings = [
            'tab'                => 'Lounaslista',
            'start_datetime'     => [
                'label'        => 'Aloitusajankohta',
                'instructions' => '',
            ],
            'end_datetime'       => [
                'label'        => 'Päättymisajankohta',
                'instructions' => '',
            ],
            'description'        => [
                'label'        => 'Lisätiedot',
                'instructions' => '',
            ],
            'days'        => [
                'label'        => 'Päivät',
                'instructions' => '',
                'date'        => [
                    'label'        => 'Päivämäärä',
                    'instructions' => '',
                ],
                'foods'        => [
                    'label'        => 'Ruoat',
                    'instructions' => '',
                ],
                'food_description' => [
                    'label'        => 'Lisätiedot',
                    'instructions' => '',
                ],
                'link'        => [
                    'label'        => 'Linkki',
                    'instructions' => '',
                ],
                'link_title'        => [
                    'label'        => 'Linkin teksti',
                    'instructions' => '',
                ],
            ],
        ];

        try {
            $tab = ( new Field\Tab( $strings['tab'] ) )
                ->set_placement( 'left' );

            $start_datetime = ( new Field\DatePicker( $strings['start_datetime']['label'] ) )
                ->set_key( "{$key}_start_datetime" )
                ->set_name( 'start_datetime' )
                ->set_required()
                ->set_instructions( $strings['start_datetime']['instructions'] )
                ->set_display_format( 'j.n.Y' )
                ->set_return_format( 'Y-m-d' )
                ->redipress_add_queryable( 'start_datetime' );

            $end_datetime = ( new Field\DatePicker( $strings['end_datetime']['label'] ) )
                ->set_key( "{$key}_end_datetime" )
                ->set_name( 'end_datetime' )
                ->set_required()
                ->set_instructions( $strings['end_datetime']['instructions'] )
                ->set_display_format( 'j.n.Y' )
                ->set_return_format( 'Y-m-d' )
                ->redipress_add_queryable( 'end_datetime' );

            $description = ( new Field\Textarea( $strings['description']['label'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->set_new_lines( 'br' )
                ->set_instructions( $strings['description']['instructions'] );

            $days = ( new Field\Repeater( $strings['days']['label'] ) )
                ->set_key( "{$key}_days" )
                ->set_name( 'days' )
                ->set_layout( 'block' )
                ->set_max( 7 )
                ->set_button_label( $strings['days']['label'] )
                ->set_instructions( $strings['days']['instructions'] );

            $date = ( new Field\DatePicker( $strings['days']['date']['label'] ) )
                ->set_key( "{$key}_date" )
                ->set_name( 'days' )
                ->set_instructions( $strings['days']['date']['instructions'] )
                ->set_display_format( 'j.n.Y' )
                ->set_return_format( 'Y-m-d' );

            $foods = ( new Field\Textarea( $strings['days']['foods']['label'] ) )
                ->set_key( "{$key}_foods" )
                ->set_name( 'foods' )
                ->set_new_lines( 'br' )
                ->set_instructions( $strings['days']['foods']['instructions'] );

            $food_description = ( new Field\Textarea( $strings['days']['food_description']['label'] ) )
                ->set_key( "{$key}_food_description" )
                ->set_name( 'food_description' )
                ->set_new_lines( 'br' )
                ->set_instructions( $strings['days']['food_description']['instructions'] );

            $link = ( new Field\Link( $strings['days']['link']['label'] ) )
                ->set_key( "{$key}_link" )
                ->set_name( 'link' )
                ->set_instructions( $strings['days']['link']['instructions'] );

            $days->add_fields( [
                $date,
                $foods,
                $food_description,
                $link,
            ] );

            $tab->add_fields( [
                $start_datetime,
                $end_datetime,
                $description,
                $days,
            ] );

            return $tab;
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return null;
    }

}
