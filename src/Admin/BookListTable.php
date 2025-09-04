<?php

namespace WPBooks\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use WPBooks\Database;

/**
 * Class BookListTable
 *
 * Displays books in WP admin using WP_List_Table.
 *
 * @package WPBooks\Admin
 */
class BookListTable extends \WP_List_Table {

    /**
     * Database instance.
     *
     * @var Database
     */
    private $db;

    /**
     * Constructor.
     *
     * Initializes WP_List_Table with custom args and sets up database instance.
     */
    public function __construct() {
        parent::__construct([
            'singular' => 'book',
            'plural'   => 'books',
            'ajax'     => false
        ]);
        $this->db = Database::get_instance();
    }

    /**
     * Prepares items for display in the table.
     *
     * Handles pagination and fetches items from database.
     *
     * @return void
     */
    public function prepare_items() {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $result = $this->db->get_books($current_page, $per_page);

        $total_items = $result['total'];
        $this->items = $result['items'];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);
    }

    /**
     * Defines the columns displayed in the table.
     *
     * @return array
     */
    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'title' => 'Title',
            'author' => 'Author',
            'published_year' => 'Published Year',
            'actions' => 'Actions'
        ];
    }

    /**
     * Renders default column output.
     *
     * @param object $item        Current row item.
     * @param string $column_name Column name.
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'title':
            case 'author':
            case 'published_year':
                return esc_html( $item->{$column_name} );
            case 'actions':
                $edit_url = admin_url('admin.php?page=wpbooks-edit&book_id=' . intval($item->id));
                return '<a href="' . esc_url(admin_url('admin.php?page=wpbooks-add&book_id=' . intval($item->id))) . '">Edit</a>';
            default:
                return print_r( $item, true );
        }
    }

    /**
     * Renders the checkbox column for bulk actions.
     *
     * @param object $item Current row item.
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="book_ids[]" value="%d" />', $item->id
        );
    }

    /**
     * Defines available bulk actions for the table.
     *
     * @return array
     */
    public function get_bulk_actions() {
        return [
            'bulk-delete' => 'Delete'
        ];
    }

    /**
     * Defines sortable columns for the table.
     *
     * @return array
     */
    public function get_sortable_columns() {
        return [
            'title' => ['title', false],
            'published_year' => ['published_year', false]
        ];
    }

    /**
     * Renders the "title" column with row actions.
     *
     * @param object $item Current row item.
     *
     * @return string
     */
    public function column_title( $item ) {
        $edit_link = admin_url( 'admin.php?page=wpbooks-add&book_id=' . intval($item->id) );
        $actions = [
            'edit' => sprintf( '<a href="%s">Edit</a>', esc_url( $edit_link ) )
        ];
        return sprintf( '<strong>%s</strong> %s', esc_html( $item->title ), $this->row_actions( $actions ) );
    }

    /**
     * Renders the whole table (overrides WP_List_Table default display).
     *
     * @return void
     */
    public function display() {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <?php $this->print_table_description(); ?>
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr>
                <?php $this->print_column_headers( false ); ?>
            </tr>
            </tfoot>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
}