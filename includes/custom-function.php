<?php
// Fungsi untuk register custom taxonomy
function register_custom_taxonomy()
{
    $labels = array(
        'name'              => _x('Konsumen Kategori', 'taxonomy general name'),
        'singular_name'     => _x('Konsumen Kategori', 'taxonomy singular name'),
        'search_items'      => __('Cari Konsumen'),
        'all_items'         => __('Semua Konsumen'),
        'parent_item'       => __('Konsumen Induk'),
        'parent_item_colon' => __('Konsumen Induk:'),
        'edit_item'         => __('Edit Konsumen'),
        'update_item'       => __('Perbarui Konsumen'),
        'add_new_item'      => __('Tambah Konsumen'),
        'new_item_name'     => __('Nama Konsumen Baru'),
        'menu_name'         => __('Konsumen Kategori'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'konsumen'),
    );

    // Register custom taxonomy
    register_taxonomy('konsumen_kategori', array('data_pelanggan'), $args);
}


// Panggil fungsi register pada hook init
add_action('init', 'register_custom_taxonomy');
// // Fungsi untuk register custom taxonomy
// function register_custom_taxonomy_job_desk() {
//     $labels = array(
//         'name'              => _x( 'Kategori Job Desk', 'taxonomy general name' ),
//         'singular_name'     => _x( 'Kategori Job Desk', 'taxonomy singular name' ),
//         'search_items'      => __( 'Cari Kategori Job Desk' ),
//         'all_items'         => __( 'Semua Kategori Job Desk' ),
//         'parent_item'       => __( 'Kategori Job Desk Induk' ),
//         'parent_item_colon' => __( 'Kategori Job Desk Induk:' ),
//         'edit_item'         => __( 'Edit Kategori Job Desk' ), 
//         'update_item'       => __( 'Perbarui Kategori Job Desk' ),
//         'add_new_item'      => __( 'Tambah Kategori Job Desk' ),
//         'new_item_name'     => __( 'Nama Kategori Job Desk Baru' ),
//         'menu_name'         => __( 'Kategori Kategori Job Desk' ),
//     );

//     $args = array(
//         'hierarchical'      => true,
//         'labels'            => $labels,
//         'show_ui'           => true,
//         'show_admin_column' => true,
//         'query_var'         => true,
//         'rewrite'           => array( 'slug' => 'kategori_job_desk' ),
//     );

//     // Register custom taxonomy
//     register_taxonomy( 'kategori_job_desk', array( 'data_pelanggan' ), $args );
// }

// Panggil fungsi register pada hook init
// add_action( 'init', 'register_custom_taxonomy_job_desk' );

// Menambahkan Kolom Kustom ke Halaman Admin
function custom_admin_column_draft_kerja($columns)
{
    // Ganti judul kolom "title" menjadi "Draft Order"
    $columns['title'] = 'Draft Order';
    $columns['klien'] = 'Data Klien';
    // Menambahkan kolom baru untuk post meta 'kerja'
    $columns['draft_kerja'] = 'Draft Kerja';

    return $columns;
}
add_filter('manage_draft_kerja_posts_columns', 'custom_admin_column_draft_kerja');

