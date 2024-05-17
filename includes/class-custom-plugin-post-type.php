<?php
/**
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Custom_Plugin
 * @subpackage Custom_Plugin/includes
 */

class Custom_Plugin_Post_Types
{
    public function __construct()
    {
        // Hook into the 'init' action
        add_action('init', array($this, 'register_post_types'));
    }

    /**
     * Register custom post types
     */
    public function register_post_types()
    {
        // Register Blog Post Type
        register_post_type('draft_kerja',
            array(
                'labels' => array(
                    'name' => __('Draft Kerja'),
                    'singular_name' => __('Draft Kerja'),
                ),
                'public' => true,
                'has_archive' => true,
                'supports' => array('title', 'author'),
            )
        );

        register_post_type('data_pelanggan',
            array(
                'labels' => array(
                    'name' => __('Data Pelanggan'),
                    'singular_name' => __('Data Pelanggan'),
                ),
                'public' => true,
                'has_archive' => true,
                'supports' => array('title'),
            )
        );

        register_post_type('job_desk',
            array(
                'labels' => array(
                    'name' => __('Job Desk'),
                    'singular_name' => __('Job Desk'),
                    'add_new' => 'Tambah Job Desk',
                    'add_new_item' => 'Tambah Job Desk', 'textdomain',
                ),
                'public' => true,
                'has_archive' => false,
                'supports' => array('title', 'author'),
            )
        );
    }
}

// membuat taxonomy kategori job desk untuk post type job_desk
function create_taxonomy_kategori_job_desk() {
    register_taxonomy(
        'kategori_job_desk',
        'job_desk',
        array(
            'label' => __( 'Kategori Job Desk' ),
            'rewrite' => array( 'slug' => 'kategori-job-desk' ),
            'hierarchical' => true,
            'show_in_rest' => true,
        )
    );
}
add_action( 'init', 'create_taxonomy_kategori_job_desk' );

// Inisialisasi class Custom_Post_Types_Register
$custom_post_types_register = new Custom_Plugin_Post_Types();