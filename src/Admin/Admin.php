<?php

namespace WPBooks\Admin;

use WPBooks\Database;

/**
 * Class Admin
 *
 * Registers admin menu pages and handles admin actions.
 *
 * @package WPBooks\Admin
 */
class Admin {

    /**
     * Singleton instance.
     *
     * @var Admin|null
     */
    private static ?Admin $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Admin
     */
    public static function get_instance(): Admin {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton.
     */
    private function __construct() {}

    /**
     * Register WordPress hooks for admin menus and actions.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_post_wpbooks_add', [ $this, 'handle_add' ] );
        add_action( 'admin_post_wpbooks_edit', [ $this, 'handle_edit' ] );
        add_action( 'admin_post_wpbooks_bulk_delete', [ $this, 'handle_bulk_delete' ] );
    }

    /**
     * Add admin menu and submenu pages for Books.
     *
     * @return void
     */
    public function add_menu(): void {
        $cap = 'manage_options';
        $slug = 'wpbooks';
        add_menu_page( 'Books', 'Books', $cap, $slug, [ $this, 'render_list_page' ], 'dashicons-book', 26 );
        add_submenu_page( $slug, 'All Books', 'All Books', $cap, $slug, [ $this, 'render_list_page' ] );
        add_submenu_page( $slug, 'Add New', 'Add New', $cap, 'wpbooks-add', [ $this, 'render_add_page' ] );
    }

    /**
     * Render the list page view for all books.
     *
     * @return void
     */
    public function render_list_page(): void {
        require_once plugin_dir_path( __DIR__ ) . '../views/admin-list.php';
    }

    /**
     * Render the add book page view.
     *
     * @return void
     */
    public function render_add_page(): void {
        require_once plugin_dir_path( __DIR__ ) . '../views/admin-add.php';
    }

    /**
     * Handle the submission of the "Add Book" form.
     * Validates nonce and user capability before inserting book data.
     *
     * @return void
     */
    public function handle_add(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'wpbooks_admin_add' ) ) {
            wp_die( 'Invalid nonce' );
        }
        $title  = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
        $author = isset( $_POST['author'] ) ? sanitize_text_field( $_POST['author'] ) : '';
        $year   = isset( $_POST['published_year'] ) ? intval( $_POST['published_year'] ) : 0;

        $db = Database::get_instance();
        $db->insert_book( $title, $author, $year );

        wp_redirect( admin_url( 'admin.php?page=wpbooks&added=1' ) );
        exit;
    }

    /**
     * Handle the submission of the "Edit Book" form.
     * Validates nonce and user capability before updating book data.
     *
     * @return void
     */
    public function handle_edit(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'wpbooks_admin_edit' ) ) {
            wp_die( 'Invalid nonce' );
        }
        $id     = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $title  = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
        $author = isset( $_POST['author'] ) ? sanitize_text_field( $_POST['author'] ) : '';
        $year   = isset( $_POST['published_year'] ) ? intval( $_POST['published_year'] ) : 0;

        $db = Database::get_instance();
        $db->update_book( $id, $title, $author, $year );

        wp_redirect( admin_url( 'admin.php?page=wpbooks&updated=1' ) );
        exit;
    }

    /**
     * Handle bulk delete action from the list table.
     * Validates nonce and user capability before deleting selected books.
     *
     * @return void
     */
    public function handle_bulk_delete(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'bulk-books' ) ) {
            wp_die( 'Invalid nonce' );
        }
        $ids = isset( $_POST['book_ids'] ) ? (array) $_POST['book_ids'] : [];
        $db  = Database::get_instance();
        $db->delete_books( $ids );

        wp_redirect( admin_url( 'admin.php?page=wpbooks&deleted=1' ) );
        exit;
    }
}