// Menampilkan Nilai Post Meta pada Kolom Baru
function custom_admin_column_draft_kerja_content($column, $post_id)
{
    if ($column == 'klien') {
        $klien = get_post_meta($post_id, 'customer_select', true);
        $whatsapp = get_post_meta($klien, '_customer_data_whatsapp', true);
        echo '<a target="_blank" href="' . get_site_url() . '/wp-admin/post.php?post=' . $klien . '&action=edit">' . get_the_title($klien) . '</a>';
        echo $whatsapp != '' ? '<br>' . $whatsapp : '';
    }
    if ($column == 'draft_kerja') {
        // Ambil nilai post meta 'kerja' dan tampilkan
        $kerja_value = get_post_meta($post_id, 'pekerjaan_group', true);
        $i = 1;
        $progress = [];
        if (is_array($kerja_value)) {
            foreach ($kerja_value as $val) {
                $j = $i++;
                $status = $val['status'] ?? '';
                $color = ($status == 'Selesai') ? 'success' : (($status == 'Pengerjaan') ? 'warning' : 'secondary');
                $tgl_mulai = isset($val['tanggal_mulai_date']) ? date('d m Y', strtotime($val['tanggal_mulai_date'])) : '';
                $tgl_selesai = isset($val['tanggal_selesai_date']) ? date('d m Y', strtotime($val['tanggal_selesai_date'])) : '';
                $user_nama = isset($val['user_list']) ? my_get_users_name($val['user_list']) : '';
                $jabatan = isset($val['user_list']) ? get_user_meta($val['user_list'], 'jabatan', true) : '';
                $progress[$status][] = '1';
                $progress['all'][] = '1';

                echo $j >= 2 ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-short" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8"/></svg>' : '';

                echo '<a class="btn btn-sm my-1 btn-' . $color . '"';
                echo 'data-bs-toggle="tooltip" data-bs-html="true" title="<small>Dikerjakan oleh</small> ' . $user_nama . ' - ' . $jabatan . ' <br><span>' . $tgl_mulai . ' - ' . $tgl_selesai . '</span>"';
                echo '>';
                echo isset($val['pekerjaan_text']) ? $val['pekerjaan_text'] : '';
                echo '</a>';
            }
        }
    }
}
add_action('manage_draft_kerja_posts_custom_column', 'custom_admin_column_draft_kerja_content', 10, 2);

function my_get_users_name($user_id = null)
{
    $user_info = $user_id ? new WP_User($user_id) : wp_get_current_user();
    if ($user_info->first_name) {
        if ($user_info->last_name) {
            return $user_info->first_name . ' ' . $user_info->last_name;
        }
        return $user_info->first_name;
    }
    return $user_info->display_name;
}

