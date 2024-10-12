<?php

/**
 * Class Custom_Plugin_Shortcode
 */
class Custom_Plugin_Shortcode
{

    /**
     * Custom_Plugin_Shortcode constructor.
     */
    public function __construct()
    {
        add_shortcode('custom-plugin', array($this, 'custom_plugin_text_shortcode_callback')); // [custom-plugin]
        add_shortcode('add-new-user', array($this, 'render_new_user_form')); // [add-new-user]
        add_action('cmb2_init', array($this, 'register_user_frontend_form'));
    }

    public function custom_plugin_text_shortcode_callback($atts, $content = null)
    {
        return '<p>Contoh Output shortcode</p>';
    }

    public function register_user_frontend_form()
    {
        $user_id = $_GET['user_id'] ?? '';
        $user_data = get_userdata($user_id);
        $full_name = $user_data->first_name ?? '';
        $user_login = $user_data->user_login ?? '';
        $email = $user_data->user_email ?? '';
        $user_role = $user_data->roles[0] ?? '';
        $address = get_user_meta($user_id, 'address', true);
        $poto_profil = get_user_meta($user_id, 'poto_profil', true);
        $password = get_user_meta($user_id, 'password', true);
        $required_password = ($user_id == '') ? 'required' : false;
        $cmb_user = new_cmb2_box(array(
            'id'           => 'user_frontend_form',
            'object_types' => array('user'),
            'hookup'       => false,
            'save_fields'  => false,

        ));

        // Nama Lengkap
        $cmb_user->add_field(array(
            'name'    => 'Nama Lengkap',
            'id'      => 'full_name',
            'type'    => 'text',
            'default' => $full_name,
            'attributes' => array(
                'required' => 'required', // Tambahkan atribut required
            ),
        ));

        // Nama Pengguna
        $cmb_user->add_field(array(
            'name'    => 'Username',
            'id'      => 'user_login',
            'type'    => 'text',
            'default' => $user_login,
            'attributes' => array(
                'required' => 'required', // Tambahkan atribut required
                'autocomplete' => '',
            ),
        ));

        // PHOBE
        $cmb_user->add_field(array(
            'name'    => 'No Telpon/WA',
            'id'      => 'no_telpon_staff',
            'type'    => 'text',
            'column'  => true,
            'attributes' => array(
                'required' => 'required', // Tambahkan atribut required
            ),
        ));

        // Email
        $cmb_user->add_field(array(
            'name'    => 'Email',
            'id'      => 'email',
            'type'    => 'text_email',
            'default' => $email,
            'attributes' => array(
                'required' => 'required', // Tambahkan atribut required
            ),
        ));



        // Role Pengguna
        $cmb_user->add_field(array(
            'name'    => 'Role Pengguna',
            'id'      => 'user_role',
            'type'    => 'select',
            'default' => $user_role,
            'options' => array(
                'superadmin' => 'Superadmin',
                'administrator' => 'Administrator',
                'editor'        => 'Editor',
            ),
            'default' => 'subscriber',
        ));

        // Alamat
        $cmb_user->add_field(array(
            'name'    => 'Alamat',
            'id'      => 'address',
            'default' => $address,
            'type'    => 'textarea_small',
        ));

        // Password
        $cmb_user->add_field(array(
            'name'    => 'Password',
            'id'      => 'password',
            'type'    => 'text',
            'default' => $password,
            'attributes' => array(
                'autocomplete' => 'off',
                'required' => $required_password, // Tambahkan atribut required
                'type' => 'password',
                'minlength' => '8',

            ),
        ));

        // Poto Profil
        $cmb_user->add_field(array(
            'name'    => 'Poto Profil',
            'id'      => 'poto_profil',
            'default' => $poto_profil,
            'type'    => 'file',
        ));
    }

    public function render_new_user_form($atts = array())
    {
        // Extract shortcode attributes, default to user_id = 0 (new user)
        $user_id = $_GET['user_id'] ?? 0;

        // Check if the user is logged in
        if (!is_user_logged_in()) {
            return '<p>You need to be logged in to add or edit a user.</p>';
        }

        // Check for user role permissions, only allow administrators to add or edit users
        if (!current_user_can('administrator')) {
            return '<p>Sorry, you do not have permission to add or edit users.</p>';
        }

        // Use the CMB2 form
        $metabox_id = 'user_frontend_form';
        $cmb = cmb2_get_metabox($metabox_id, $user_id); // Use user ID if editing

        // Pre-fill form if editing existing user
        if ($user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $cmb->object_id($user_id); // Set object ID to the user ID for pre-filling the fields
            } else {
                return '<p>User not found.</p>';
            }
        }

        // Handle form submission
        $output = '';
        $result = $this->handle_new_user_submit($cmb, $user_id);
        if ($result) {
            if (is_wp_error($result)) {
                $output .= '<div class="alert alert-warning">' . $result->get_error_message() . '</div>';
            } else {
                $output .= '<div class="alert alert-success">User ' . ($user_id ? 'updated' : 'added') . ' successfully with ID: ' . $result . '</div>';
                wp_safe_redirect('https://asistennotaris.com/data-staff/?type=staff');
                exit;
            }
        }

