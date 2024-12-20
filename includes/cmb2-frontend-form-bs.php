<?php

/**
 * CMB2 Front-end Shortcode Bootstrap 5 
 *
 * Author: Adityathok
 * Version: 1.0.0
 * Author URI: https://github.com/adityathok
 * GitHub URI: https://github.com/adityathok/cmb2-frontend-form-bs
 */

class CMB2_Frontend_Form_Bs
{
    function initialize()
    {
        add_shortcode('cmb-form', array($this, 'form'));
        add_action('init', array($this, 'allow_subscriber_uploads'));
        add_action('pre_get_posts', array($this, 'restrict_media_library'));
    }

    /**
     * Shortcode to display a CMB2 form for a post ID.
     * Adding this shortcode to your WordPress editor would look something like this:
     *
     * [cmb-form id="test_metabox" post_id=2]
     *
     * The shortcode requires a metabox ID, and (optionally) can take
     * a WordPress post ID (or user/comment ID) to be editing.
     *
     * @param  array  $atts Shortcode attributes
     * @return string       Form HTML markup
     */
    function form($atts = array())
    {

        global $post;
        // Current user
        $user_id = get_current_user_id();

        // Use ID of metabox in wds_frontend_form_register
        $metabox_id = $atts['id'] ?? '';
        $post_attr_id = $atts['post_id'] ?? '';

        // since post ID will not exist yet, just need to pass it something
        if ($post_attr_id && $post_attr_id != '') {
            $object_id  = $post_attr_id;
        } else {
            $object_id  = isset($_GET['post_id']) ? absint($_GET['post_id']) : 'new-object-id';
        }

        // Get CMB2 metabox object
        $cmb = cmb2_get_metabox($metabox_id, $object_id);

        $show = true;

        if (empty($cmb))
            return 'Metabox ID not found';

        if (!(current_user_can('administrator') || current_user_can('editor'))) {
            return 'Silahkan Login sebagai admin';
        }

        // Get $cmb object_types
        $post_types = $cmb->prop('object_types');

        // Parse attributes. These shortcode attributes can be optionally overridden.
        $atts = shortcode_atts(array(
            'ID'            => isset($_GET['post_id']) ? absint($_GET['post_id']) : 0,
            'post_author'   => $user_id ? $user_id : 1,
            'post_status'   => 'publish',
            'post_type'     => reset($post_types),
        ), $atts, 'cmb-frontend-form');

        // Initiate our output variable
        $output = '';

        $new_id = $this->handle_submit($cmb, $atts);
        if ($new_id) {

            if (is_wp_error($new_id)) {
                // If there was an error with the submission, add it to our ouput.
                $output .= '<div class="alert alert-warning">' . sprintf(__('There was an error in the submission: %s', 'cmb2-post-submit'), '<strong>' . $new_id->get_error_message() . '</strong>') . '</div>';
            } else {
                if (isset($_POST['_customer_data_nama_lengkap'])) {
                    // Add notice of submission
                    $output .= '<div class="alert alert-success">Update data berhasil untuk pelanggan <b>' . get_post_meta($new_id, '_customer_data_nama_lengkap', true) . '</b><br> klik tombol dibawah jika tidak di redirect otomatis. <br><a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/kelola-prosses-kerja/?user_id=' . $new_id . '">Tambah Data Order</a></div>';
                    $output .= '<script>window.location.href = "' . get_site_url() . '/kelola-prosses-kerja/?user_id=' . $new_id . '";</script>';
                } elseif (isset($_POST['judul_job_desk']) && isset($_POST['job_desk_draft_kerja'])) {
                    // Add notice of submission
                    $output .= '<div class="alert alert-success">Update data <b>' . $_POST['judul_job_desk'] . '</b><br> klik tombol dibawah jika tidak di redirect otomatis. <br><a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/kelola-prosses-kerja/?user_id=' . $new_id . '">Tambah Data Order</a></div>';
                    $output .= '<script>window.parent.location.href = "' . get_site_url() . '/jobdesk/?post_id=' . $_POST['job_desk_draft_kerja'] . '";</script>';
                } elseif (isset($_POST['tanggal_order'])) {
                    // Add notice of submission
                    $output .= '<div class="alert alert-success">Update data berhasil.</b> klik tombol dibawah jika tidak di redirect otomatis. <br><a class="btn btn-sm btn-dark text-white" href="' . get_site_url() . '/tracking-order/?kode-layanan=' . $new_id . '">Jobdesk</a></div>';
                    $output .= '<script>window.location.href = "' . get_site_url() . '/jobdesk/?post_id=' . $new_id . '";</script>';
                } else {
                    $output .= '<div class="alert alert-success">Update data berhasil.</div>';
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
                    $full_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $output .= '<script>window.location.href = "' . $full_url . '";</script>';
                }
            }
        }

        // Get our form
        $form = cmb2_get_metabox_form($cmb, $object_id, array('save_button' => 'SUBMIT'));

        // Format our form use Bootstrap 5
        $styling = [
            'regular-text'              => 'regular-text form-control',
            'cmb2-text-small'           => 'cmb2-text-small form-control',
            'cmb2-text-medium'          => 'cmb2-text-medium form-control',
            'cmb2-timepicker'           => 'cmb2-timepicker form-control d-inline-block',
            'cmb2-datepicker'           => 'cmb2-datepicker d-inline-block w-100',
            'cmb2-text-money'           => 'cmb2-text-money form-control d-inline-block',
            'cmb2_textarea'             => 'cmb2_textarea form-control',
            'cmb2-textarea-small'       => 'cmb2-textarea-small form-control d-inline-block',
            'cmb2_select'               => 'cmb2_select form-select',
            'cmb2-upload-file regular-text'         => 'cmb2-upload-file regular-text d-block w-100 text-body-secondary',
            'type="radio" class="cmb2-option"'      => 'type="radio" class="cmb2-option form-check-input"',
            'type="checkbox" class="cmb2-option"'   => 'type="checkbox" class="cmb2-option form-check-input"',
            'class="button-primary"'                => 'class="button-primary btn btn-primary text-white"',
            'cmb2-metabox-description'              => 'cmb2-metabox-description fw-normal small',
            'class="cmb-th"'                        => 'class="cmb-th w-100 p-0"',
            'class="cmb-td"'                        => 'class="cmb-th w-100 p-0 pb-2"',
            'class="cmb-add-row"'                   => 'class="cmb-add-row text-end"',
            'button-secondary'                      => 'button-secondary btn-sm btn btn-outline-secondary',
            'cmb2-upload-button'                    => 'cmb2-upload-button float-end mt-1',
            'button-secondary btn-sm btn btn-outline-secondary cmb-remove-row-button'   => 'button-secondary btn btn-danger cmb-remove-row-button',
        ];
        foreach ($styling as $std => $newf) {
            $form = str_replace($std, $newf, $form);
        }
        if ($show) {
            $output .= $form;
        }
        return '<div class="card mx-auto p-2 p-md-3" style="max-width:500px;">' . $output . '</div>';
    }

