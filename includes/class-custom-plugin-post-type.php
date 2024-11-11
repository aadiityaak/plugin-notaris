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
    function register_post_types()
    {
        // Post Type: Data Pelanggan
        $labels_data_pelanggan = array(
            'name'               => 'Data Pelanggan',
            'singular_name'      => 'Data Pelanggan',
            'menu_name'          => 'Data Pelanggan',
            'name_admin_bar'     => 'Data Pelanggan',
            'add_new'            => 'Tambah Baru',
            'add_new_item'       => 'Tambah Data Pelanggan Baru',
            'new_item'           => 'Data Pelanggan Baru',
            'edit_item'          => 'Edit Data Pelanggan',
            'view_item'          => 'Lihat Data Pelanggan',
            'all_items'          => 'Semua Data Pelanggan',
            'search_items'       => 'Cari Data Pelanggan',
            'not_found'          => 'Tidak ada Data Pelanggan ditemukan',
            'not_found_in_trash' => 'Tidak ada Data Pelanggan ditemukan di tempat sampah',
        );

        $args_data_pelanggan = array(
            'labels'             => $labels_data_pelanggan,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 6,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_icon'          => 'dashicons-businessman',
            'supports'           => array('title', 'editor', 'custom-fields'),
        );

        register_post_type('data_pelanggan', $args_data_pelanggan);

        // Post Type: Proses Kerja
        $labels_proses_kerja = array(
            'name'               => 'Proses Kerja',
            'singular_name'      => 'Proses Kerja',
            'menu_name'          => 'Proses Kerja',
            'name_admin_bar'     => 'Proses Kerja',
            'add_new'            => 'Tambah Baru',
            'add_new_item'       => 'Tambah Proses Kerja Baru',
            'new_item'           => 'Proses Kerja Baru',
            'edit_item'          => 'Edit Proses Kerja',
            'view_item'          => 'Lihat Proses Kerja',
            'all_items'          => 'Semua Proses Kerja',
            'search_items'       => 'Cari Proses Kerja',
            'not_found'          => 'Tidak ada Proses Kerja ditemukan',
            'not_found_in_trash' => 'Tidak ada Proses Kerja ditemukan di tempat sampah',
        );

        $args_proses_kerja = array(
            'labels'             => $labels_proses_kerja,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 5,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => array('title', 'editor', 'custom-fields'),
        );

        register_post_type('draft_kerja', $args_proses_kerja);

        // Post Type: Job Desk
        $labels_job_desk = array(
            'name'               => 'Job Desk',
            'singular_name'      => 'Job Desk',
            'menu_name'          => 'Job Desk',
            'name_admin_bar'     => 'Job Desk',
            'add_new'            => 'Tambah Baru',
            'add_new_item'       => 'Tambah Job Desk Baru',
            'new_item'           => 'Job Desk Baru',
            'edit_item'          => 'Edit Job Desk',
            'view_item'          => 'Lihat Job Desk',
            'all_items'          => 'Semua Job Desk',
            'search_items'       => 'Cari Job Desk',
            'not_found'          => 'Tidak ada Job Desk ditemukan',
            'not_found_in_trash' => 'Tidak ada Job Desk ditemukan di tempat sampah',
        );

        $args_job_desk = array(
            'labels'             => $labels_job_desk,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 7,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => array('title', 'editor', 'custom-fields'),
        );

        register_post_type('job_desk', $args_job_desk);
    }
}
// membuat taxonomy kategori job desk untuk post type job_desk
function create_taxonomy_kategori_job_desk()
{
    register_taxonomy(
        'kategori_job_desk',
        'job_desk',
        array(
            'label' => __('Kategori Job Desk'),
            'rewrite' => array('slug' => 'kategori-job-desk'),
            'hierarchical' => true,
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'create_taxonomy_kategori_job_desk');

// Inisialisasi class Custom_Post_Types_Register
$custom_post_types_register = new Custom_Plugin_Post_Types();