        // Display form with Bootstrap 5 styling
        $form = cmb2_get_metabox_form($cmb, $user_id ? $user_id : null, array('save_button' => __('Save User', 'cmb2-user-submit')));

        // Apply Bootstrap 5 styling
        $styling = [
            'regular-text'                              => 'regular-text form-control',
            'cmb2-text-small'                           => 'cmb2-text-small form-control',
            'cmb2-text-medium'                          => 'cmb2-text-medium form-control',
            'cmb2-timepicker'                           => 'cmb2-timepicker form-control d-inline-block',
            'cmb2-datepicker'                           => 'cmb2-datepicker d-inline-block',
            'cmb2-text-money'                           => 'cmb2-text-money form-control d-inline-block',
            'cmb2_textarea'                             => 'cmb2_textarea form-control w-100',
            'cmb2-textarea-small'                       => 'cmb2-textarea-small form-control d-inline-block',
            'cmb2_select'                               => 'cmb2_select form-select',
            'cmb2-upload-file regular-text'             => 'cmb2-upload-file regular-text form-control d-block w-100',
            'type="radio" class="cmb2-option"'          => 'type="radio" class="cmb2-option form-check-input"',
            'type="checkbox" class="cmb2-option"'       => 'type="checkbox" class="cmb2-option form-check-input"',
            'class="button-primary"'                    => 'class="button-primary btn btn-primary float-end"',
            'cmb2-metabox-description'                  => 'cmb2-metabox-description fw-normal small',
            'class="cmb-th"'                            => 'class="cmb-th w-100 p-0"',
            'class="cmb-td"'                            => 'class="cmb-th w-100 p-0 pb-2"',
            'class="cmb-add-row"'                       => 'class="cmb-add-row text-end"',
            'button-secondary'                          => 'button-secondary btn-sm btn btn-outline-secondary',
            'cmb2-upload-button'                        => 'cmb2-upload-button mt-1 ms-0',
            'button-secondary btn-sm btn btn-outline-secondary cmb-remove-row-button' => 'button-secondary btn btn-danger cmb-remove-row-button',
        ];

        // Replace form classes with Bootstrap 5
        $form = strtr($form, $styling);

        // Tambahkan tombol disabled secara default dan cek form dengan JavaScript
        $output .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            const button = form.querySelector(".button-primary");
            button.disabled = true;

            const requiredFields = form.querySelectorAll("[required]");
            
            requiredFields.forEach(field => {
                field.addEventListener("input", function() {
                    let allFilled = true;
                    requiredFields.forEach(input => {
                        if (!input.value) {
                            allFilled = false;
                        }
                    });
                    button.disabled = !allFilled;
                });
            });
        });
        </script>';

        $output .= $form;

        return $output;
    }

    public function handle_new_user_submit($cmb, $user_id = 0)
    {
        $get_user_id = $_GET['user_id'] ?? 0;
        // Check if the form was submitted
        if (empty($_POST)) {
            return false;
        }

        // Sanitize the values from the form
        $sanitized_values = $cmb->get_sanitized_values($_POST);

        // Check required fields
        if (empty($sanitized_values['email']) || empty($sanitized_values['full_name'])) {
            return new WP_Error('missing_fields', 'Email, and Full Name are required.');
        }
        if (!$get_user_id && empty($sanitized_values['password'])) {
            return new WP_Error('missing_fields', 'Password is required.');
        }

        // Check if updating an existing user
        if ($user_id) {
            // Update the existing user
            $userdata = array(
                'ID'         => $user_id,
                'user_login' => $sanitized_values['user_login'],
                'user_email' => $sanitized_values['email'],
                'first_name' => $sanitized_values['full_name'],
                'role'       => $sanitized_values['user_role'],
            );

            $user_id = wp_update_user($userdata);
        } else {
            // Create a new user
            $userdata = array(
                'user_login' => $sanitized_values['user_login'],
                'user_email' => $sanitized_values['email'],
                'user_pass'  => $sanitized_values['password'],
                'first_name' => $sanitized_values['full_name'],
                'role'       => $sanitized_values['user_role'],
            );

            $user_id = wp_insert_user($userdata);
        }

        // Set avatar
        if (!empty($sanitized_values['poto_profil'])) {
            update_user_meta($user_id, 'poto_profil', $sanitized_values['poto_profil']);
        }

        // Check for errors
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Save additional user meta
        foreach ($sanitized_values as $key => $value) {
            if ($key !== 'password') { // Skip password saving in user meta
                update_user_meta($user_id, $key, $value);
                update_user_meta($user_id, 'status', 'Aktif');
            }
        }

        return $user_id;
    }
}

// Inisialisasi class Custom_Plugin_Shortcode
new Custom_Plugin_Shortcode();
