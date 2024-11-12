<?php

// Fungsi untuk menampilkan formulir tambah dan edit pengguna
function display_user_crud_form($atts)
{
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
            return 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. <a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/kelola-user/">Coba Lagi</a>';
        }

        // Memeriksa apakah data yang diterima valid
        if (empty($username) || empty($email)) {
            return 'Harap isi semua kolom yang diperlukan. <a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/kelola-user/">Coba Lagi</a>';
        }

        if (empty($user_id)) {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // Memeriksa apakah password dan konfirmasi password sama
            if ($password !== $confirm_password) {
                return 'Password dan konfirmasi password tidak cocok. <a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/kelola-user/">Coba Lagi</a>';
            }

            // Memeriksa apakah password sudah diisi
            if (empty($password)) {
                return 'Harap isi password. <br><a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/kelola-user/">Coba Lagi</a>';
            }

            // Memeriksa apakah password memiliki panjang yang cukup
            if (strlen($password) < 8) {
                return 'Password harus terdiri dari minimal 8 karakter. <br><a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/kelola-user/">Coba Lagi</a>';
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

            return 'Pengguna berhasil diperbarui. <br><a class="btn btn-sm btn-dark text-white" href="' . $redirect . '">' . $redirect_text . '</a>';
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
                return 'Terjadi kesalahan saat menambahkan pengguna baru: ' . $new_user_id->get_error_message() . '<br><a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/kelola-user/">Coba Lagi</a>';
            }

            // Menambah metadata status dan jabatan
            add_user_meta($new_user_id, 'status', $status);
            add_user_meta($new_user_id, 'jabatan', $jabatan);
            add_user_meta($new_user_id, 'catatan', $catatan);
            add_user_meta($new_user_id, 'tanggal_daftar', $tanggal_daftar);
            $new_user = new WP_User($new_user_id);
            $new_user->set_role($role);
            $user_login = $new_user->user_login;


            return '<center>Staff baru berhasil ditambahkan. <br/><a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/data-staff/?type=staff">List Staff</a></center>';
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

        <?php if (empty($user_id)) { ?>

            <label for="password">Password:</label><br>
            <input class="form-control" type="password" id="password" name="password" value=""><br>
            <label for="confirm_password">Konfirmasi Password:</label><br>
            <input class="form-control" type="password" id="confirm_password" name="confirm_password" value="">
            <br>
        <?php } ?>

        <?php if (isset($_GET['type']) && $_GET['type'] == 'pelanggan') { ?>
            <label for="tanggal_daftar">Tanggal Daftar:</label><br>
            <input class="form-control" type="date" id="tanggal_daftar" name="tanggal_daftar" value="<?php echo ($user_data) ? $user_data->tanggal_daftar : date('Y-m-d'); ?>"><br>
            <input type="hidden" name="type" value="pelanggan">
        <?php } ?>

        <?php if (isset($_GET['type']) && $_GET['type'] == 'staff') { ?>
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










// Fungsi untuk menampilkan daftar pengguna dengan tabel Bootstrap 5
function display_user_list()
{
    // Memulai output buffering
    ob_start();
    $paged = $_GET['halaman'] ?? '1';
    $user_id = $_GET['user_id'] ?? '';
    $post_per_page = 20;
    // Number increament
    $number = 1;
    if ($user_id) {
    ?>
        <style>
            .cmb2-id-user-login {
                display: none;
            }
        </style>
    <?php
    }

    if (!(current_user_can('administrator') || current_user_can('editor'))) {
        return 'Silahkan login sebagai administrator untuk melihat data.';
    }
    // Query untuk mendapatkan daftar pengguna
    $users = get_users(
        array(
            'number' => $post_per_page,
            'offset' => ($paged - 1) * $post_per_page,

        )
    );


    $total_user = get_users();
    $total_users = count($total_user);
    $jabatan_options = array(
        'notaris' => 'Notaris',
        'ppat' => 'PPAT (Pejabat Pembuat Akta Tanah)',
        'staff_notaris' => 'Staff Notaris',
        'pegawai_administrasi' => 'Pegawai Administrasi',
        'staff_keuangan' => 'Staff Keuangan',
        // 'pengacara' => 'Pengacara atau Legal Consultant'
    );
    // Mengecek apakah ada pengguna yang ditemukan
    if (empty($users)) {
        echo 'Tidak ada pengguna yang ditemukan.';
    } else {
        echo '<button type="button" class="btn btn-success btn-sm text-white mb-2" data-bs-toggle="modal" data-bs-target="#tambahUser">
            Tambah User
        </button>';
        // Memulai pembentukan tabel
        echo '<div class="table-responsive">';
    ?>
        <!-- Button trigger modal -->
        <!-- <button type="button" class="btn btn-success btn-sm text-white mb-2" data-bs-toggle="modal" data-bs-target="#tambahUser">
            Tambah User
        </button> -->

        <!-- Modal -->
        <div class="modal fade" id="tambahUser" tabindex="-1" aria-labelledby="tambahUserlLabel" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="tambahUserlLabel">
                            <?php if (!empty($user_id)) { ?>
                                Edit Data User
                            <?php } else { ?>
                                Tambah User
                            <?php } ?>
                        </h1>
                        <a href="<?php echo get_site_url(); ?>/data-staff/?type=staff" type="button" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <?php echo do_shortcode('[add-new-user]') ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if (!empty($user_id)) {
        ?>
            <script type="text/javascript">
                jQuery(function($) {
                    $('#tambahUser').modal('show');

                });
            </script>
            <?php
        }
        // echo '<a class="btn btn-success btn-sm text-white mb-2" href="' . get_site_url() . '/wp-admin/user-new.php">Tambah User</a>';
        echo '<table class="table table-striped">';

        // Header tabel
        echo '<thead>';
        echo '<tr >';
        echo '<th class="bg-blue text-white" style="text-align: center;">No</th>';
        echo '<th class="bg-blue text-white">Nama</th>';
        echo '<th class="bg-blue text-white">PIC</th>';
        echo '<th class="bg-blue text-white">No Telepon</th>';
        echo '<th class="bg-blue text-white">Email</th>';
        echo '<th class="bg-blue text-white">User Role</th>';
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
            $user_data = get_userdata($user_id);
            $url_edit = get_site_url() . '/kelola-user/?edit=' . $user_id;
            $poto_profil = get_user_meta($user_id, 'poto_profil', true) ?? 'https://asistennotaris.com/wp-content/uploads/2024/10/user.png';
            $jabatan_staff = get_user_meta($user_id, 'jabatan_staff', true) ?? '-';
            if ($jabatan_staff == 'superadmin') {
                $jabatan_staff = 'Super Admin';
            } elseif ($jabatan_staff == 'administrator') {
                $jabatan_staff = 'Supervisor';
            } elseif ($jabatan_staff == 'editor') {
                $jabatan_staff = 'Manager';
            }
            $pic_staff = get_user_meta($user_id, 'pic_staff', true) ?? '-';
            $no_telpon_staff = get_user_meta($user_id, 'no_telpon_staff', true) ?? '-';
            $user_role = $user_data->roles[0] ?? '-';
            $address = get_user_meta($user_id, 'address', true) ?? '-';
            $url_edit = admin_url('user-edit.php?user_id=' . $user_id);
            $status = get_user_meta($user_id, 'status', true) ?? '-';
            echo '<tr>';
            echo '<td style="text-align: center;">' . $number++ . '.' . '</td>';
            echo '<td style="white-space: nowrap"><div class="d-flex align-items-center"><img class="rounded-circle ratio ratio-1x1 me-2" style="width: 100%; height: 100%; max-width: 40px; aspect-ratio: 1/1; object-fit: cover;" src="' . $poto_profil . '">' . $user->first_name . '</div></td>';
            echo '<td>' . $pic_staff . '</td>';
            echo '<td>' . $no_telpon_staff . '</td>';
            echo '<td>' . $user->user_email . '</td>';
            echo '<td>' . ucfirst($user_role) . '</td>';
            echo '<td><div style="white-space: nowrap">' . ucfirst($jabatan_staff) . '</div></td>';
            echo '<td>' . get_user_meta($user_id, 'status', true) . '</td>';
            if (current_user_can('administrator')) {
            ?>
                <td>
                    <div class="text-end d-flex justify-content-end">
                        <!-- Modal -->
                        <a href="?type=staff&user_id=<?php echo $user_id; ?>" class="btn btn-primary btn-sm text-white tooltips ms-1" data-bs-toggle="tooltip" data-bs-title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-person-fill-gear" viewBox="0 0 16 16">
                                <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0" />
                            </svg>
                        </a>
                        <!-- Modal -->
                        <a class="btn btn-primary btn-sm text-white tooltips ms-1" data-bs-toggle="modal" data-bs-target="#profilModal<?php echo $user_id; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-eye" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                            </svg>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm text-white ms-1" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?php echo $user_id; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" style="color: #ffffff;" fill="white" class="bi bi-trash3" viewBox="0 0 16 16">
                                <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                            </svg>
                        </button>
                    </div>
                    <!-- Modal Konfirmasi Hapus -->
                    <div class="modal fade" id="confirmDeleteModal<?php echo $user_id; ?>" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
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
                    <!-- Modal Singgle User -->
                    <div class="modal fade" id="profilModal<?php echo $user_id; ?>" tabindex="-1" aria-labelledby="profilModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="profilModalLabel">Profil Pengguna</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 text-center mb-3">
                                            <img class="rounded-circle ratio ratio-1x1" style="width: 100%; height: 100%; max-width: 150px; aspect-ratio: 1/1; object-fit: cover;" src="<?php echo $poto_profil; ?>" alt="">
                                        </div>
                                        <div class="col-12">
                                            <ol class="list-group text-start">
                                                <li class="list-group-item">
                                                    <div class="">
                                                        <div class="fw-bold"><?php echo  $user->display_name; ?></div>
                                                    </div>
                                                </li>
                                                <li class="list-group-item">
                                                    <div class="">
                                                        <div class="fw-bold">No Telepon</div>
                                                        <?php echo $no_telpon_staff; ?>
                                                    </div>
                                                </li>
                                                <li class="list-group-item">
                                                    <div class="">
                                                        <div class="fw-bold">Email</div>
                                                        <?php echo $user->user_email; ?>
                                                    </div>
                                                </li>
                                                <li class="list-group-item">
                                                    <div class="">
                                                        <div class="fw-bold">User Role</div>
                                                        <?php echo $user->roles[0]; ?>
                                                    </div>
                                                </li>
                                                <li class="list-group-item">
                                                    <div class="">
                                                        <div class="fw-bold">Jabatan</div>
                                                        <?php echo $jabatan_staff; ?>
                                                    </div>
                                                </li>
                                                <li class="list-group-item">
                                                    <div class="">
                                                        <div class="fw-bold">Alamat</div>
                                                        <?php echo $address; ?>
                                                    </div>
                                                </li>
                                                <li class="list-group-item">
                                                    <div class="">
                                                        <div class="fw-bold">Status</div>
                                                        <?php echo $status; ?>
                                                    </div>
                                                </li>
                                            </ol>
                                        </div>
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
        pagination_bootstrap($total_users, $post_per_page);
        echo '</div>';
    }

    // Mengambil output buffering, membersihkan buffer, dan mengembalikan output
    $output = ob_get_clean();
    return $output;
}

// Mendaftarkan shortcode
add_shortcode('user_list', 'display_user_list');




function draft_kerja_shortcode()
{
    ob_start();
    global $post, $wpdb;
    $s = $_GET['filter'] ?? '';
    $paged = $_GET['halaman'] ?? '1';
    $post_per_page = 20;
    $current_user = wp_get_current_user();
    $status_post = $_GET['status_post'] ?? 'aktif';
    $class_aktif = $status_post == 'aktif' ? 'btn-success' : 'btn-secondary';
    $class_archive = $status_post == 'arsip' ? 'btn-success' : 'btn-secondary';
    $class_selesai = $status_post == 'selesai' ? 'btn-success' : 'btn-secondary';
    // Number increament
    $number = ($paged - 1) * $post_per_page;
    $number = $number + 1;
    $jabatan_staff = get_user_meta($current_user->ID, 'jabatan', true);

    // Cetak peran pengguna yang saat ini masuk
    // echo 'Peran pengguna saat ini: ' . implode( ', ', $current_user->roles );
    if (!(current_user_can('administrator') || current_user_can('editor'))) {
        return 'Silahkan login sebagai administrator untuk melihat data.';
    }

    ?>
    <!-- <div class="table-responsive m-3"> -->
    <div class="container">
        <div class="mb-2 row mx-0">
            <div class="col-sm-2 px-0 ps-1">
                <a class="btn btn-success btn-sm text-white d-block me-1" href="<?php echo get_site_url(); ?>/kelola-prosses-kerja/">Tambah Order</a>
            </div>
            <div class="col-sm-3 d-md-flex my-3 my-md-0 px-1">
                <!-- search form -->
                <form action="?" method="get" class="d-flex">
                    <input class="form-control form-control-sm me-2" type="search" placeholder="Cari no order" aria-label="Cari" value="<?php echo $s; ?>" name="filter">
                    <button class="btn btn-sm btn-outline-success" type="submit">Search</button>
                </form>
            </div>
            <div class="col-sm-7 pe-1 d-flex justify-content-between text-sm-end text-right">
                <div class="d-flex justify-content-start align-items-center">
                    <?php
                    $konsumen = $_GET['konsumen'] ?? '';
                    $get_kategori = $_GET['kategori'] ?? '';
                    if ($konsumen) {
                        echo '<b>Filter:</b> Tampilkan data dari <b class="mx-2">' . get_post_meta($konsumen, '_customer_data_nama_lengkap', true) . '</b>';
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
                <div class="d-flex">
                    <a href="?status_post=aktif" type="button" class="btn btn-sm text-white <?php echo $class_aktif; ?>">Aktif</a>
                    <a href="?status_post=arsip" type="button" class="btn btn-sm ms-1 text-white <?php echo $class_archive; ?>">Arsip</a>
                    <a href="?status_post=selesai" type="button" class="btn btn-sm ms-1 text-white btn-danger <?php echo $class_selesai; ?>">Selesai</a>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive m-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="bg-blue text-white" scope="col">No.</th>
                    <th class="bg-blue text-white" scope="col" style="white-space: nowrap;">No Order</th>
                    <th class="bg-blue text-white" scope="col" style="min-width: 120px;">Tgl Order</th>
                    <th class="bg-blue text-white" scope="col" style="min-width: 200px;">Pelanggan</th>
                    <th class="bg-blue text-white" scope="col" style="min-width: 140px;">Pembayaran</th>
                    <!-- Sembunyikan biaya hanya tampil pada admin saja selain admin tidak ditampilkan  -->
                    <!-- Tampil hanya di admin dan keuangan -->
                    <?php if (current_user_can('administrator') || $jabatan_staff == 'keuangan'): ?>
                        <th class="bg-blue text-white" scope="col" style="white-space: nowrap;">Biaya Notaris</th>
                    <?php endif; ?>
                    <th class="bg-blue text-white" scope="col">Progres</th>
                    <th class="bg-blue text-white" scope="col">Kategori</th>
                    <th class="bg-blue text-white" scope="col" style="white-space: nowrap;">Nilai BPHTB</th>
                    <th class="bg-blue text-white" scope="col" style="white-space: nowrap;">Nilai SSP</th>
                    <th class="bg-blue text-white" scope="col">Keterangan</th>
                    <th class="bg-blue text-white" scope="col">Petugas</th>
                    <?php if ($status_post != 'selesai'): ?>
                        <th class="bg-blue text-white text-end" scope="col">Action</th>
                    <?php endif; ?>
                    <?php if ($status_post == 'selesai'): ?>
                        <th class="bg-blue text-white text-end" style="white-space: nowrap;" scope="col">Lihat Data</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $status_post = isset($_GET['status_post']) ? $_GET['status_post'] : 'aktif';
                $args = array(
                    'post_type' => 'draft_kerja',
                    'paged' => $paged,
                    'posts_per_page' => $post_per_page,
                    'order' => 'ASC',
                    's' => $s,
                    'meta_query' => array(
                        'relation' => 'AND',
                    )
                );

                if ($status_post == 'arsip') {
                    $args['meta_query'][] = array(
                        'key' => 'job_desk_status',
                        'value' => 'arsip',
                        'compare' => '='
                    );
                } elseif ($status_post == 'selesai') {
                    $args['meta_query'][] = array(
                        'key' => 'job_desk_status',
                        'value' => 'selesai',
                        'compare' => '='
                    );
                } else {
                    $args['meta_query'][] = array(
                        'key' => 'job_desk_status',
                        'value' => array('arsip', 'selesai'),
                        'compare' => 'NOT IN'
                    );
                }



                // Tambahkan kondisi untuk 'konsumen' jika tersedia
                if (isset($_GET['konsumen'])) {
                    $args['meta_query'][] = array(
                        'key' => 'customer_select',
                        'value' => sanitize_text_field($_GET['konsumen']),
                        'compare' => '=' // Opsional, '=' adalah default value dari 'compare'
                    );
                }
                // print_r($args);
                // Buat query
                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();


                        $job_desk_posts = get_posts(array(
                            'post_type'      => 'job_desk',
                            'posts_per_page' => -1,
                            'meta_key'       => 'job_desk_draft_kerja',
                            'meta_value'     => $post->ID,
                            'order-by'       => 'date',
                            'order'          => 'DESC',
                        ));

                        $dokumen = get_posts(array(
                            'post_type' => 'dokumen',
                            'post_status' => 'publish',
                            'posts_per_page' => -1,
                            'meta_query' => array(
                                array(
                                    'key' => 'id_order',
                                    'value' => $post->ID,
                                    'compare' => '='
                                )
                            )
                        ));

                        // Menghitung total job_desk posts
                        $total_job_desk_posts = count($job_desk_posts);

                        // Menampilkan job_desk posts
                        $selesai = [];
                        foreach ($job_desk_posts as $job_desk_post) {
                            $status = get_post_meta($job_desk_post->ID, 'job_desk_status', true);
                            if ($status == 'Selesai') {
                                $selesai[] = $job_desk_post->ID;
                            }
                        }
                        if ($total_job_desk_posts > 0 && $total_job_desk_posts == count($selesai)) {
                            $progres = '<span class="badge bg-success">Selesai</span>';
                            update_post_meta($post->ID, 'job_desk_status', 'arsip');
                            // skip the rest of the loop
                            if (count($dokumen) > 0) {
                                update_post_meta($post->ID, 'job_desk_status', 'selesai');
                            }
                        } else {
                            update_post_meta($post->ID, 'job_desk_status', 'aktif');
                            $progres = '<span data-bs-toggle="tooltip" data-bs-title="' . count($selesai) . '/' . $total_job_desk_posts . '" class="badge bg-warning">Dalam Proses</span>';
                        }
                ?>
                        <tr>
                            <!-- Nomor -->
                            <td><?php echo $number++; ?></td>
                            <td style="width: 140px; white-space: nowrap">
                                <a href="<?php echo get_site_url(); ?>/jobdesk/?post_id=<?php echo $post->ID; ?>" class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                                        <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z" />
                                        <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z" />
                                    </svg>
                                    <?php the_title(); ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                $tgl = get_post_meta($post->ID, 'tanggal_order', true);

                                // Memeriksa apakah $tgl bukan kosong dan merupakan timestamp yang valid
                                if (!empty($tgl) && (is_numeric($tgl) || strtotime($tgl) !== false)) {
                                    // Jika $tgl adalah timestamp Unix
                                    if (is_numeric($tgl) && strlen($tgl) === 10) {
                                        $awal = new DateTime();
                                        $awal->setTimestamp((int)$tgl);
                                    } else {
                                        // Jika $tgl adalah format tanggal yang dapat dibaca
                                        $awal = new DateTime($tgl);
                                    }

                                    $akhir = new DateTime();

                                    // Menghitung selisih hari
                                    $selisih = $awal->diff($akhir);
                                    $hari = $selisih->d . ' hari';
                                    $warna = ($selisih->d >= 30) ? 'danger' : ($selisih->d >= 10 ? 'warning' : 'success');

                                    echo '<span data-bs-toggle="tooltip" data-bs-title="' . $hari . '" class="badge bg-' . $warna . '">' . $awal->format("d m Y") . '</span>';
                                } else {
                                    echo ''; // Atau bisa ditambahkan pesan untuk tanggal tidak valid
                                }
                                ?>

                            </td>
                            <td style="max-width: 200px;">
                                <?php
                                $customer = get_post_meta($post->ID, 'customer_select', true);
                                $nama = get_post_meta($customer, '_customer_data_nama_lengkap', true);
                                echo '<a href="?konsumen=' . $customer . '">';
                                echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                                <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
                                <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
                            </svg>';
                                echo $nama . '</a>';
                                //echo '<small>('.get_post_meta($customer, '_customer_data_whatsapp', true).')</small>';
                                ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo get_post_meta($customer, '_customer_data_whatsapp', true); ?>
                                    <br>
                                    <?php echo get_post_meta($customer, '_customer_data_alamat', true); ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $pembayaran = get_post_meta($post->ID, 'jenis_pembayaran', true);

                                if ($pembayaran == 'tunai') {
                                    echo 'Tunai';
                                } else if ($pembayaran == 'transfer') {
                                    echo 'Transfer';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <?php if (current_user_can('administrator')): ?>
                                <td style="min-width:150px;">
                                    <?php
                                    if (isset($post)) {
                                        $biaya_transaksi = get_post_meta($post->ID, 'biaya_transaksi', true);
                                        $biaya_transfer = get_post_meta($post->ID, 'biaya_transfer', true);
                                        $dibayar = get_post_meta($post->ID, 'dibayar', true);

                                        // Jika nilai kosong atau tidak valid, set ke 0
                                        $biaya_transaksi = preg_replace('/[^0-9]/', '', $biaya_transaksi);
                                        $biaya_transfer = preg_replace('/[^0-9]/', '', $biaya_transfer);
                                        $dibayar = preg_replace('/[^0-9]/', '', $dibayar);

                                        $total_biaya = (intval($biaya_transaksi) + intval($biaya_transfer));

                                        // Format output
                                        $formatted_total_biaya = $total_biaya ? '<a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" data-bs-custom-class="text-start"
        title="Dibayar Rp. ' . number_format(intval($dibayar), 2, ',', '.') . '<br><b>Kekurangan Rp. ' . number_format((intval($total_biaya) - intval($dibayar)), 2, ',', '.') . '</b>">Rp. ' . number_format(intval($total_biaya), 2, ',', '.') . '</a>' : '';
                                        $kurang = (intval($dibayar) && intval($total_biaya)) ? 'Rp. ' . number_format((intval($total_biaya) - intval($dibayar)), 2, ',', '.') : '';
                                        echo $formatted_total_biaya;
                                        // echo $kurang ? '<br><small>-'.$kurang.'</small>' : '';
                                    } else {
                                        echo 'Post tidak ditemukan.';
                                    }
                                    ?>
                                </td>
                            <?php endif; ?>
                            <td>
                                <?php
                                echo $progres;
                                echo isset($job_desk_posts[0]->ID) ? '<br><small>' . get_post_meta($job_desk_posts[0]->ID, 'judul_job_desk', true) : '' . '</small>';
                                ?>
                            </td>
                            <td>
                                <small>
                                    <?php
                                    $kategori = get_post_meta($customer, '_customer_data_kategori', true);
                                    echo $kategori ? $kategori : '-';
                                    ?>
                                    <?php
                                    if ($kategori == 'Bank') {
                                        echo get_post_meta($customer, '_customer_data_bank', true);
                                    }
                                    ?>
                                </small>
                            </td>
                            <!-- Tampilkan nilai BPHTB -->
                            <td>
                                <?php
                                $nilai_bphtb = get_post_meta($customer, '_customer_data_pajak_pembeli', true);

                                if ($nilai_bphtb && is_numeric($nilai_bphtb)) {
                                    echo 'Rp ' . number_format($nilai_bphtb, 0, ',', '.');
                                } else {
                                    echo '-';
                                }
                                ?><br />
                            </td>
                            <!-- Tampilkan nilai SSP -->
                            <td>
                                <?php
                                $nilai_ssp = get_post_meta($customer, '_customer_data_pajak_penjual', true);

                                if ($nilai_ssp && is_numeric($nilai_ssp)) {
                                    echo 'Rp ' . number_format($nilai_ssp, 0, ',', '.');
                                } else {
                                    echo '-';
                                }
                                ?><br />
                            </td>
                            <td style="white-space: nowrap;">
                                <?php echo get_post_meta($post->ID, 'layanan', true); ?><br />
                            </td>
                            <td style="white-space: nowrap;">
                                <small>
                                    <?php
                                    // Mendapatkan ID penulis berdasarkan ID postingan
                                    $author_id = get_post_field('post_author', $post->ID);

                                    // Mendapatkan nama lengkap penulis berdasarkan ID penulis
                                    $author_name = get_the_author_meta('display_name', $author_id);

                                    // Menampilkan nama lengkap penulis
                                    echo $author_name;
                                    ?>
                                </small>
                            </td>
                            <?php if ($status_post != 'selesai'): ?>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end">
                                        <?php if ($status_post == 'aktif'): ?>
                                            <a class="btn btn-info btn-sm text-white <?php echo $class_archive; ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Job Desk" href="<?php echo get_site_url(); ?>/jobdesk/?post_id=<?php echo $post->ID; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" class="bi bi-journal-text" viewBox="0 0 16 16">
                                                    <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5" />
                                                    <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2" />
                                                    <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z" />
                                                </svg>
                                            </a>

                                            <a class="btn btn-info ms-1 btn-sm text-white tooltips" href="<?php echo get_site_url(); ?>/kelola-prosses-kerja/?post_id=<?php echo $post->ID; ?>">
                                                <span class="<?php echo $class_archive; ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                                    </svg>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($status_post == 'arsip'): ?>
                                            <a class="ms-1 btn btn-info btn-sm text-white tooltips <?php echo $class_aktif; ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Upload" href="<?php echo get_site_url(); ?>/kelola-dokumen/?draft_kerja_id=<?php echo $post->ID; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-up" viewBox="0 0 16 16">
                                                    <path d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z" />
                                                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        <!-- Button trigger modal -->
                                        <a class="ms-1 btn btn-info btn-sm text-white <?php echo $class_aktif; ?>" data-bs-toggle="modal" data-bs-target="#view-post-<?php echo $post->ID; ?>" href="<?php echo get_site_url(); ?>/jobdesk/?post_id=<?php echo $post->ID; ?>">
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Lihat Data">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-eye" viewBox="0 0 16 16">
                                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                                </svg>
                                            </span>
                                        </a>
                                        <!-- Modal -->
                                        <div class="modal fade" id="view-post-<?php echo $post->ID; ?>" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                                            <?php
                                            $tanggal_order = get_post_meta($post->ID, 'tanggal_order', true);
                                            $tanggal_order = $tanggal_order ? date("d m Y", strtotime($tanggal_order)) : '';
                                            $layanan_order = get_post_meta($post->ID, 'layanan', true) ?: '';
                                            $customer = get_post_meta($post->ID, 'customer_select', true);
                                            $nama = get_post_meta($customer, '_customer_data_nama_lengkap', true);
                                            $whatsapp = get_post_meta($customer, '_customer_data_whatsapp', true);
                                            $biaya_transaksi = get_post_meta($customer, '_customer_data_nilai_transaksi', true);
                                            $biaya_transaksi = preg_replace('/[^0-9]/', '', $biaya_transaksi);
                                            $harga_real = get_post_meta($customer, '_customer_data_harga_real', true);
                                            $harga_real = preg_replace('/[^0-9]/', '', $harga_real);
                                            $harga_kesepakatan = get_post_meta($customer, '_customer_data_harga_kesepakatan', true);
                                            $harga_kesepakatan = preg_replace('/[^0-9]/', '', $harga_kesepakatan);
                                            $alamat = get_post_meta($customer, '_customer_data_alamat', true);
                                            ?>
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="viewModalLabel"><?php echo '#' . $post->post_title; ?></h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-12 text-start">
                                                                <ul class="list-group">
                                                                    <li class="list-group-item"><b class="d">Tangal Order : </b> <?php echo $tanggal_order; ?></li>
                                                                    <li class="list-group-item"><b>Order : </b><?php echo $layanan_order; ?></li>
                                                                    <li class="list-group-item"><b>Nama Customer : </b> <?php echo $nama; ?></li>
                                                                    <li class="list-group-item"><b>Whatsapp : </b> <?php echo $whatsapp; ?></li>
                                                                    <!-- Hanya tampil di admin dan keuangan -->
                                                                    <?php if (current_user_can('administrator') || $jabatan_staff == 'keuangan'): ?>
                                                                        <li class="list-group-item"><b>Biaya Notaris: </b> Rp <?php echo $biaya_transaksi ? number_format($biaya_transaksi, 0, ',', '.') : '-'; ?></li>
                                                                        <li class="list-group-item"><b>Harga Real: </b> Rp <?php echo $harga_real ? number_format($harga_real, 0, ',', '.') : '-'; ?></li>
                                                                        <li class="list-group-item"><b>Harga Kesepakatan: </b> Rp <?php echo $harga_kesepakatan ? number_format($harga_kesepakatan, 0, ',', '.') : '-'; ?></li>
                                                                    <?php endif; ?>
                                                                    <li class="list-group-item"><b>Alamat : </b> <?php echo $alamat; ?></li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-12">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <a class="btn btn-danger btn-sm btn-danger text-white ms-1" href="<?php echo ($post->ID > 0) ? wp_nonce_url(admin_url('admin-post.php?action=delete_post&redirect=' . get_site_url() . '/prosses-kerja/&post_id=' . $post->ID), 'delete_post_' . $post->ID) : ''; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                                <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <?php if ($status_post == 'selesai'): ?>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-success btn-sm btn-success text-white w-100" style="white-space: nowrap;" href="#" data-bs-toggle="modal" data-bs-target="#exampleModalData<?php echo $post->ID; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-clipboard-data" viewBox="0 0 16 16">
                                                <path d="M4 11a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm6-4a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zM7 9a1 1 0 0 1 2 0v3a1 1 0 1 1-2 0z" />
                                                <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z" />
                                                <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z" />
                                            </svg> Lihat
                                        </a>
                                    </div>
                                    <!-- Isi modal -->
                                    <div class="modal fade" id="exampleModalData<?php echo $post->ID; ?>" tabindex="-1" aria-labelledby="exampleModalData" aria-hidden="true">
                                        <?php
                                        $tanggal_order = get_post_meta($post->ID, 'tanggal_order', true);
                                        $tanggal_order = $tanggal_order ? date("d m Y", strtotime($tanggal_order)) : '';
                                        $layanan_order = get_post_meta($post->ID, 'layanan', true) ?: '';
                                        $customer = get_post_meta($post->ID, 'customer_select', true);
                                        $nama = get_post_meta($customer, '_customer_data_nama_lengkap', true);
                                        $whatsapp = get_post_meta($customer, '_customer_data_whatsapp', true);
                                        $biaya_transaksi = get_post_meta($customer, '_customer_data_nilai_transaksi', true);
                                        $biaya_transaksi = preg_replace('/[^0-9]/', '', $biaya_transaksi);
                                        $harga_real = get_post_meta($customer, '_customer_data_harga_real', true);
                                        $harga_real = preg_replace('/[^0-9]/', '', $harga_real);
                                        $harga_kesepakatan = get_post_meta($customer, '_customer_data_harga_kesepakatan', true);
                                        $harga_kesepakatan = preg_replace('/[^0-9]/', '', $harga_kesepakatan);
                                        $alamat = get_post_meta($customer, '_customer_data_alamat', true);
                                        ?>
                                        <div class="modal-dialog modal-xl modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Data Order #<?php echo $post->post_title; ?></h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo do_shortcode('[tabel-dokumen draft_kerja_id="' . $post->ID . '"]') ?>
                                                    <div class="text-start">
                                                        <ul class="list-group">
                                                            <li class="list-group-item"><b>Tanggal Order: </b> <?php echo $tanggal_order ?: ' -'; ?></li>
                                                            <li class="list-group-item"><b>Order: </b> <?php echo $layanan_order ?: ' -'; ?></li>
                                                            <li class="list-group-item"><b>Nama Customer: </b> <?php echo $nama ?: ' -'; ?></li>
                                                            <li class="list-group-item"><b>Whatsapp: </b> <?php echo $whatsapp ?: ' -'; ?></li>
                                                            <!-- Hanya tampil di admin dan keuangan -->
                                                            <?php if (current_user_can('administrator') || $jabatan_staff == 'keuangan'): ?>
                                                                <li class="list-group-item"><b>Biaya Notaris: </b> Rp <?php echo $biaya_transaksi ? number_format($biaya_transaksi, 0, ',', '.') : '-'; ?></li>
                                                                <li class="list-group-item"><b>Harga Real: </b> Rp <?php echo $harga_real ? number_format($harga_real, 0, ',', '.') : '-'; ?></li>
                                                                <li class="list-group-item"><b>Harga Kesepakatan: </b> Rp <?php echo $harga_kesepakatan ? number_format($harga_kesepakatan, 0, ',', '.') : '-'; ?></li>
                                                            <?php endif; ?>
                                                            <li class="list-group-item"><b>Alamat: </b> <?php echo $alamat ?: ' -'; ?></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <tr>
                        <td colspan="13">Tidak ada Order yang ditemukan</td>
                    </tr>
                <?php
                endif;
                ?>
            </tbody>
        </table>
    </div>
    <?php
    // echo '<pre>' . print_r($query, 1) . '</pre>';
    pagination_bootstrap($query->found_posts, $post_per_page);
    ?>
    <!-- Modal -->
    <div class="modal fade" id="exampleModalPrint" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Tanda Terima</h1>
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
    <!-- </div> -->
<?php
    return ob_get_clean();
}
add_shortcode('draft_kerja', 'draft_kerja_shortcode');





function data_konsumen()
{
    ob_start();
    $paged = $_GET['halaman'] ?? '1';
    $post_per_page = 20;
    // Number increment
    $number = 1;
    $current_user = wp_get_current_user();
    $jabatan_staff = get_user_meta($current_user->ID, 'jabatan', true);

    if (!(current_user_can('administrator') || current_user_can('editor'))) {
        return 'Silahkan login sebagai administrator untuk melihat data.';
    }
    global $post;
    $search = $_GET['search'] ?? '';
    $hp = $_GET['hp'] ?? '';
?>
    <div class="mb-2 d-flex">
        <a class="btn btn-sm btn-success text-white" style="white-space: nowrap;" href="<?php echo get_site_url(); ?>/kelola-konsumen/">Tambah Konsumen</a>
        <form action="" method="get" class="d-flex justify-content-end ms-3">
            <input type="text" name="search" class="form-control form-control-sm d-inline-block" value="<?php echo $search; ?>" placeholder="Nama Konsumen">
            <button type="submit" class="btn btn-sm btn-primary text-white">Cari</button>
        </form>
        <form action="" method="get" class="d-flex justify-content-end ms-3">
            <input type="text" name="hp" value="<?php echo $hp; ?>" class="form-control form-control-sm d-inline-block" placeholder="No. HP">
            <button type="submit" class="btn btn-sm btn-primary text-white">Cari</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="bg-blue text-white" scope="col">No</th>
                    <th class="bg-blue text-white" scope="col">Nama</th>
                    <th class="bg-blue text-white" scope="col">No. HP</th>
                    <th class="bg-blue text-white" style="max-width: 300px" scope="col">Alamat</th>
                    <th class="bg-blue text-white" scope="col">Kategori</th>
                    <th class="bg-blue text-white text-end" scope="col">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $args = array(
                    'post_type' => 'data_pelanggan',
                    'paged' => $paged,
                    'posts_per_page' => $post_per_page,
                );

                // Jika ada pencarian, tambahkan meta_query
                if ($search) {
                    // Jika ingin mencari juga di judul dan konten
                    $args['s'] = $search;
                }

                if ($hp) {
                    $args['meta_query'] = array(
                        array(
                            'key' => '_customer_data_whatsapp',
                            'value' => $hp,
                            'compare' => 'LIKE',
                        ),
                    );
                }

                // echo '<pre>' . print_r($args, 1) . '</pre>';
                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                ?>
                        <tr>
                            <td class="align-middle"><?php echo $number++ . '.'; ?></td>
                            <td class="align-middle" style="white-space: nowrap;">
                                <?php echo get_post_meta($post->ID, '_customer_data_nama_lengkap', true); ?>
                            </td>
                            <td style="white-space: nowrap;"><?php echo get_post_meta($post->ID, '_customer_data_whatsapp', true); ?></td>
                            <td style="max-width: 300px;"><?php echo get_post_meta($post->ID, '_customer_data_alamat', true); ?></td>
                            <td style=" white-space: nowrap;">
                                <?php
                                $kategori = get_post_meta($post->ID, '_customer_data_kategori', true);
                                $bank = get_post_meta($post->ID, '_customer_data_bank', true);
                                $pekerjaan1 = get_post_meta($post->ID, '_customer_data_pekerjan', true);
                                $pekerjaan2 = get_post_meta($post->ID, '_customer_data_pekerjan_2', true);
                                $pekerjaan_lainnya = get_post_meta($post->ID, '_customer_data_pekerjaan_lainnya', true);
                                if ($kategori == 'Bank') {
                                    echo $kategori . ': ' . $bank;
                                } else if ($kategori == 'Pribadi') {
                                    echo $kategori . ': ' .  $pekerjaan1 . ', ' . $pekerjaan2 . ', ' . $pekerjaan_lainnya;
                                } else {
                                    echo '- <small><i>(Kategori belum ditentukan)</i></small>';
                                }
                                // $konsumen_kategori = wp_get_post_terms($post->ID, 'konsumen_kategori');
                                // if (!empty($konsumen_kategori)) {
                                //     foreach ($konsumen_kategori as $kategori) {
                                //         echo '<span class="badge rounded-pill text-bg-light">' . $kategori->name . '</span>';
                                //     }
                                // }
                                // echo implode(',', $konsumen_kategori);
                                ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end">
                                    <!-- Modal Trigger -->
                                    <a class="btn btn-primary btn-sm text-white tooltips ms-1" data-bs-toggle="modal" data-bs-target="#konsumenModal<?php echo $post->ID; ?>">
                                        <span class="tooltips" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Lihat Data Konsumen">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-eye" viewBox="0 0 16 16">
                                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                            </svg>
                                        </span>
                                    </a>

                                    <!-- Modal Konsumen -->
                                    <?php
                                    // Ambil data pengguna yang membuat post
                                    $user = get_userdata($post->post_author);

                                    // Definisikan variabel jabatan, address, dan status (jika diperlukan)
                                    $nama = get_post_meta($post->ID, '_customer_data_nama_lengkap', true);
                                    $whatsapp = get_post_meta($post->ID, '_customer_data_whatsapp', true);
                                    $kategori = wp_get_post_terms($post->ID, '_customer_data_kategori');
                                    $alamat = get_post_meta($post->ID, '_customer_data_alamat', true);
                                    $pekerjaan1 = get_post_meta($post->ID, '_customer_data_pekerjan', true);
                                    $pekerjaan2 = get_post_meta($post->ID, '_customer_data_pekerjan_2', true);
                                    $sertifikat = get_post_meta($post->ID, '_customer_data_sertifikat', true);
                                    $nilai_transaksi = get_post_meta($post->ID, '_customer_data_nilai_transaksi', true);
                                    // Pastikan $nilai_transaksi adalah angka
                                    if (is_numeric($nilai_transaksi)) {
                                        // Format nilai transaksi tanpa 'Rp' terlebih dahulu
                                        $nilai_transaksi = number_format($nilai_transaksi, 0, ',', '.');

                                        // Tambahkan 'Rp ' di depannya setelah diformat
                                        $nilai_transaksi = 'Rp ' . $nilai_transaksi;
                                    } else {
                                        // Jika bukan angka, tampilkan nilai default
                                        $nilai_transaksi = '-';
                                    }
                                    $harga_real = get_post_meta($post->ID, '_customer_data_harga_real', true);
                                    // Pastikan $harga_real adalah angka
                                    if (is_numeric($harga_real)) {
                                        // Format harga real tanpa 'Rp' terlebih dahulu
                                        $harga_real = number_format($harga_real, 0, ',', '.');

                                        // Tambahkan 'Rp ' di depannya setelah diformat
                                        $harga_real = 'Rp ' . $harga_real;
                                    } else {
                                        // Jika bukan angka, tampilkan nilai default
                                        $harga_real = '-';
                                    }
                                    $harga_kesepakatan = get_post_meta($post->ID, '_customer_data_harga_kesepakatan', true);
                                    // Pastikan $harga_real adalah angka
                                    if (is_numeric($harga_kesepakatan)) {
                                        // Format harga real tanpa 'Rp' terlebih dahulu
                                        $harga_kesepakatan = number_format($harga_kesepakatan, 0, ',', '.');

                                        // Tambahkan 'Rp ' di depannya setelah diformat
                                        $harga_kesepakatan = 'Rp ' . $harga_kesepakatan;
                                    } else {
                                        // Jika bukan angka, tampilkan nilai default
                                        $harga_kesepakatan = '-';
                                    }
                                    $data_pajak_pembeli = get_post_meta($post->ID, '_customer_data_pajak_pembeli', true);
                                    // Pastikan $data_pajak_pembeli adalah angka
                                    if (is_numeric($data_pajak_pembeli)) {
                                        // Format data pajak pembeli tanpa 'Rp' terlebih dahulu
                                        $data_pajak_pembeli = number_format($data_pajak_pembeli, 0, ',', '.');

                                        // Tambahkan 'Rp ' di depannya setelah diformat
                                        $data_pajak_pembeli = 'Rp ' . $data_pajak_pembeli;
                                    } else {
                                        // Jika bukan angka, tampilkan nilai default
                                        $data_pajak_pembeli = '-';
                                    }
                                    $data_pajak_penjual = get_post_meta($post->ID, '_customer_data_pajak_penjual', true);
                                    // Pastikan $data_pajak_penjual adalah angka
                                    if (is_numeric($data_pajak_penjual)) {
                                        // Format data pajak penjual tanpa 'Rp' terlebih dahulu
                                        $data_pajak_penjual = number_format($data_pajak_penjual, 0, ',', '.');

                                        // Tambahkan 'Rp ' di depannya setelah diformat 
                                        $data_pajak_penjual = 'Rp ' . $data_pajak_penjual;
                                    } else {
                                        // Jika bukan angka, tampilkan nilai default
                                        $data_pajak_penjual = '-';
                                    }
                                    ?>
                                    <div class="modal fade" id="konsumenModal<?php echo $post->ID; ?>" tabindex="-1" aria-labelledby="konsumenModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="konsumenModalLabel">Informasi Data Konsumen</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <ol class="list-group text-start">
                                                                <li class="list-group-item text-center" style="background-color: #4EA9F5;">
                                                                    <div class="fw-bold text-white">Detail Konsumen</div>
                                                                </li>
                                                                <li class="list-group-item">
                                                                    <div class="fw-bold">Nama Lengkap</div>
                                                                    <?php echo $nama; ?>
                                                                </li>
                                                                <li class="list-group-item">
                                                                    <div class="fw-bold">WhatsApp </div>
                                                                    <?php echo $whatsapp; ?>
                                                                </li>
                                                                <li class="list-group-item">
                                                                    <div class="fw-bold">Kategori</div>
                                                                    <?php
                                                                    $kategori = get_post_meta($post->ID, '_customer_data_kategori', true);
                                                                    $bank = get_post_meta($post->ID, '_customer_data_bank', true);
                                                                    $pekerjaan1 = get_post_meta($post->ID, '_customer_data_pekerjan', true);
                                                                    $pekerjaan2 = get_post_meta($post->ID, '_customer_data_pekerjan_2', true);
                                                                    $pekerjaan_lainnya = get_post_meta($post->ID, '_customer_data_pekerjaan_lainnya', true);

                                                                    if ($kategori == 'Bank') {
                                                                        echo $kategori . ': ' . $bank;
                                                                    } else if ($kategori == 'Pribadi') {
                                                                        echo $kategori . ': ' .  $pekerjaan1 . ', ' . $pekerjaan2 . ', ' . $pekerjaan_lainnya;
                                                                    } else {
                                                                        echo '- <small><i>(Kategori belum ditentukan)</i></small>';
                                                                    }
                                                                    ?>
                                                                </li>
                                                                <li class="list-group-item">
                                                                    <div class="fw-bold">Alamat</div>
                                                                    <?php echo $alamat; ?>
                                                                </li>
                                                                <li class="list-group-item">
                                                                    <div class="fw-bold">Sertifikat</div>
                                                                    <?php echo !empty(get_post_meta($post->ID, '_customer_data_sertifikat', true)) ? get_post_meta($post->ID, '_customer_data_sertifikat', true) : '-'; ?>
                                                                </li>
                                                                <?php if (current_user_can('administrator') || $jabatan_staff == 'keuangan'): ?>
                                                                    <li class="list-group-item">
                                                                        <div class="fw-bold">Nilai Transaksi</div>
                                                                        <?php echo !empty(get_post_meta($post->ID, '_customer_data_nilai_transaksi', true)) ? get_post_meta($post->ID, '_customer_data_nilai_transaksi', true) : '-'; ?>
                                                                    </li>
                                                                    <li class="list-group-item">
                                                                        <div class="fw-bold">Harga Real</div>
                                                                        <?php echo !empty(get_post_meta($post->ID, '_customer_data_harga_real', true)) ? get_post_meta($post->ID, '_customer_data_harga_real', true) : '-'; ?>
                                                                    </li>
                                                                    <li class="list-group-item">
                                                                        <div class="fw-bold">Harga Kesepakatan</div>
                                                                        <?php echo !empty(get_post_meta($post->ID, '_customer_data_harga_kesepakatan', true)) ? get_post_meta($post->ID, '_customer_data_harga_kesepakatan', true) : '-'; ?>
                                                                    </li>
                                                                    <li class="list-group-item">
                                                                        <div class="fw-bold">Data Pajak Pembeli</div>
                                                                        <?php echo !empty(get_post_meta($post->ID, '_customer_data_pajak_pembeli', true)) ? get_post_meta($post->ID, '_customer_data_pajak_pembeli', true) : '-'; ?>
                                                                    </li>
                                                                    <li class="list-group-item">
                                                                        <div class="fw-bold">Data Pajak Penjual</div>
                                                                        <?php echo !empty(get_post_meta($post->ID, '_customer_data_pajak_penjual', true)) ? get_post_meta($post->ID, '_customer_data_pajak_penjual', true) : '-'; ?>
                                                                    </li>
                                                                <?php endif; ?>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="btn btn-sm btn-primary text-white ms-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit Data" href="<?php echo get_site_url(); ?>/kelola-konsumen/?post_id=<?php echo $post->ID; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-person-fill-gear" viewBox="0 0 16 16">
                                            <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0" />
                                        </svg>
                                    </a>
                                    <a class="btn btn-primary btn-sm text-white ms-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Tambah Order" href="<?php echo get_site_url(); ?>/kelola-prosses-kerja/?user_id=<?php echo $post->ID; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2" />
                                        </svg>
                                    </a>
                                    <a class="btn btn-primary btn-sm text-white ms-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Lihat Prosses Kerja" href="<?php echo get_site_url(); ?>/prosses-kerja/?konsumen=<?php echo $post->ID; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-card-checklist" viewBox="0 0 16 16">
                                            <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z" />
                                            <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0" />
                                        </svg>
                                    </a>
                                    <a class="btn btn-danger btn-sm text-white ms-1" href="<?php echo ($post->ID > 0) ? wp_nonce_url(admin_url('admin-post.php?action=delete_post&redirect=' . get_site_url() . '/data-konsumen/&post_id=' . $post->ID), 'delete_post_' . $post->ID) : ''; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                            <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
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
        <?php
        // echo '<pre>' . print_r($query, 1) . '</pre>';
        pagination_bootstrap($query->found_posts, $post_per_page);
        ?>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('data_konsumen', 'data_konsumen');




add_shortcode('crud_taxonomy', 'crud_taxonomy_shortcode');

function crud_taxonomy_shortcode()
{
    if (!(current_user_can('administrator') || current_user_can('editor'))) {
        return '<center>Silahkan <a class="btn btn-dark text-white" href="' . get_site_url() . '/login/"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-person-fill" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg> login</a> untuk melihat data </center>';
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
            <a class="btn btn-sm btn-dark text-white ms-1" href="' . get_site_url() . '/data-konsumen/">Konsumen</a>
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





function pp()
{
    $user_id = get_current_user_id();

    // Set user ID
    um_fetch_user($user_id);

    // Returns current user avatar
    $avatar_uri = get_user_meta($user_id, 'poto_profil', true) ?? 'https://asistennotaris.com/wp-content/uploads/2024/10/user.png';


    // Returns default UM avatar, e.g. https://ultimatemember.com/wp-content/uploads/2015/01/default_avatar.jpg
    $default_avatar_uri = (filter_var($avatar_uri, FILTER_VALIDATE_URL) === FALSE) ? 'https://asistennotaris.com/wp-content/uploads/2024/04/user-2.png' : $avatar_uri;
    $img = '<img src="' . $default_avatar_uri . '" alt="" style="width:30px;height=30px;border-radius:50%;">';
    return $img;
}
add_shortcode('pp', 'pp');



// Fungsi untuk membuat shortcode tombol "Kembali"
function back_button_shortcode()
{
    // Mengembalikan kode HTML untuk tombol "Kembali" dengan JavaScript inline
    return '<button class="btn btn-sm btn-primary text-white" onclick="window.history.back()">Kembali</button>';
}
// Mendaftarkan shortcode dengan nama 'back_button'
add_shortcode('back_button', 'back_button_shortcode');
