<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://websweetstudio.com
 * @since             1.0.0
 * @package           Custom_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Plugin Notaris
 * Plugin URI:        https://websweetstudio.com
 * Description:       Plugin untuk web custom.
 * Version:           1.0.0
 * Author:            Websweet Studio
 * Author URI:        https://websweetstudio.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Membuat tabel attachments di database WordPress
function create_attachment_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attachments';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        invoice varchar(255) NOT NULL,
        attachment_id mediumint(9) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
add_action( 'after_setup_theme', 'create_attachment_table' );

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CUSTOM_PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-custom-plugin-activator.php
 */
function activate_custom_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-plugin-activator.php';
	Custom_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-custom-plugin-deactivator.php
 */
function deactivate_custom_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-plugin-deactivator.php';
	Custom_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_custom_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_custom_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-custom-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_custom_plugin() {

	$plugin = new Custom_Plugin();
	$plugin->run();

}
run_custom_plugin();

// Fungsi untuk menampilkan daftar pengguna dengan tabel Bootstrap 5
function display_user_list() {
    // Memulai output buffering
    ob_start();
    if ( !(current_user_can( 'administrator' ) || current_user_can( 'editor' )) ) {
        return 'Silahkan login sebagai administrator untuk melihat data.';
    }
    // Query untuk mendapatkan daftar pengguna
    $users = get_users();
    $jabatan_options = array(
        'notaris' => 'Notaris',
        'ppat' => 'PPAT (Pejabat Pembuat Akta Tanah)',
        'staff_notaris' => 'Staff Notaris',
        'pegawai_administrasi' => 'Pegawai Administrasi',
        'staff_keuangan' => 'Staff Keuangan',
        'pengacara' => 'Pengacara atau Legal Consultant'
    );
    // Mengecek apakah ada pengguna yang ditemukan
    if (empty($users)) {
        echo 'Tidak ada pengguna yang ditemukan.';
    } else {
        // Memulai pembentukan tabel
        echo '<div class="table-responsive">';
        echo '<a class="btn btn-success btn-sm text-white mb-2" href="'.get_site_url().'/kelola-user/">Tambah User</a>';
        echo '<table class="table table-striped">';
        
        // Header tabel
        echo '<thead>';
        echo '<tr >';
        echo '<th class="bg-blue text-white">Nama</th>';
        echo '<th class="bg-blue text-white">Email</th>';
		echo '<th class="bg-blue text-white">Jabatan</th>';
		echo '<th class="bg-blue text-white">Status</th>';
		if (current_user_can('administrator')) {
			echo '<th class="bg-blue text-white text-end">Tindakan</th>';
		}
        echo '</tr>';
        echo '</thead>';
        
        // Body tabel
        echo '<tbody>';

        // Loop melalui setiap pengguna dan tambahkan ke output
        foreach ($users as $user) {
            $user_id = $user->id;
			$url_edit = get_site_url().'/kelola-user/?edit='.$user_id;
            $jabatan = $jabatan_options[get_user_meta($user_id, 'jabatan', true)] ?? '-';
            echo '<tr>';
				echo '<td>' . $user->display_name . '</td>';
				echo '<td>' . $user->user_email . '</td>';
				echo '<td>' . $jabatan . '</td>';
				echo '<td>' . get_user_meta($user_id, 'status', true) . '</td>';
				if (current_user_can('administrator')) {
					?>
                    <td class="text-end">
                        <a class="btn btn-dark btn-sm text-white" href="<?php echo $url_edit; ?>">Edit</a>
                        <button type="button" class="btn btn-danger btn-sm text-white" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?php echo $user_id; ?>">Hapus</button>
                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="confirmDeleteModal<?php echo $user_id; ?>" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus Pengguna</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus pengguna ini?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <a href="<?php echo ($user_id > 0) ? wp_nonce_url(admin_url('admin-post.php?action=delete_user&user_id=' . $user_id), 'delete_user_' . $user_id) : ''; ?>" class="btn btn-danger">Hapus</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <?php
				}
            echo '</tr>';
        }

        // Menutup tbody dan tabel
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    // Mengambil output buffering, membersihkan buffer, dan mengembalikan output
    $output = ob_get_clean();
    return $output;
}

// Mendaftarkan shortcode
add_shortcode('user_list', 'display_user_list');

// Fungsi untuk menangani penghapusan pengguna
function delete_user_action() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete_user' && isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);

        // Memeriksa apakah pengguna saat ini adalah administrator
        if (!current_user_can('administrator')) {
            wp_die('Maaf, Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        // Memeriksa apakah nonce valid
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_user_' . $user_id)) {
            wp_die('Permintaan tidak valid.');
        }

        // Hapus pengguna
        if (wp_delete_user($user_id)) {
            wp_redirect(get_site_url().'/data-staff/');
            exit;
        } else {
            wp_die('Gagal menghapus pengguna.');
        }
    }
}