// Fungsi untuk menambahkan Bootstrap 5 hanya di halaman admin untuk post type 'draft_kerja'
function load_bootstrap_5_admin_style_script($hook)
{
    // Cek apakah sedang berada di halaman edit post type 'draft_kerja'
    if (($hook == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'draft_kerja') || $hook == 'users.php') {
        // Memuat Bootstrap 5 CSS dari CDN
        wp_enqueue_style('bootstrap-5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

        // Memuat Bootstrap 5 JavaScript dari CDN
        wp_enqueue_script('bootstrap-5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    }
}

// Tambahkan Hook untuk Eksekusi Fungsi
add_action('admin_enqueue_scripts', 'load_bootstrap_5_admin_style_script');

// Tambahkan custom column pada tabel pengguna
function custom_user_table_columns($columns)
{
    $columns['user_jabatan'] = 'Jabatan';
    $columns['user_type'] = 'Tipe';
    $columns['user_status'] = 'Status';
    return $columns;
}
add_filter('manage_users_columns', 'custom_user_table_columns');

// Tampilkan data pada custom column
function custom_user_table_column_content($value, $column_name, $user_id)
{
    // Ambil user meta berdasarkan nama kolom
    $user_jabatan = get_user_meta($user_id, 'jabatan', true);
    $user_status = get_user_meta($user_id, 'status', true);
    $user_type = get_user_meta($user_id, 'type', true);

    // Tampilkan data sesuai dengan nama kolom
    switch ($column_name) {
        case 'user_jabatan':
            return $user_jabatan;
        case 'user_type':
            return $user_type;
        case 'user_status':
            if ($user_status == 'Non Aktif') {
                return '<a class="btn btn-sm btn-warning">' . $user_status . '</a>';
            } else {
                return '<a class="btn btn-sm btn-success">' . $user_status . '</a>';
            }
        default:
            return $value;
    }
}
add_action('manage_users_custom_column', 'custom_user_table_column_content', 10, 3);

// Fungsi untuk mendapatkan daftar post type 'data_pelanggan'
function get_customer_posts()
{
    $post_id = $_GET['post_id'] ?? '';
    $user_id = $_GET['user_id'] ? $_GET['user_id'] : get_post_meta($post_id, 'customer_select', true);
    $args = array(
        'post_type'      => 'data_pelanggan',
        'posts_per_page' => -1,
    );

    $customer_posts = get_posts($args);

    $options = array();
    if (!$user_id) {
        $options[''] = '-';
    }
    foreach ($customer_posts as $post) {
        if ($user_id && $user_id == $post->ID) {
            $options[$post->ID] = $post->post_title;
        }
        if (!$user_id) {
            $options[$post->ID] = $post->post_title;
        }
    }

    return $options;
}

function bl_get_post_id_by_title(string $title = ''): int
{
    $posts = get_posts(
        array(
            'post_type'              => 'draft_kerja',
            'title'                  => $title,
            'numberposts'            => 1,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby'                => 'post_date ID',
            'order'                  => 'ASC',
            'fields'                 => 'ids'
        )
    );

    return empty($posts) ? get_the_ID() : $posts[0];
}

function get_draft_kerja_posts()
{
    $args = array(
        'post_type'      => 'draft_kerja',
        'posts_per_page' => -1,
    );

    $customer_posts = get_posts($args);

    $options = array();
    $options[''] = '-';
    foreach ($customer_posts as $post) {
        $options[$post->ID] = $post->post_title;
    }

    return $options;
}

function tracking_order_shortcode()
{
    ob_start();
    global $post;
    $current_user = wp_get_current_user();

    // Cetak peran pengguna yang saat ini masuk
    // echo 'Peran pengguna saat ini: ' . implode( ', ', $current_user->roles );
    if (!(current_user_can('administrator') || current_user_can('editor'))) {
        return 'Silahkan login sebagai administrator untuk melihat data.';
    }
    $kode_layanan = $_GET['kode-layanan'] ?? '';
    $draft_id = bl_get_post_id_by_title($kode_layanan);
    $customer_id = get_post_meta($draft_id, 'customer_select', true);
    $customer_name = get_post_meta($customer_id, '_customer_data_nama_lengkap', true);

    $tanggal_order = get_post_meta($draft_id, 'tanggal_order', true);
    $layanan = get_post_meta($draft_id, 'layanan', true);


    $post_judul_job_desk = isset($_POST['judul_job_desk']) ? 'show d-block' : '';
    $args = array(
        'post_type' => 'job_desk',
        'meta_query' => array(
            array(
                'key'   => 'job_desk_draft_kerja',
                'value' => $draft_id,
                'compare' => '='
            ),
        ),
        'meta_key' => 'job_desk_start', // Meta key yang digunakan untuk order
        'orderby' => 'meta_value', // Order by meta value
        'order' => 'DESC', // Urutkan secara descending
    );
    $the_query = new WP_Query($args);
?>
    <div class="row">
        <div class="col-md-4 mb-3 mb-sm-0">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-3x text-primary"></i>
                            <h3 class="h4" style="font-size:22px;"><b><?php echo $layanan ?></b></h3>
                            <p>
                                Konsumen: <b><?php echo $customer_name ?></b><br />
                                Tanggal Order: <?php echo convertDateFormat($tanggal_order); ?>
                            </p>
                        </div>
                        <div class="col-auto">
                            <button type="button" data-url="<?php echo get_site_url(); ?>/kelola-job-desk/" class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                Tambah Job Desk
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table">
                        <?php
                        // The Loop.
                        if ($the_query->have_posts()) {
                        ?>
                            <thead>
                                <tr>
                                    <th scope="col">Judul</th>
                                    <th scope="col">Staff</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Mulai</th>
                                    <th scope="col">Selesai</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($the_query->have_posts()) {
                                    $the_query->the_post();

                                    $judul = get_post_meta($post->ID, 'judul_job_desk', true);
                                    $post_id =  $post->ID;
                                    $author_id = get_post_field('post_author', $post_id);
                                    $display_name = get_the_author_meta('nickname', $author_id);
                                    $kategori = get_post_meta($post->ID, 'job_desk_kategori_select', true);
                                    $tanggal_mulai = get_post_meta($post->ID, 'job_desk_start', true);
                                    $tanggal_selesai = get_post_meta($post->ID, 'job_desk_end', true);
                                    $status = get_post_meta($post->ID, 'job_desk_status', true);
                                    $id_draft_bypost = get_post_meta($post->ID, 'job_desk_draft_kerja', true);
                                ?>

                                    <tr>
                                        <th><span><?php echo $judul; ?></span></th>
                                        <td><span><?php echo $display_name ?></span>
                                        <td><span><small><?php echo $kategori ?></small></span>
                                        <td><span><?php echo convertDateFormat($tanggal_mulai); ?></span></td>
                                        <td><span><?php echo convertDateFormat($tanggal_selesai); ?></span>
                                        <td class="text-center"><span>
                                                <?php
                                                if ($status == 'Selesai') {
                                                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-fill" style="color: green;" viewBox="0 0 16 16"><circle cx="8" cy="8" r="8"/></svg>';
                                                } elseif ($status == 'Pengerjaan') {
                                                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-fill" style="color: orange;" viewBox="0 0 16 16"><circle cx="8" cy="8" r="8"/></svg>';
                                                } else {
                                                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/></svg>';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                                <button type="button" data-url="<?php echo get_site_url(); ?>/kelola-job-desk/?post_id=<?php echo $post->ID; ?>" class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill-gear" viewBox="0 0 16 16">
                                                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0" />
                                                    </svg>
                                                </button>
                                                <a class="btn btn-sm btn-danger" href="<?php echo ($post->ID > 0) ? wp_nonce_url(admin_url('admin-post.php?action=delete_post&redirect=' . get_site_url() . '/jobdesk/?kode-layanan=CS-1708589182803/'), 'delete_post_' . $post->ID) : ''; ?>" class="btn btn-danger">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-trash" viewBox="0 0 16 16">
                                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                                    </svg>
                                                </a>
                                            </div>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        <?php
                        } else {
                        ?>
                            <tbody>
                                <tr>Jobdesk tidak ditemukan untuk order ini. klik <b>Tambah Job Desk</b> dibawah ini utuk menambahkan job desk baru.</tr>
                            </tbody>
                        <?php
                        }
                        // Restore original Post Data.
                        wp_reset_postdata();
                        ?>
                    </table>
                    <!-- Button trigger modal -->
                    <div class="text-end mt-3">
                        <button type="button" data-url="<?php echo get_site_url(); ?>/kelola-job-desk/" class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            Tambah Job Desk
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Kelola Jobdesk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body hide-frame">
                    Loading
                </div>
                <?php
                if (isset($_POST['judul_job_desk'])) {
                ?>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        jQuery(function($) {
            $('#staticBackdrop').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var url = button.data('url'); // Extract info from data-* attributes
                console.log(url);
                var modal = $('#staticBackdrop .modal-body').html('<iframe  style="width:100%; height:70vh;" src="' + url + '"></iframe>');
            });
        });
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('tracking-order', 'tracking_order_shortcode');

// Menambahkan kolom admin untuk post type 'data_pelanggan'
function tambah_kolom_admin_data_pelanggan($columns)
{
    $columns['whatsapp'] = 'WhatsApp';
    $columns['alamat'] = 'Alamat';
    return $columns;
}
add_filter('manage_data_pelanggan_posts_columns', 'tambah_kolom_admin_data_pelanggan');

// Menampilkan isi kolom admin untuk post type 'data_pelanggan'
function isi_kolom_admin_data_pelanggan($column, $post_id)
{
    switch ($column) {
        case 'whatsapp':
            echo get_post_meta($post_id, '_customer_data_whatsapp', true);
            break;
        case 'alamat':
            echo get_post_meta($post_id, '_customer_data_alamat', true);
            break;
    }
}
add_action('manage_data_pelanggan_posts_custom_column', 'isi_kolom_admin_data_pelanggan', 10, 2);

// Mengatur kolom agar dapat diurutkan berdasarkan WhatsApp
function urut_kolom_admin_data_pelanggan($columns)
{
    $columns['whatsapp'] = 'whatsapp';
    return $columns;
}
add_filter('manage_edit-data_pelanggan_sortable_columns', 'urut_kolom_admin_data_pelanggan');


// Add the custom columns to the job_desk post type:
add_filter('manage_job_desk_posts_columns', 'set_custom_edit_job_desk_columns');
function set_custom_edit_job_desk_columns($columns)
{
    unset($columns['author']);
    $columns['judul_job_desk'] = __('Job Desk', 'your_text_domain');
    $columns['staf'] = __('Staff', 'your_text_domain');
    $columns['kategori'] = __('Kategori', 'your_text_domain');
    $columns['tgl_mulai'] = __('Tgl Mulai', 'your_text_domain');
    $columns['tgl_selesai'] = __('Tgl Selesai', 'your_text_domain');
    $columns['status'] = __('Status', 'your_text_domain');

    return $columns;
}

// Add the data to the custom columns for the job_desk post type:
add_action('manage_job_desk_posts_custom_column', 'custom_job_desk_column', 10, 2);
function custom_job_desk_column($column, $post_id)
{
    switch ($column) {
        case 'judul_job_desk':
            $judul_job_desk = get_post_meta($post_id, "judul_job_desk", true);
            echo $judul_job_desk;
            break;
        case 'staf':
            $author_id = get_post_field('post_author', $post_id);
            $display_name = get_the_author_meta('display_name', $author_id);
            echo $display_name;
            break;
        case 'kategori':
            $kategori = get_post_meta($post_id, 'job_desk_kategori_select', true);
            echo $kategori;
            break;
        case 'tgl_mulai':
            $tgl_mulai = get_post_meta($post_id, 'job_desk_start', true);
            echo $tgl_mulai;
            break;
        case 'tgl_selesai':
            $tgl_selesai = get_post_meta($post_id, 'job_desk_end', true);
            echo $tgl_selesai;
            break;
        case 'status':
            $status = get_post_meta($post_id, 'job_desk_status', true);
            if ($status == 'Selesai') {
                echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-fill" style="color: green;" viewBox="0 0 16 16"><circle cx="8" cy="8" r="8"/></svg>';
            } elseif ($status == 'Pengerjaan') {
                echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-fill" style="color: orange;" viewBox="0 0 16 16"><circle cx="8" cy="8" r="8"/></svg>';
            } else {
                echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/></svg>';
            }
            break;
    }
}

// Fungsi untuk membuat shortcode [jobdesk]
function jobdesk_shortcode($atts)
{
    ob_start();
    global $post;
    $paged = $_GET['halaman'] ?? '1';
    $post_per_page = 20;
    $get_post_id = $_GET['post_id'] ?? '';

    // Pengaturan atribut shortcode
    $atts = shortcode_atts(array(
        'posts_per_page' => 20,
        'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
    ), $atts, 'jobdesk');

    // Query untuk mendapatkan daftar post type 'job_desk' dan urutkan berdasarkan meta 'job_desk_start' terlama
    $query_args = array(
        'post_type' => 'job_desk',
        'posts_per_page' => $atts['posts_per_page'],
        'paged' => $atts['paged'],
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_key' => 'job_desk_start',
        'paged' => $paged,
        'posts_per_page' => $post_per_page,
    );
    // if administrator
    if (!current_user_can('administrator') && empty($get_post_id)) {
        $query_args['meta_key'] = 'job_desk_id_staff';
        $query_args['meta_value'] = get_current_user_id();
    } elseif ($get_post_id) {
        $query_args['meta_key'] = 'job_desk_draft_kerja';
        $query_args['meta_value'] = $get_post_id;
    }
    // echo '<pre>'.print_r($query_args, 1).' -'.$get_post_id.'</pre>';
    $query = new WP_Query($query_args);

    // Jika $get_post_id ditemukan maka tampilkan detail job_desk dengan ID $get_post_id
    if ($get_post_id != '') {
        $customer_id = get_post_meta($get_post_id, 'customer_select', true);
        $customer_name = get_post_meta($customer_id, '_customer_data_nama_lengkap', true);

        $biaya_transaksi = get_post_meta($get_post_id, 'biaya_transaksi', true);
        $biaya_transfer = get_post_meta($get_post_id, 'biaya_transfer', true);
        $dibayar = get_post_meta($get_post_id, 'dibayar', true);

        // Jika nilai kosong atau tidak valid, set ke 0
        $biaya_transaksi = preg_replace('/[^0-9]/', '', $biaya_transaksi);
        $biaya_transfer = preg_replace('/[^0-9]/', '', $biaya_transfer);
        $dibayar = preg_replace('/[^0-9]/', '', $dibayar);

        $total_biaya = (intval($biaya_transaksi) + intval($biaya_transfer));
        $formatted_total_biaya = 'Rp. ' . number_format($total_biaya, 2, ',', '.');

        $format_dibayar = 'Rp. ' . number_format($dibayar, 2, ',', '.');

        $kekurangan = $total_biaya - $dibayar;
        $format_kekurangan = 'Rp. ' . number_format($kekurangan, 2, ',', '.');

        echo '<div class="container">';
        echo '<div class="card mb-5">';
        echo '<div class="card-body">';
        echo '<div class="row ">';
        echo '<div class="col-md-8">';
        echo '<h5 class="card-title"><b>' . get_post_meta($get_post_id, 'layanan', true) . '</b></h5>';
        echo '<p class="card-text">';
        echo 'ID Order: ' . get_the_title($get_post_id) . ' | ';
        echo 'Tanggal Order: ' . convertDateFormat(get_post_meta($get_post_id, 'tanggal_order', true)) . '<br>';
        echo 'Customer: ' . $customer_name . '<br>';
        echo 'Biaya Transaksi: ' . $formatted_total_biaya . '<br>';
        echo 'Dibayar: ' . $format_dibayar . '<br>';
        echo '<b>Kekurangan: ' . $format_kekurangan . '<br></b>';

        echo '</p>';
        echo '</div>';
        echo '<div class="col-md-4 text-end">';
        echo '<button type="button" data-url="' . get_site_url() . '/kelola-job-desk/?draft_id=' . $get_post_id . '" class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-journal-plus" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 5.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 .5-.5"/>
                                <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>
                                <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
                            </svg>
                            Tambah Job Desk
                            </button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    // Menampilkan daftar post dalam tabel
    if ($query->have_posts()) {
        echo '<div class="container">';

        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="bg-blue text-white" scope="col">#</th>';
        echo '<th class="bg-blue text-white" scope="col">Order</th>';
        echo '<th class="bg-blue text-white" scope="col">Pekerjaan</th>';
        echo '<th class="bg-blue text-white" scope="col">Kategori</th>';
        echo '<th class="bg-blue text-white" scope="col">Staff</th>';
        echo '<th class="bg-blue text-white" scope="col">Mulai</th>';
        echo '<th class="bg-blue text-white" scope="col">Selesai</th>';
        echo '<th class="bg-blue text-white" scope="col">Status</th>';
        echo '<th class="bg-blue text-white" scope="col">Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $count = 1;
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = $post->ID;
            $id_staff = get_post_meta($post_id, 'job_desk_id_staff', true);
            $disable_button = ($id_staff != get_current_user_id() && !current_user_can('administrator')) ? 'disabled' : '';
            $parent = get_post_meta($post_id, 'job_desk_draft_kerja', true);
            $user_info = get_userdata($id_staff);
            $delete_url = ($post_id > 0) ? wp_nonce_url(admin_url('admin-post.php?action=delete_post&redirect=' . get_site_url() . '/jobdesk/&post_id=' . $parent), 'delete_post_' . $post_id) : '';
            echo '<tr>';
            echo '<th scope="row">' . $count . '</th>';
            echo '<td><a href="?post_id=' . $parent . '">' . get_post_meta($parent, 'layanan', true) . '</a><br><small class="text-muted">ID: ' . get_the_title($parent) . '<small></td>';
            echo '<td>' . get_post_meta($post_id, 'judul_job_desk', true) . '</td>';
            echo '<td>' . get_post_meta($post_id, 'job_desk_kategori_select', true) . '</td>';
            echo '<td>' . $user_info->first_name . ' ' . $user_info->last_name . '</td>';
            echo '<td>' . convertDateFormat(get_post_meta($post_id, 'job_desk_start', true)) . '</td>';
            echo '<td>' . convertDateFormat(get_post_meta($post_id, 'job_desk_end', true)) . '</td>';
            echo '<td>' . get_post_meta($post_id, 'job_desk_status', true) . '</td>';
            echo '<td>';
            echo '<button type="button" data-url="' . get_site_url() . '/kelola-job-desk/?post_id=' . $post->ID . '" class="btn btn-sm me-1 btn-primary text-white" data-bs-toggle="modal" data-bs-target="#staticBackdrop" ' . $disable_button . '>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-pencil" viewBox="0 0 16 16">
                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                    </svg>
                </button>';
            echo '<a class="btn btn-danger btn-sm text-white ' . $disable_button . '" href="' . $delete_url . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                    </svg>
                </a>';
            echo '</td>';
            echo '</tr>';

            $count++;
        }

        echo '</tbody>';
        echo '</table>';
        // <?php
        // echo '<pre>' . print_r($query, 1) . '</pre>';
        pagination_bootstrap($query->found_posts, $post_per_page);
        echo '</div>';
    } else {
        echo '<p class="px-3">No job desks found.</p>';
    }
?>
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Kelola Jobdesk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body hide-frame">
                    Loading
                </div>
                <?php
                if (isset($_POST['judul_job_desk'])) {
                ?>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        jQuery(function($) {
            $('#staticBackdrop').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var url = button.data('url'); // Extract info from data-* attributes
                var modal = $('#staticBackdrop .modal-body').html('<iframe  style="width:100%; height:70vh;" src="' + url + '"></iframe>');
            });
        });
    </script>
<?php
    // Mengembalikan WordPress ke query asli
    wp_reset_postdata();

    return ob_get_clean();
}

// Menambahkan shortcode ke WordPress
add_shortcode('jobdesk', 'jobdesk_shortcode');

function hide_admin_bar_from_front_end()
{
    if (is_blog_admin()) {
        return true;
    }

    // Memeriksa apakah halaman yang sedang diakses adalah halaman dengan ID 503
    if (is_page(503)) {
        return false;
    }

    return true;
}
add_filter('show_admin_bar', 'hide_admin_bar_from_front_end');

function convertDateFormat($date)
{
    // Mengubah format tanggal dari MM/DD/YYYY menjadi YYYY-MM-DD
    $dateObject = DateTime::createFromFormat('m/d/Y', $date);

    // Memastikan bahwa objek DateTime berhasil dibuat
    if ($dateObject === false) {
        return "-";
    }

    // Mengubah format tanggal menjadi DD MM YYYY
    return $dateObject->format('d m Y');
}

function pagination_bootstrap($total_items, $items_per_page = 10)
{
    $current_page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
    $total_pages = ceil($total_items / $items_per_page);
    $status_post = $_GET['status_post'] ?? '';

    // Menghindari halaman yang tidak valid
    $current_page = max(1, min($current_page, $total_pages));

    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination justify-content-center">';

    // Link Previous
    if ($current_page > 1) {
        echo '<li class="page-item"><a class="page-link bg-light border-0" href="?halaman=' . ($current_page - 1) . '&status_post=' . $status_post . '">&laquo; Previous</a></li>';
    }

    // Link untuk setiap halaman
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo '<li class="page-item active"><span class="page-link bg-blue text-white border-0">' . $i . '</span></li>';
        } else {
            echo '<li class="page-item"><a class="page-link bg-light border-0" href="?halaman=' . $i . '&status_post=' . $status_post . '">' . $i . '</a></li>';
        }
    }

    // Link Next
    if ($current_page < $total_pages) {
        echo '<li class="page-item"><a class="page-link bg-light border-0" href="?halaman=' . ($current_page + 1) . '&status_post=' . $status_post . '">Next &raquo;</a></li>';
    }

    echo '</ul>';
    echo '</nav>';
}

add_action('wp_ajax_update_post_status', 'update_post_status_callback');
add_action('wp_ajax_nopriv_update_post_status', 'update_post_status_callback');

function update_post_status_callback()
{
    if (isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);

        // Update post meta
        update_post_meta($post_id, 'status_post', 'archive');

        // Kirim respons
        wp_send_json_success('Status post berhasil diubah.');
    } else {
        wp_send_json_error('Post ID tidak valid.');
    }
}
