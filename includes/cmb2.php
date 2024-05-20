<?php

add_action( 'cmb2_init', 'pekerjaan_metabox_metabox' );
function pekerjaan_metabox_metabox() {
    $user_id = $_GET['user_id'] ?? '';
    $prefix = '';
    // Buat metabox
    $cmb_group = new_cmb2_box( array(
        'id'            => 'pekerjaan_metabox',
        'title'         => esc_html__( 'Pekerjaan Details', 'text-domain' ),
        'object_types'  => array( 'draft_kerja' ), // Ganti 'post' dengan jenis postingan yang Anda inginkan
    ) );

    // Field Pilihan Pelanggan
    $cmb_group->add_field(array(
        'name'             => 'Pilih Konsumen',
        'id'               => $prefix . 'customer_select',
        'type'             => 'select',
        'options_cb'       => 'get_customer_posts',
        'default'          => $user_id
    ));

    $cmb_group->add_field(array(
        'name'             => 'Tanggal Order',
        'id'               => $prefix . 'tanggal_order',
        'type'             => 'text_date',
    ));
    $cmb_group->add_field(array(
        'name'             => 'Layanan',
        'id'               => $prefix . 'layanan',
        'type'             => 'text',
    ));

    $cmb_group->add_field( array(
        'name' => 'Sertipikat Asli',
        'desc' => 'konsumen menyertakan sertipikat asli.',
        'id'   => 'sertipikat_asli',
        'type' => 'checkbox',
    ) );
    $cmb_group->add_field( array(
        'name' => 'PBB',
        'desc' => 'konsumen menyertakan PBB.',
        'id'   => 'pbb',
        'type' => 'checkbox',
    ) );
    $cmb_group->add_field( array(
        'name' => 'KTP',
        'desc' => 'konsumen menyertakan KTP.',
        'id'   => 'ktp',
        'type' => 'checkbox',
    ) );
    $cmb_group->add_field( array(
        'name' => 'KK',
        'desc' => 'konsumen menyertakan KK.',
        'id'   => 'kk',
        'type' => 'checkbox',
    ) );
}

function get_users_options() {
    $users = get_users( array( 'fields' => array( 'ID', 'user_login' ) ) );
    $user_options = array();

    // tampilkan semua user jika administrator
    if ( current_user_can( 'administrator' ) ) {
        foreach ( $users as $user ) {
            $user_options[ $user->ID ] = $user->user_login;
        }
    } else {
        $user_options[ get_current_user_id() ] = get_userdata( get_current_user_id() )->user_login;
    }



    return $user_options;
}


add_action( 'cmb2_admin_init', 'register_user_profile_metabox' );
function register_user_profile_metabox() {

	/**
	 * Metabox for the user profile screen
	 */
	$cmb_user = new_cmb2_box( array(
		'id'               => 'user_edit',
		'title'            => esc_html__( 'User Profile Metabox', 'cmb2' ), // Doesn't output for user boxes
		'object_types'     => array( 'user' ), // Tells CMB2 to use user_meta vs post_meta
		'show_names'       => true,
		'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
	) );

	$cmb_user->add_field( array(
		'name' => esc_html__( 'Jabatan', 'cmb2' ),
		'desc' => esc_html__( '', 'cmb2' ),
		'id'   => 'jabatan',
		'type'    => 'select',
		'options' => array(
			'Notaris' => esc_html__( 'Notaris', 'cmb2' ),
			'PPAT (Pejabat Pembuat Akta Tanah)' => esc_html__( 'PPAT (Pejabat Pembuat Akta Tanah)', 'cmb2' ),
			'Staff Notaris' => esc_html__( 'Staff Notaris', 'cmb2' ),
            'Pegawai Administrasi' => esc_html__( 'Pegawai Administrasi', 'cmb2' ),
            'Staff Keuangan' => esc_html__( 'Staff Keuangan', 'cmb2' ),
            'Pengacara atau Legal Consultant' => esc_html__( 'Pengacara atau Legal Consultant', 'cmb2' ),
		),
	) );
    $cmb_user->add_field( array(
		'name' => esc_html__( 'Status', 'cmb2' ),
		'desc' => esc_html__( '', 'cmb2' ),
		'id'   => 'status',
		'type'    => 'select',
		'options' => array(
			'' => esc_html__( '-', 'cmb2' ),
			'Aktif' => esc_html__( 'Aktif', 'cmb2' ),
			'Non Aktif' => esc_html__( 'Non Aktif', 'cmb2' ),
		),
	) );

}

// Pastikan bahwa CMB2 sudah diinstal dan diaktifkan di situs Anda