// Menangani aksi penghapusan pengguna setelah admin_post
add_action('admin_post_delete_user', 'delete_user_action');

// Fungsi untuk menampilkan formulir tambah dan edit pengguna
function display_user_crud_form($atts) {
    ob_start();
    date_default_timezone_set('Asia/Jakarta');

    if (isset($_POST['submit'])) {
        $user_id = intval($_POST['user_id']);
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $status = sanitize_text_field($_POST['status']);
        $jabatan = sanitize_text_field($_POST['jabatan']);
        $catatan = sanitize_text_field($_POST['catatan']);
        $tanggal_daftar = $_POST['tanggal_daftar'] ?? date('Y-m-d');

        // Memeriksa apakah pengguna saat ini adalah administrator
        if (!current_user_can('administrator')) {
            return 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. <a class="btn btn-sm btn-dark text-white" href="'.get_site_url().'/kelola-user/">Coba Lagi</a>';
        }

        // Memeriksa apakah data yang diterima valid
        if (empty($username) || empty($email)) {
            return 'Harap isi semua kolom yang diperlukan. <a class="btn btn-sm btn-dark text-white" href="'.get_site_url().'/kelola-user/">Coba Lagi</a>';
        }

        if(empty($user_id)){
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // Memeriksa apakah password dan konfirmasi password sama
            if ($password !== $confirm_password) {
                return 'Password dan konfirmasi password tidak cocok. <a class="btn btn-sm btn-dark text-white" href="'.get_site_url().'/kelola-user/">Coba Lagi</a>';
            }

            // Memeriksa apakah password sudah diisi
            if (empty($password)) {
                return 'Harap isi password. <br><a class="btn btn-sm btn-dark text-white" href="'.get_site_url().'/kelola-user/">Coba Lagi</a>';
            }

            // Memeriksa apakah password memiliki panjang yang cukup
            if (strlen($password) < 8) {
                return 'Password harus terdiri dari minimal 8 karakter. <br><a class="btn btn-sm btn-dark text-white" href="'.get_site_url().'/kelola-user/">Coba Lagi</a>';
            }

            // Mengenkripsi password sebelum disimpan
            $hashed_password = wp_hash_password($password);
        }

        // Memeriksa apakah ini adalah operasi tambah atau edit
        if ($user_id > 0) {
            // Operasi edit
            $userdata = array(
                'ID' => $user_id,
                'user_login' => $username,
                'user_email' => $email,
            );

            // Mengupdate data pengguna
            $updated_user = wp_update_user($userdata);

            if (is_wp_error($updated_user)) {
                return 'Terjadi kesalahan saat memperbarui pengguna: ' . $updated_user->get_error_message();
            }

            // Memperbarui metadata status dan jabatan
            update_user_meta($user_id, 'status', $status);
            update_user_meta($user_id, 'jabatan', $jabatan);
            update_user_meta($user_id, 'catatan', $catatan);
            update_user_meta($user_id, 'tanggal_daftar', $tanggal_daftar);
            $user = new WP_User($user_id);

            return 'Pengguna berhasil diperbarui. <br><a class="btn btn-sm btn-dark text-white" href="'.$redirect.'">'.$redirect_text.'</a>';
        } else {
            // Operasi tambah
            $userdata = array(
                'user_login' => $username,
                'user_email' => $email,
                'user_pass' => $hashed_password, // Menggunakan password baru yang dienkripsi
            );

            // Membuat pengguna baru
            $new_user_id = wp_insert_user($userdata);

            if (is_wp_error($new_user_id)) {
                return 'Terjadi kesalahan saat menambahkan pengguna baru: ' . $new_user_id->get_error_message().'<br><a class="btn btn-sm btn-dark text-white" href="'.get_site_url().'/kelola-user/">Coba Lagi</a>';
            }

            // Menambah metadata status dan jabatan
            add_user_meta($new_user_id, 'status', $status);
            add_user_meta($new_user_id, 'jabatan', $jabatan);
            add_user_meta($new_user_id, 'catatan', $catatan);
            add_user_meta($new_user_id, 'tanggal_daftar', $tanggal_daftar);
            $new_user = new WP_User($new_user_id);
            $new_user->set_role($role);
            $user_login = $new_user->user_login;
            

            return '<center>Staff baru berhasil ditambahkan. <br/><a class="btn btn-sm btn-dark text-white" href="'.get_site_url().'/data-staff/?type=staff">List Staff</a></center>';
            
        }
    }
    // tutup handle

    // Mendapatkan data pengguna jika sedang diedit
    $user_id = intval($_GET['edit']);
    $user_data = ($user_id > 0) ? get_userdata($user_id) : null;

    // Mengecek apakah pengguna saat ini adalah administrator
    if (!current_user_can('administrator')) {
        return 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.';
    }

    // Memulai output buffering
    
    ?>
    <form method="post" action="">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <label for="username">Username:</label><br>
        <input class="form-control" type="text" id="username" name="username" value="<?php echo ($user_data) ? $user_data->user_login : ''; ?>"><br>
        <label for="email">Email:</label><br>
        <input class="form-control" type="email" id="email" name="email" value="<?php echo ($user_data) ? $user_data->user_email : ''; ?>"><br>
        
        <?php if(empty($user_id)){ ?>
            
            <label for="password">Password:</label><br>
            <input class="form-control" type="password" id="password" name="password" value=""><br>
            <label for="confirm_password">Konfirmasi Password:</label><br>
            <input class="form-control" type="password" id="confirm_password" name="confirm_password" value="">
            <br>
        <?php } ?>

        <?php if(isset($_GET['type']) && $_GET['type'] == 'pelanggan'){ ?>
            <label for="tanggal_daftar">Tanggal Daftar:</label><br>
            <input class="form-control" type="date" id="tanggal_daftar" name="tanggal_daftar" value="<?php echo ($user_data) ? $user_data->tanggal_daftar : date('Y-m-d'); ?>"><br>
            <input type="hidden" name="type" value="pelanggan">
        <?php } ?>

        <?php if(isset($_GET['type']) && $_GET['type'] == 'staff'){ ?>
            <input type="hidden" name="type" value="staff">
            <label for="status">Status:</label><br>
            <select class="form-control" id="status" name="status">
                <option value="active" <?php echo ($user_data && get_user_meta($user_id, 'status', true) == 'active') ? 'selected' : ''; ?>>Aktif</option>
                <option value="inactive" <?php echo ($user_data && get_user_meta($user_id, 'status', true) == 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
            </select><br>
            <label for="jabatan">Jabatan:</label><br>
            <select class="form-control" id="jabatan" name="jabatan">
                <option value="notaris" <?php echo ($user_data && get_user_meta($user_id, 'jabatan', true) == 'notaris') ? 'selected' : ''; ?>>Notaris</option>
                <option value="ppat" <?php echo ($user_data && get_user_meta($user_id, 'jabatan', true) == 'ppat') ? 'selected' : ''; ?>>PPAT (Pejabat Pembuat Akta Tanah)</option>
                <option value="staff_notaris" <?php echo ($user_data && get_user_meta($user_id, 'jabatan', true) == 'staff_notaris') ? 'selected' : ''; ?>>Staff Notaris</option>
                <option value="pegawai_administrasi" <?php echo ($user_data && get_user_meta($user_id, 'jabatan', true) == 'pegawai_administrasi') ? 'selected' : ''; ?>>Pegawai Administrasi</option>
                <option value="staff_keuangan" <?php echo ($user_data && get_user_meta($user_id, 'jabatan', true) == 'staff_keuangan') ? 'selected' : ''; ?>>Staff Keuangan</option>
                <option value="pengacara" <?php echo ($user_data && get_user_meta($user_id, 'jabatan', true) == 'pengacara') ? 'selected' : ''; ?>>Pengacara atau Legal Consultant</option>
            </select><br>
        <?php } ?>

        <label for="catatan">Catatan:</label><br>
        <textarea class="form-control" id="catatan" name="catatan"><?php echo ($user_data) ? $user_data->catatan : ''; ?></textarea><br>
        
        <input class="btn btn-dark text-white" type="submit" name="submit" value="Simpan">
        <a class="btn btn-dark text-white" href="<?php echo get_site_url(); ?>/data-staff/">Kembali ke List Staff</a>
    </form>
    <?php
    // Mengambil output buffering, membersihkan buffer, dan mengembalikan output
    $output = ob_get_clean();
    return $output;
}

// Mendaftarkan shortcode
add_shortcode('user_crud_form', 'display_user_crud_form');

function draft_kerja_shortcode() {
    ob_start();
    global $post;
$current_user = wp_get_current_user();

// Cetak peran pengguna yang saat ini masuk
// echo 'Peran pengguna saat ini: ' . implode( ', ', $current_user->roles );
    if ( !(current_user_can( 'administrator' ) || current_user_can( 'editor' )) ) {
        return 'Silahkan login sebagai administrator untuk melihat data.';
    }
    ?>
    <div class="table-responsive">
        <div class="mb-2 row mx-0">
            <div class="col-sm-6 px-0">
                <a class="btn btn-sm btn-success text-white" href="<?php echo get_site_url();?>/kelola-prosses-kerja/">Tambah Order</a>
            </div>
            <div class="col-sm-6 text-sm-end">
                <?php
                    $konsumen = $_GET['konsumen'] ?? '';
                    $get_kategori = $_GET['kategori'] ?? '';
                    if($konsumen) {
                        echo '<b>Filter:</b> Tampilkan data dari <b>'.get_post_meta($konsumen, '_customer_data_nama_lengkap', true).'</b>';
                        echo '<a href="?">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red" class="bi bi-x-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </a>';
                    } elseif ($get_kategori) {
                        // Mendapatkan nama kategori berdasarkan ID
                        $kategori = get_term_by('id', $get_kategori, 'konsumen_kategori');

                        if ($kategori && !is_wp_error($kategori)) {
                            echo '<b>Filter:</b> Tampilkan data dari kategori <b>' . esc_html($kategori->name) . '</b>';
                            echo '<a href="?">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red" class="bi bi-x-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                                </svg>
                            </a>';
                        } else {
                            echo '<b>Filter:</b> Kategori tidak ditemukan.';
                        }
                    }
                ?>
            </div>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="bg-blue text-white" scope="col">No</th>
                    <th class="bg-blue text-white" scope="col">Layanan</th>
                    <th class="bg-blue text-white" scope="col">Klien</th>
                    <th class="bg-blue text-white" scope="col">Kategori</th>
                    <th class="bg-blue text-white" scope="col">Staff</th>
                    <th class="bg-blue text-white text-end" scope="col">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type' => 'draft_kerja',
                    'posts_per_page' => -1,
                );

                if(isset($_GET['konsumen'])){
                    $args['meta_query'] = array(
                        array(
                            'key'   => 'customer_select',
                            'value' => $_GET['konsumen'],
                            'compare' => '=' // Opsional, '=' adalah default value dari 'compare'
                        ),
                    );
                }

                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                ?>
                        <tr>
                            <td>
                                <a href="<?php echo get_site_url(); ?>/jobdesk/?post_id=<?php $post->ID; ?>">
                                <?php the_title(); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo get_post_meta($post->ID, 'layanan', true); ?><br/>
                                <small class="text-muted">
                                <?php 
                                $tgl = get_post_meta($post->ID, 'tanggal_order', true);
                                echo $tgl ? date("d/m/Y", strtotime($tgl)) : '';
                                ?>
                                </small>
                            </td>
                            <td>
                            <?php 
                            $customer = get_post_meta($post->ID, 'customer_select', true); 
                            $nama = get_post_meta($customer, '_customer_data_nama_lengkap', true);
                            echo '<a href="?konsumen='.$customer.'">'.$nama.'</a>';
                            //echo '<small>('.get_post_meta($customer, '_customer_data_whatsapp', true).')</small>';
                            ?>
                            <br>
                            <small class="text-muted"><?php echo get_post_meta($customer, '_customer_data_alamat', true); ?></small>
                            </td>
                            <td>
                                <small>
                                <?php  
                                // Mendapatkan taksonomi 'data_pelanggan' dari pos dengan ID tertentu
                                $terms = wp_get_post_terms( $customer, 'konsumen_kategori' );

                                // Memeriksa apakah ada taksonomi yang ditemukan
                                if ( $terms && ! is_wp_error( $terms ) ) {
                                    $term_links = array();
                                    foreach ( $terms as $term ) {
                                        // Mendapatkan URL arsip untuk setiap taksonomi
                                        $term_link = get_term_link( $term );
                                        if ( ! is_wp_error( $term_link ) ) {
                                            // Membuat link HTML untuk taksonomi
                                            $term_links[] = '<a href="?kategori=' . $term->term_id . '">' . esc_html( $term->name ) . '</a>';
                                        }
                                    }
                                    // Menampilkan link taksonomi dipisahkan dengan koma
                                    echo implode( ', ', $term_links );
                                } else {
                                    echo '-';
                                }
                                ?>
                                </small>
                            </td>
                            <td>
                                <small>
                                <?php
                                // Mendapatkan ID penulis berdasarkan ID postingan
                                $author_id = get_post_field( 'post_author', $post->ID );

                                // Mendapatkan nama lengkap penulis berdasarkan ID penulis
                                $author_name = get_the_author_meta( 'display_name', $author_id );

                                // Menampilkan nama lengkap penulis
                                echo $author_name;
                                ?>
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="text-end btn-group">
                                    <a class="btn btn-info btn-sm text-white tooltips" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit" href="<?php echo get_site_url(); ?>/kelola-prosses-kerja/?post_id=<?php echo $post->ID; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" class="bi bi-pencil" viewBox="0 0 16 16">
                                            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                        </svg>
                                    </a>
                                    <a class="btn btn-info btn-sm text-white" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit" href="<?php echo get_site_url(); ?>/jobdesk/?post_id=<?php echo $post->ID; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" class="bi bi-journal-text" viewBox="0 0 16 16">
                                            <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/><path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/><path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
                                        </svg>
                                    </a>
                                    <a class="btn btn-info btn-sm text-white load-pdf" data-url="<?php echo get_site_url(); ?>/page-print/?proses_kerja=<?php echo $post->ID ?>" data-bs-toggle="modal" data-bs-target="#exampleModalPrint">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-filetype-pdf" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z"/>
                                        </svg>
                                    </a>

                                    <a class="btn btn-danger btn-sm text-white" href="<?php echo ($post->ID > 0) ? wp_nonce_url(admin_url('admin-post.php?action=delete_post&redirect='.get_site_url().'/prosses-kerja/&post_id=' . $post->ID), 'delete_post_' . $post->ID) : ''; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                            <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>                        
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                    <tr>
                        <td colspan="6">Tidak ada Order yang ditemukan</td>
                    </tr>
                <?php
                endif;
                ?>
            </tbody>
        </table>
        <!-- Modal -->
        <div class="modal fade" id="exampleModalPrint" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Detail Job Desk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('draft_kerja', 'draft_kerja_shortcode');


function data_konsumen() {
    ob_start();
    if ( !(current_user_can( 'administrator' ) || current_user_can( 'editor' )) ) {
        return 'Silahkan login sebagai administrator untuk melihat data.';
    }
    global $post;
    ?>
    <div class="table-responsive">
        <div class="mb-2">
            <a class="btn btn-sm btn-success text-white" href="<?php echo get_site_url();?>/kelola-konsumen/">Tambah Konsumen</a>
            <a class="btn btn-sm btn-success text-white" href="<?php echo get_site_url();?>/kelola-kategori/">Kelola Kategori</a>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="bg-blue text-white" scope="col">Nama</th>
                    <th class="bg-blue text-white" scope="col">No. HP</th>
                    <th class="bg-blue text-white" scope="col">Alamat</th>
                    <th class="bg-blue text-white" scope="col">Kategori</th>
                    <th class="bg-blue text-white text-end" scope="col">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type' => 'data_pelanggan',
                    'posts_per_page' => -1,
                );
                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                ?>
                    <tr>
                        <td class="align-middle">
                            <a class="btn btn-sm" href="<?php echo get_site_url(); ?>/kelola-konsumen/?post_id=<?php echo $post->ID; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="dark" class="bi bi-pencil" viewBox="0 0 16 16">
                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                </svg>
                            </a>
                            <?php echo get_post_meta($post->ID, '_customer_data_nama_lengkap', true); ?>
                        </td>
                        <td><?php echo get_post_meta($post->ID, '_customer_data_whatsapp', true); ?></td>
                        <td><?php echo get_post_meta($post->ID, '_customer_data_alamat', true); ?></td>
                        <td>
                        <?php
                            $konsumen_kategori = wp_get_post_terms($post->ID, 'konsumen_kategori');
                            if (!empty($konsumen_kategori)) {
                                foreach ($konsumen_kategori as $kategori) {
                                    echo '<span class="badge rounded-pill text-bg-light">'.$kategori->name . '</span>';
                                }
                            }
                            // echo implode(',', $konsumen_kategori);
                        ?>
                        </td>
                        <td>
                            <div class="text-end">
                                <a class="btn btn-dark btn-sm text-white" href="<?php echo get_site_url(); ?>/kelola-prosses-kerja/?user_id=<?php echo $post->ID; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                    </svg>
                                </a>
                                <a class="btn btn-dark btn-sm text-white" href="<?php echo get_site_url(); ?>/prosses-kerja/?konsumen=<?php echo $post->ID; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-eye" viewBox="0 0 16 16">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                    </svg>
                                </a>
                                <a class="btn btn-dark btn-sm text-white" href="<?php echo ($post->ID > 0) ? wp_nonce_url(admin_url('admin-post.php?action=delete_post&redirect='.get_site_url().'/data-konsumen/&post_id=' . $post->ID), 'delete_post_' . $post->ID) : ''; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                    <tr>
                        <td colspan="2">Tidak ada Order yang ditemukan</td>
                    </tr>
                <?php
                endif;
                ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('data_konsumen', 'data_konsumen');


function delete_post_action() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete_post' && isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']);
        $redirect = $_GET['redirect'] ?? get_site_url();

        // Memeriksa apakah pengguna saat ini adalah administrator
        if (!current_user_can('administrator')) {
            wp_die('Maaf, Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        // Memeriksa apakah nonce valid
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_post_' . $post_id)) {
            wp_die('Permintaan tidak valid.');
        }

        // Hapus postingan
        if (wp_delete_post($post_id, true)) {
            wp_redirect($redirect);
            exit;
        } else {
            wp_die('Gagal menghapus postingan.');
        }
    }
}

