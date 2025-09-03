<?php

namespace WPBooks;

/**
 * Class Shortcode
 *
 * Handles the [book_list] shortcode output.
 *
 * @package WPBooks
 */
class Shortcode {
    /**
     * Singleton instance.
     *
     * @var Shortcode|null
     */
    private static ?Shortcode $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Shortcode
     */
    public static function get_instance(): Shortcode {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register shortcode.
     *
     * @return void
     */
    public function register(): void {
        add_shortcode( 'book_list', [ $this, 'render' ] );
    }

    /**
     * Render the shortcode content.
     *
     * The shortcode prints:
     * - a form (AJAX)
     * - an empty table container that JS will populate by fetching the books via AJAX
     *
     * @param array $atts
     * @param string|null $content
     *
     * @return string
     */
    public function render( array $atts = [], ?string $content = null ): string {
        ob_start();
        ?>
        <div id="wp-books-app">
            <form id="wp-books-form" class="wp-books-form" method="post" novalidate>
                <div>
                    <label>Title<br>
                        <input type="text" name="title" id="wp-books-title" required maxlength="255">
                    </label>
                </div>
                <div>
                    <label>Author<br>
                        <input type="text" name="author" id="wp-books-author" required maxlength="255">
                    </label>
                </div>
                <div>
                    <label>Published Year<br>
                        <input type="number" name="published_year" id="wp-books-year" required min="1" max="9999">
                    </label>
                </div>
                <div>
                    <button type="submit" id="wp-books-submit">Add Book</button>
                </div>
                <div id="wp-books-message" role="status" aria-live="polite"></div>
            </form>

            <hr>

            <div id="wp-books-list">
                <table id="wp-books-table" border="1" cellpadding="5" cellspacing="0" style="width:100%;border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th>Title</th><th>Author</th><th>Published Year</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- JS will populate -->
                    </tbody>
                </table>

                <div id="wp-books-pagination">
                    <button id="wp-books-prev" disabled>Prev</button>
                    <span id="wp-books-pageinfo"></span>
                    <button id="wp-books-next" disabled>Next</button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}