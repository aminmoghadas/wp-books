<?php
/**
 * Plugin Name: WP Books
 * Plugin URI:  https://keywp.ir
 * Description: WP Books plugin — PSR-4, Composer autoload, DB manager, Shortcode, AJAX handler and Admin UI.
 * Version:     1.1.0
 * Author:      Amin Moghadas
 * Text Domain: wp-books
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$autoload_path = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $autoload_path ) ) {
    require_once $autoload_path;
} else {
    require_once __DIR__ . '/src/Plugin.php';
    require_once __DIR__ . '/src/Database.php';
    require_once __DIR__ . '/src/Shortcode.php';
    require_once __DIR__ . '/src/AjaxHandler.php';
}

add_action( 'plugins_loaded', function() {
    WPBooks\Plugin::get_instance();
} );

