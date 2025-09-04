<?php
use WPBooks\Database;

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized' );
}

$db = Database::get_instance();
$editing = false;
$book = null;
if ( isset($_GET['book_id']) ) {
    $editing = true;
    $book = $db->get_book( intval($_GET['book_id']) );
}

$action = $editing ? 'wpbooks_edit' : 'wpbooks_add';
$nonce_action = $editing ? 'wpbooks_admin_edit' : 'wpbooks_admin_add';
?>
<div class="wrap">
    <h1><?php echo $editing ? 'Edit Book' : 'Add New Book'; ?></h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field( $nonce_action ); ?>
        <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
        <?php if ( $editing ): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($book->id); ?>">
        <?php endif; ?>

        <table class="form-table" role="presentation">
            <tbody>
                <tr class="form-field">
                    <th scope="row"><label for="title">Title</label></th>
                    <td><input name="title" id="title" type="text" value="<?php echo $editing ? esc_attr($book->title) : ''; ?>" class="regular-text" required></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="author">Author</label></th>
                    <td><input name="author" id="author" type="text" value="<?php echo $editing ? esc_attr($book->author) : ''; ?>" class="regular-text" required></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="published_year">Published Year</label></th>
                    <td><input name="published_year" id="published_year" type="number" value="<?php echo $editing ? esc_attr($book->published_year) : ''; ?>" class="small-text" required></td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( $editing ? 'Update Book' : 'Add Book' ); ?>
    </form>
</div>
