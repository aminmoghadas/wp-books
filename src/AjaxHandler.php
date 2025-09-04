<?php

namespace WPBooks;

/**
 * Class AjaxHandler
 *
 * Registers AJAX endpoints and handles AJAX requests.
 *
 * @package WPBooks
 */
class AjaxHandler {
    /**
     * Singleton instance.
     *
     * @var AjaxHandler|null
     */
    private static ?AjaxHandler $instance = null;

    /**
     * Get singleton instance.
     *
     * @return AjaxHandler
     */
    public static function get_instance(): AjaxHandler {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register AJAX actions.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'wp_ajax_wpbooks_add_book', [ $this, 'handle_add_book' ] );
        add_action( 'wp_ajax_wpbooks_get_books', [ $this, 'handle_get_books' ] );
        add_action( 'wp_ajax_nopriv_wpbooks_add_book', [ $this, 'handle_add_book' ] );
        add_action( 'wp_ajax_nopriv_wpbooks_get_books', [ $this, 'handle_get_books' ] );
    }

    /**
     * Add book via AJAX.
     * Expects: title, author, published_year, nonce
     *
     * @return void
     */
    public function handle_add_book(): void {
        header( 'Content-Type: application/json; charset=utf-8' );

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'wpbooks_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Invalid nonce' ], 401 );
        }

        $title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $author = isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : '';
        $year = isset( $_POST['published_year'] ) ? intval( wp_unslash( $_POST['published_year'] ) ) : 0;

        // Basic validation
        if ( $title === '' || $author === '' || $year <= 0 || $year > 9999 ) {
            wp_send_json_error( [ 'message' => 'Invalid input' ], 422 );
        }

        $db = Database::get_instance();
        $insert_id = $db->insert_book( $title, $author, $year );

        if ( $insert_id === false ) {
            wp_send_json_error( [ 'message' => 'DB insert failed' ], 500 );
        }

        wp_send_json_success( [
            'message' => 'Book added',
            'book'    => [
                'id'             => $insert_id,
                'title'          => esc_html( $title ),
                'author'         => esc_html( $author ),
                'published_year' => intval( $year ),
            ],
        ] );
    }

    /**
     * Get books via AJAX with pagination support.
     * Optional params: page (int), per_page (int)
     *
     * @return void
     */
    public function handle_get_books(): void {
        header( 'Content-Type: application/json; charset=utf-8' );

        $page = isset( $_REQUEST['page'] ) ? max(1, intval( wp_unslash( $_REQUEST['page'] ) )) : 1;
        $per_page = isset( $_REQUEST['per_page'] ) ? max(1, intval( wp_unslash( $_REQUEST['per_page'] ) )) : 10;

        $db = Database::get_instance();
        $result = $db->get_books( $page, $per_page );

        $total = isset($result['total']) ? intval($result['total']) : 0;
        $items = isset($result['items']) ? $result['items'] : [];

        $total_pages = (int) ceil( $total / $per_page );

        $out = [];
        foreach ( $items as $b ) {
            $out[] = [
                'id'             => intval( $b->id ),
                'title'          => esc_html( $b->title ),
                'author'         => esc_html( $b->author ),
                'published_year' => intval( $b->published_year ),
            ];
        }

        wp_send_json_success( [
            'books' => $out,
            'meta'  => [
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $total_pages,
            ]
        ] );
    }
}