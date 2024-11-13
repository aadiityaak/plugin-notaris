<?php

add_action('wp_ajax_tandai_selesai', 'tandai_selesai');
function tandai_selesai()
{
  $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
  $status = $_POST['status'] ?? '';

  if ($post_id) {
    update_post_meta($post_id, 'status_post', $status);
    wp_send_json(array(
      'status' => 200,
      'message' => 'Update Berhasil'
    ));
  } else {
    wp_send_json(array(
      'status' => 400,
      'message' => 'ID tidak valid'
    ));
  }
  exit; // Pastikan exit setelah wp_send_json
}
