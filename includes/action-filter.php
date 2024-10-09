<?php
// Fungsi untuk menangani penghapusan pengguna
function delete_user_action()
{
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
            wp_redirect(get_site_url() . '/data-staff/');
            exit;
        } else {
            wp_die('Gagal menghapus pengguna.');
        }
    }
}

// Menangani aksi penghapusan pengguna setelah admin_post
add_action('admin_post_delete_user', 'delete_user_action');


function delete_post_action()
{
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



//regsiter page template
add_filter('template_include', 'vdc_register_page_template');
function vdc_register_page_template($template)
{

    if (is_singular()) {
        $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
        if ('print-notaris' === $page_template) {
            $template = plugin_dir_path(__FILE__) . 'page-print.php';
        }
    }

    return $template;
}
add_filter("theme_page_templates", 'vdc_templates_page');
function vdc_templates_page($post_templates)
{
    $post_templates['print-notaris'] = __('Print notaris', 'wss');
    return $post_templates;
}
