<?php
/**
 * Obecní dům Holčovice – functions.php
 */

function odh_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption' ] );
    register_nav_menus( [ 'primary' => __( 'Hlavní navigace', 'odh' ) ] );
}
add_action( 'after_setup_theme', 'odh_setup' );

function odh_enqueue() {
    wp_enqueue_style( 'google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Nunito:wght@400;600;700&family=Raleway:wght@700&display=swap',
        [], null );
    wp_enqueue_style( 'odh-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [ 'google-fonts' ], '1.0' );
    wp_enqueue_script( 'odh-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [], '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'odh_enqueue' );

function odh_img( $file ) {
    return get_template_directory_uri() . '/img/' . $file;
}
