<?php
use WPBooks\Admin\BookListTable;
use WPBooks\Admin\Admin;

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized' );
}

$book_table = new BookListTable();
$book_table->prepare_items();

$message = '';
if ( isset($_GET['added']) ) $message = 'Book added.';
if ( isset($_GET['updated']) ) $message = 'Book updated.';
if ( isset($_GET['deleted']) ) $message = 'Book(s) deleted.';
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Books</h1>
    <?php if ( $message ): ?>
        <div id="message" class="updated notice is-dismissible"><p><?php echo esc_html($message); ?></p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

        <?php $book_table->display(); ?>
        <p>
            <input type="submit" class="button button-primary" value="Delete Selected">
        </p>
    </form>
</div>