    function handle_submit($cmb, $post_data = array())
    {

        // If no form submission, bail
        if (empty($_POST)) {
            return false;
        }

        // Fetch sanitized values
        $sanitized_values = $cmb->get_sanitized_values($_POST);

        // Set our post data arguments
        if (!empty($sanitized_values['_customer_data_nama_lengkap'])) {
            $title = $sanitized_values['_customer_data_nama_lengkap'];
        } else {
            $title = isset($_GET['post_id']) ? get_the_title($_GET['post_id']) : date('U') . rand(100, 999);
        }

        $post_data['post_title']   = $title;
        $post_data['post_content'] = '-';

        // Create the new post
        $new_submission_id = wp_insert_post($post_data, true);

        if (is_wp_error($new_submission_id)) {
            return $new_submission_id;
        }

        //thumbnail
        if (isset($sanitized_values['post_thumbnail_id']) && $sanitized_values['post_thumbnail_id']) {
            set_post_thumbnail($new_submission_id, $sanitized_values['post_thumbnail_id']);
        }

        unset($post_data['post_type']);
        unset($post_data['post_status']);

        // Loop through remaining (sanitized) data, and save to post-meta
        foreach ($sanitized_values as $key => $value) {
            update_post_meta($new_submission_id, $key, $value);
        }

        // Debugging line
        if (isset($_POST['kategori_konsumen']) && !empty($_POST['kategori_konsumen'])) {
            $kategori_konsumen = sanitize_text_field($_POST['kategori_konsumen']);
            wp_set_object_terms($new_submission_id, $kategori_konsumen, 'konsumen_kategori', false);
        }

        return $new_submission_id;
    }

    /**
     * Replace 'subscriber' with the required role to update, can also be contributor
     */
    function allow_subscriber_uploads()
    {
        if (is_admin()) {
            return;
        }
        /**
         * Replace 'subscriber' with the required role to update, can also be contributor
         */
        $subscriber = get_role('subscriber');

        // This is the only cap needed to upload files.
        $subscriber->add_cap('upload_files');
    }

    /**
     * Restricts the media library based on the current user's capabilities and the current page.
     *
     * @param object $wp_query_obj The WordPress query object.
     */
    function restrict_media_library($wp_query_obj)
    {
        if (is_admin()) {
            return;
        }

        global $current_user, $pagenow;

        if (! is_a($current_user, 'WP_User')) {
            return;
        }

        if ('admin-ajax.php' != $pagenow || 'query-attachments' != $_REQUEST['action']) {
            return;
        }

        if (! current_user_can('manage_media_library')) {
            $wp_query_obj->set('author', $current_user->ID);
        }
    }
}

$CMB2_Frontend_Form_Bs = new CMB2_Frontend_Form_Bs;
$CMB2_Frontend_Form_Bs->initialize();
