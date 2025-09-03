<?php

namespace WPBooks;

use wpdb;

/**
 * Class Database
 *
 * Responsible for creating the DB table and CRUD operations for books.
 *
 * @package WPBooks
 */
class Database
{
    /**
     * Singleton instance.
     *
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * WPDB instance.
     *
     * @var wpdb
     */
    private wpdb $wpdb;

    /**
     * Full table name (with prefix).
     *
     * @var string
     */
    private string $table;

    /**
     * Get singleton instance.
     *
     * @return Database
     */
    public static function get_instance(): Database {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Database constructor.
     */
    private function __construct() {
        global $wpdb;
        $this->wpdb  = $wpdb;
        $this->table = $this->wpdb->prefix . 'books';
    }

    /**
     * Create the table if not exists (uses dbDelta).
     *
     * @return void
     */
    public function maybe_create_table(): void {
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            author varchar(255) NOT NULL,
            published_year int(4) NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}