// Menangani aksi penghapusan postingan setelah admin_post
add_action('admin_post_delete_post', 'delete_post_action');

// Register shortcode
add_shortcode('crud_taxonomy', 'crud_taxonomy_shortcode');

function crud_taxonomy_shortcode() {
    if ( !(current_user_can( 'administrator' ) || current_user_can( 'editor' )) ) {
        return '<center>Silahkan <a class="btn btn-dark text-white" href="'.get_site_url().'/login/"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-person-fill" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg> login</a> untuk melihat data </center>';
    }
    $output = '';
    // Handle form submission
    if (isset($_POST['submit'])) {
        $action = $_POST['action'];
        $term_name = sanitize_text_field($_POST['term_name']);

        if ($action == 'create') {
            // Create new term
            $term_id = wp_insert_term($term_name, 'konsumen_kategori');
            if (!is_wp_error($term_id)) {
                $output .= '<p>Kategori berhasil ditambahkan!</p>';
            } else {
                $output .= '<p>Error: ' . $term_id->get_error_message() . '</p>';
            }
        } elseif ($action == 'update') {
            $term_id = intval($_POST['term_id']);

            // Update term
            $result = wp_update_term($term_id, 'konsumen_kategori', array(
                'name' => $term_name
            ));
            if (!is_wp_error($result)) {
                $output .= '<p>Kategori diperbarui.</p>';
            } else {
                $output .= '<p>Error: ' . $result->get_error_message() . '</p>';
            }
        } elseif ($action == 'delete') {
            $term_id = intval($_POST['term_id']);

            // Delete term
            $result = wp_delete_term($term_id, 'konsumen_kategori');
            if (!is_wp_error($result)) {
                $output .= '<p>Term deleted successfully!</p>';
            } else {
                $output .= '<p>Error: ' . $result->get_error_message() . '</p>';
            }
        }
    }

    // Display form
    $output .= '
        <form method="post" class="d-flex flex-nowrap">
            <input class="form-control me-2" type="text" id="term_name" name="term_name" placeholder="Nama Kategori" required>
            <input type="hidden" name="action" value="create">
            <input class="btn btn-sm btn-dark text-white" type="submit" name="submit" value="Buat Kategori">
            <a class="btn btn-sm btn-dark text-white ms-1" href="'.get_site_url().'/data-konsumen/">Konsumen</a>
        </form>
    ';

    // Retrieve terms
    $terms = get_terms(array(
        'taxonomy' => 'konsumen_kategori',
        'hide_empty' => false,
    ));

    // Display terms
    $output .= '<table class="table mt-2">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th scope="col" class="bg-blue text-white">Nama Kategori</th>';
    $output .= '<th scope="col" class="bg-blue text-white text-end">Aksi</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';
    foreach ($terms as $term) {
        $output .= '<tr>';
        $output .= '<td>' . $term->name . '</td>';
        $output .= '<td class="text-end"><a href="#" class="edit-term" data-id="' . $term->term_id . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg></a> | <a href="#" class="delete-term" data-id="' . $term->term_id . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16"><path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/></svg></a></td>';
        $output .= '</tr>';
    }
    $output .= '</tbody>';
    $output .= '</table>';

    // JavaScript for handling edit and delete actions
    $output .= '
        <script>
            jQuery(document).ready(function($) {
                $(".edit-term").click(function(e) {
                    e.preventDefault();
                    var term_id = $(this).data("id");
                    var term_name = $(this).parent().prev().text().trim();
                    $("#term_name").val(term_name);
                    $("input[name=\'action\']").val("update");
                    $("<input>").attr({type: "hidden", name: "term_id", value: term_id}).appendTo("form");
                    $("input[name=\'submit\']").val("Update Term");
                });

                $(".delete-term").click(function(e) {
                    e.preventDefault();
                    if (confirm("Are you sure you want to delete this term?")) {
                        var term_id = $(this).data("id");
                        $("input[name=\'action\']").val("delete");
                        $("<input>").attr({type: "hidden", name: "term_id", value: term_id}).appendTo("form");
                        $("form").submit();
                    }
                });
            });
        </script>
    ';

    return $output;
}
function pp() {
    $user_id = get_current_user_id();

    // Set user ID
    um_fetch_user( $user_id );

    // Returns current user avatar
    $avatar_uri = um_get_avatar_uri( um_profile('profile_photo'), 32 );

    // Returns default UM avatar, e.g. https://ultimatemember.com/wp-content/uploads/2015/01/default_avatar.jpg
    $default_avatar_uri = (filter_var($avatar_uri, FILTER_VALIDATE_URL) === FALSE) ? 'https://asistennotaris.com/wp-content/uploads/2024/04/user-2.png' : $avatar_uri;
    $img = '<img src="'.$default_avatar_uri.'" alt="" style="width:30px;height=30px;border-radius:50%;">';
    return $img;
}
add_shortcode('pp', 'pp');

// Fungsi untuk membuat shortcode tombol "Kembali"
function back_button_shortcode() {
    // Mengembalikan kode HTML untuk tombol "Kembali" dengan JavaScript inline
    return '<button class="btn btn-sm btn-primary text-white" onclick="window.history.back()">Kembali</button>';
}
// Mendaftarkan shortcode dengan nama 'back_button'
add_shortcode('back_button', 'back_button_shortcode');

//regsiter page template
add_filter( 'template_include', 'vdc_register_page_template' );
function vdc_register_page_template( $template ) {

    if ( is_singular() ) {
        $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
        if('print-notaris' === $page_template){
            $template = plugin_dir_path(__FILE__) . 'page-print.php';
        }
    }

    return $template;
}
add_filter( "theme_page_templates", 'vdc_templates_page' );
function vdc_templates_page($post_templates) {
    $post_templates['print-notaris'] = __( 'Print notaris', 'wss' );
    return $post_templates;
}