// Fungsi untuk menambahkan meta box pada post type 'draft_kerja'
function add_customer_data_metabox() {
    $prefix = '_customer_data_';

    $cmb = new_cmb2_box(array(
        'id'           => 'customer_data_metabox',
        'title'        => __('Data Konsumen', 'textdomain'),
        'object_types' => array('data_pelanggan'), // Post type yang diinginkan
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    // Field Nama Lengkap
    $cmb->add_field(array(
        'name' => 'Nama Lengkap',
        'id'   => $prefix . 'nama_lengkap',
        'type' => 'text',
    ));

    // Field Alamat
    $cmb->add_field(array(
        'name' => 'Alamat',
        'id'   => $prefix . 'alamat',
        'type' => 'textarea_small',
    ));

    // Field WhatsApp
    $cmb->add_field(array(
        'name' => 'WhatsApp',
        'id'   => $prefix . 'whatsapp',
        'type' => 'text',
    ));
    
    $cmb->add_field( array(
        'name'     => 'Pilih Kategori',
        'id'       => 'kategori_konsumen',
        'desc'     => '',
        'type'     => 'taxonomy_select',
        'taxonomy' => 'konsumen_kategori',
        // 'apply_term' => false, // If set to false, saves the term to meta instead of setting term on the object.
        // 'attributes' => array(
        // 	'data-min-length' => 2, // Override minimum length
        // 	'data-delay'      => 100, // Override delay
        // ),
    ) );
    // $cmb->add_field( array(
    //     'name' => 'File Pendukung',
    //     'desc' => '',
    //     'id'   => 'file',
    //     'type' => 'file_list',
    //     // 'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
    //     // 'query_args' => array( 'type' => 'image' ), // Only images attachment
    //     // Optional, override default text strings
    //     'text' => array(
    //         'add_upload_files_text' => 'Tambah / Upload File', // default: "Add or Upload Files"
    //         'remove_image_text' => 'Hapus', // default: "Remove Image"
    //         'file_text' => 'File', // default: "File:"
    //         'file_download_text' => 'Download', // default: "Download"
    //         'remove_text' => 'Hapus', // default: "Remove"
    //     ),
    // ) );
}

add_action('cmb2_init', 'add_customer_data_metabox');

// METABOX JOB DESK
add_action( 'cmb2_init', 'job_desk_metabox_metabox' );
function job_desk_metabox_metabox() {
    $user_id = $_GET['user_id'] ?? '';
    $prefix = '';
    // Buat metabox
    $cmb_group = new_cmb2_box( array(
        'id'            => 'job_desk_metabox',
        'title'         => esc_html__( 'Pekerjaan Details', 'text-domain' ),
        'object_types'  => array( 'job_desk' ), // Ganti 'post' dengan jenis postingan yang Anda inginkan
    ) );

    $cmb_group->add_field( array(
        'name' => esc_html__( 'Judul Job Desk', 'text-domain' ),
        'id'   => 'judul_job_desk',
        'type' => 'text',
    ) );

    $kode_layanan = $_GET['kode-layanan'] ?? '';
    $draft_id = bl_get_post_id_by_title( $kode_layanan );
    $cmb_group->add_field(array(
        'name'             => 'Draft Kerja',
        'id'               => 'job_desk_draft_kerja',
        'type'             => 'text',
        'default'          => $draft_id,
    ));

    $current_user_id = get_current_user_id();
    $cmb_group->add_field(array(
        'name'             => 'Staff',
        'id'               => 'job_desk_id_staff',
        'type' => 'select',
        'options_cb' => 'get_users_options', // Gunakan callback untuk mengisi opsi
        'default'          => $current_user_id,
    ));

    // Field Pilihan Pelanggan

    $cmb_group->add_field( array(
        'name'    => esc_html__( 'Kategori', 'text-domain' ),
        'id'      => 'job_desk_kategori_select',
        'type'    => 'select',
        'options' => array(
            'Notaris' => esc_html__( 'Notaris', 'text-domain' ),
            'PPAT' => esc_html__( 'PPAT', 'text-domain' ),
            'Lainya' => esc_html__( 'Lainya', 'text-domain' ),
            // Tambahkan opsi lain sesuai kebutuhan Anda
        ),
        
    ) );

    // $cmb_group->add_field( array(
    //     'name' => esc_html__( 'Pilih Staff', 'text-domain' ),
    //     'id'   => 'user_list',
    //     'type' => 'select',
    //     'options_cb' => 'get_users_options', // Gunakan callback untuk mengisi opsi
    // ) );

    $cmb_group->add_field( array(
        'name' => esc_html__( 'Tanggal Mulai', 'text-domain' ),
        'id'   => 'job_desk_start',
        'type' => 'text_date',
    ) );

    $cmb_group->add_field( array(
        'name' => esc_html__( 'Tanggal Selesai', 'text-domain' ),
        'id'   => 'job_desk_end',
        'type' => 'text_date',
    ) );

    $cmb_group->add_field( array(
        'name'    => esc_html__( 'Status', 'text-domain' ),
        'id'      => 'job_desk_status',
        'type'    => 'select',
        'options' => array(
            '-' => esc_html__( '-', 'text-domain' ),
            'Pengerjaan' => esc_html__( 'Pengerjaan', 'text-domain' ),
            'Selesai' => esc_html__( 'Selesai', 'text-domain' ),
        ),
    ) );
}
