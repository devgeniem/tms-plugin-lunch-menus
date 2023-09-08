<?php

use TMS\Plugin\LunchMenus\PostType;
use TMS\Theme\Base\Logger;

/**
 * Copyright (c) 2023. Hion Digital Oy
 * Template Name: Lounaslista
 */

/**
 * The PageLunchMenusList class.
 */
class PageLunchMenusList extends BaseModel {
    /**
     * Template
     */
    const TEMPLATE = 'page-lunch-menus-list.php';

    /**
     * Week count
     */
    public function week_count() : ?string {
        return get_field( 'week_count', \get_the_ID() );
    }

    /**
     * Get title
     *
     * @return string
     */
    public function lunch_menu_title() : string {
        return __( 'Lunch menu', 'tms-plugin-lunch-menus' );
    }

    /**
     * Get title
     *
     * @return string
     */
    public function additional_information_link() : string {
        return __( 'Additional information link', 'tms-plugin-lunch-menus' );
    }

    /**
     * Get no results text
     *
     * @return string
     */
    public function no_results() : string {
        return __( 'No results', 'tms-plugin-lunch-menus' );
    }

    /**
     * Get lunch menus.
     */
    public function lunch_menus() {
        $items = $this->get_lunch_menus();

        if ( empty( $items ) ) {
            return [];
        }

        $formatted_items = self::format_lunch_menu_items( $items );

        return [
            'items'   => $formatted_items,
        ];
    }

    /**
     * Get lunch menus.
     *
     * @return array
     */
    protected function get_lunch_menus() : array {

        $posts       = $this->get_lunch_posts();
        $dates       = $this->lunch_menu_dates();
        $lunch_menus = [];

        if ( empty( $posts ) ) {
            return [];
        }

        foreach ( $dates as $date ) {
            foreach ( $posts as $post ) {
                $start_datetime = get_field( 'start_datetime', $post );
                $end_datetime   = get_field( 'end_datetime', $post );

                if ( $date >= $start_datetime && $date <= $end_datetime ) {

                    if( ! isset( $lunch_menus[ $date ] ) ) {
                        $lunch_menus[ $date ] = [];
                    }

                    $lunch_menus[ $date ][] = $post;
                }
            }
        }

        $lunch_menus = array_map( function ( $lunch_menu ) {
            return $lunch_menu[0];
        }, $lunch_menus );

        return $lunch_menus;
    }

    /**
     * Get current date(s) depending week setting.
     *
     * @return array
     */
    public function lunch_menu_dates() {
        $week_count = (int) get_field( 'week_count', \get_the_ID() );

        if ( $week_count === 1 ) {
            $date[] = date( 'Y-m-d' );
        } else {
            for ($i = 0; $i < $week_count; $i++ ) {
                $dates[] = date( 'Y-m-d', strtotime( "+$i week" ) );
            }
        }

        return $dates;
    }

    /**
     * Get lunch menu posts.
     *
     * @return array
     */
    public function get_lunch_posts() {
        $args = [
            'post_type'          => PostType\LunchMenu::SLUG,
            'orderby'            => 'post_date',
            'posts_per_page' => 999, // phpcs:ignore
        ];

        $query = new \WP_Query( $args );

        if ( empty( $query->posts ) ) {
            return [];
        }

        return $query->posts;
    }

    /**
     * Format lunch menu items.
     *
     * @param array $items lunch menu items.
     *
     * @return array
     */
    public static function format_lunch_menu_items( array $items ) : array {

        return array_map( function ( $item ) {
                return [
                    'title'       => $item->post_title,
                    'start'       => date( 'j.n.', strtotime( get_field( 'start_datetime', $item->ID ) ) ),
                    'end'         => date( 'j.n.Y', strtotime( get_field( 'end_datetime', $item->ID ) ) ),
                    'description' => get_field( 'description', $item->ID ),
                    'days'        => self::format_lunch_menu( get_field( 'days', $item->ID ) ?: [] ),
                ];
        }, $items );

    }

    /**
     * Format days.
     *
     * @param array $days Lunch menu days.
     *
     * @return array
     */
    public static function format_lunch_menu( array $days ) : array {

        if ( empty( $days ) ) {
            return [];
        }

        return array_map( function ( $day ) use ( $days ) {

                $locale = \get_locale();
                $date_formatter = new IntlDateFormatter( $locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE );
                $date_formatter->setPattern( 'EEEE d.M.' );

                // Get the current date as a string with the day of the week
                $day_of_week = $date_formatter->format( new \DateTime( $day['days'] ) );

                $is_last = end( $days ) === $day;

                return [
                    'day'         => $day_of_week,
                    'foods'       => $day['foods'],
                    'description' => $day['food_description'],
                    'link'        => $day['link'],
                    'is_last'     => $is_last,
                ];
        }, $days );

    }

    /**
     * View's flexible layouts
     *
     * @return array
     */
    public function components() : array {
        $content = get_field( 'components' ) ?? [];

        if ( empty( $content ) || ! is_array( $content ) ) {
            return [];
        }

        return $this->handle_layouts( $content );
    }

    /**
     * Format layout data
     *
     * @param array $fields Array of Layout fields.
     *
     * @return array
     */
    protected function handle_layouts( array $fields ) : array {
        $handled = [];

        if ( empty( $fields ) ) {
            return $handled;
        }

        foreach ( $fields as $layout ) {
            if ( empty( $layout['acf_fc_layout'] ) ) {
                continue;
            }

            $acf_layout        = $layout['acf_fc_layout'];
            $layout_name       = str_replace( '_', '-', $acf_layout );
            $layout['partial'] = 'layout-' . $layout_name . '.dust';

            $handled[] = apply_filters(
                "tms/acf/layout/{$acf_layout}/data",
                $layout
            );
        }

        return $handled;
    }
}
