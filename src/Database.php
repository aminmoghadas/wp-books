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

    /**
     * Insert a new book.
     *
     * @param string $title
     * @param string $author
     * @param int    $year
     *
     * @return int|false Insert ID on success, false on failure.
     */
    public function insert_book( string $title, string $author, int $year ) {
        $title  = sanitize_text_field( $title );
        $author = sanitize_text_field( $author );
        $year   = absint( $year );

        $res = $this->wpdb->insert(
            $this->table,
            [
                'title'          => $title,
                'author'         => $author,
                'published_year' => $year,
            ],
            [ '%s', '%s', '%d' ]
        );

        if ( $res === false ) {
            return false;
        }

        return (int) $this->wpdb->insert_id;
    }

    /**
     * Update a book by id.
     *
     * @param int $id
     * @param string $title
     * @param string $author
     * @param int $year
     * @return bool
     */
    public function update_book( int $id, string $title, string $author, int $year ): bool {
        $id = absint( $id );
        $title  = sanitize_text_field( $title );
        $author = sanitize_text_field( $author );
        $year   = absint( $year );

        $res = $this->wpdb->update(
            $this->table,
            [
                'title' => $title,
                'author' => $author,
                'published_year' => $year,
            ],
            ['id' => $id],
            ['%s','%s','%d'],
            ['%d']
        );

        return $res !== false;
    }

    /**
     * Delete books by ids.
     *
     * @param array $ids
     * @return int|false Number of rows deleted or false on failure.
     */
    public function delete_books( array $ids ) {
        $ids = array_map('intval', $ids);
        if ( empty($ids) ) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = "DELETE FROM {$this->table} WHERE id IN ({$placeholders})";
        $result = $this->wpdb->query( $this->wpdb->prepare( $query, $ids ) );
        return $result;
    }

    /**
     * Get books (with pagination support).
     *
     * @param int $page
     * @param int $per_page
     * @return array [ 'items' => array, 'total' => int ]
     */
    public function get_books( int $page = 1, int $per_page = 10 ): array {
        $page = max(1, $page);
        $per_page = max(1, $per_page);
        $offset = ($page - 1) * $per_page;

        $total = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );

        $sql = $this->wpdb->prepare( "SELECT id, title, author, published_year FROM {$this->table} ORDER BY id DESC LIMIT %d, %d", $offset, $per_page );
        $results = $this->wpdb->get_results( $sql );
        return [
            'items' => is_array($results) ? $results : [],
            'total' => $total
        ];
    }

    /**
     * Get single book by id.
     *
     * @param int $id
     * @return object|null
     */
    public function get_book( int $id ) {
        $id = absint($id);
        $row = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT id, title, author, published_year FROM {$this->table} WHERE id = %d", $id ) );
        return $row ?: null;
    }
}