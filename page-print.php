<?php

/**
 * Template Name: Notaris Print
 *
 */

$proses_kerja       = isset($_GET['proses_kerja']) ? $_GET['proses_kerja'] : '';
$download   = isset($_GET['download']) ? $_GET['download'] : 0;

if (empty($proses_kerja)) {
    return false;
}
echo 'bbbb';
$args =
    array(
        "post_type" => "draft_kerja",
        // "s" => $proses_kerja
    );
$query = get_posts($args);

if (empty($query)) {
    return false;
}
echo 'cccc';
///Barcode
require 'vendor/autoload.php';

//start html
ob_start();
if (isset($query[0]->ID)) {
    $meta_key = get_post_meta($query[0]->ID);
    $customer_select = $meta_key['customer_select'][0] ?? '';
    $layanan = $meta_key['layanan'][0] ?? '';
    $tanggal_order = $meta_key['tanggal_order'][0] ?? '';
    $sertipikat_asli = $meta_key['sertipikat_asli'][0] == 'on' ? '<input type="checkbox" checked>' : '<input type="checkbox">';
    $ktp = isset($meta_key['ktp'][0]) && $meta_key['ktp'][0] == 'on' ? '<input type="checkbox" checked>' : '<input type="checkbox">';
    $kk = isset($meta_key['kk'][0]) && $meta_key['kk'][0] == 'on' ? '<input type="checkbox" checked>' : '<input type="checkbox">';
    $pbb = isset($meta_key['pbb'][0]) && $meta_key['pbb'][0] == 'on' ? '<input type="checkbox" checked>' : '<input type="checkbox">';
?>
    <div class="container">
        <div style="text-align: center;">
            <div style="padding-bottom:10px;">
                <div style="font-size: 18px; font-weight: 600;padding-bottom:5px;">NOTARIS & PPAT</div>
                <div>DAERAH KERJA KABUPATEN GUNUNGKIDUL</small><br>
                    <div style="font-size: 18px; font-weight: 600;padding-bottom:5px;">ZULFIKAR PANDU WILANTARA, SH., MKn.</div>
                    <div style="padding-bottom:5px;"><b>Alamat Kantor:</b> Perum Bumi Logandeng Asri Blok A6 - Jl. Manthous KM 01 Desa Logandeng, Kecamatan Playen, Kabupaten Gunungkidul E-mail zulfikarpandu@rocketmail.com | Telepon 087738899662/087775757493</div>
                </div>
                <hr class="s2">
            </div>
            <div class="frame-kotak" style="text-align: center;">
                <span>TANDA TERIMA</span>
            </div>
            <br>
            <div class="frame-konten">
                <div style="text-align: left;">
                    <div style="padding-bottom:5px;">Telah diterima dari: Tuan/Nyonya .......................................................................................................</div>
                    <div style="padding-bottom:5px;">Proses: ................................................................................................................................................</div>
                </div>
                <div style="padding-bottom:5px;">Tanggal Order: ....................................................................................................................................</div>
                <div>No. Telp: ..............................................................................................................................................</div>
            </div>
            <div style="padding:30px 0 30px;">
                <table class="table-berkas" style="width: 100%;">
                    <thead>
                        <tr style="text-align: left;">
                            <th scope="row" style="text-align: left;">Berkas</th>
                            <th scope="row" style="text-align: center; width:30px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span>Sertipikat Asli</span></td>
                            <td style="width:30px;text-align:center;"><span><?php echo $sertipikat_asli; ?></span></td>
                        </tr>
                        <tr>
                            <td><span>PBB</span></td>
                            <td style="width:30px;text-align:center;"><span><?php echo $pbb; ?></span></td>
                        </tr>
                        <tr>
                            <td><span>KTP</span></td>
                            <td style="width:30px;text-align:center;"><span><?php echo $ktp; ?></span></td>
                        </tr>
                        <tr>
                            <td><span>KK</span></td>
                            <td style="width:30px;text-align:center;"><span><?php echo $kk; ?></span></td>
                        </tr>
                        <tr>
                            <td><span></span></td>
                            <td style="width:30px;text-align:center;"><span><input type="checkbox"></span></td>
                        </tr>
                        <tr>
                            <td><span></span></td>
                            <td style="width:30px;text-align:center;"><span><input type="checkbox"></span></td>
                        </tr>
                        <tr>
                            <td><span></span></td>
                            <td style="width:30px;text-align:center;"><span><input type="checkbox"></span></td>
                        </tr>
                        <tr>
                            <td><span></span></td>
                            <td style="width:30px;text-align:center;"><span><input type="checkbox"></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div style="text-align: center;">
            <span>Semua surat-surat yang kami terima ini diserahkan dalam rangka pengurusan/penyelesaian urusan-urusan yang diserahkan pada kantor kami.</span>
        </div>
        <br><br>
        <div style="text-align: right;">
            <span>Gunungkidul,........................20.......</span>
        </div>
        <br><br>
        <div class="frame-footer">
            <table style="width: 100%;">
                <tr>
                    <td style="text-align: center; width: 65%;">
                        Yang Menerima,
                        <br><br><br><br>
                        <b>(ZULFIKAR PANDU WIRANTARA, SH., MKn.)</b>
                    </td>
                    <td style="text-align: center; width: 35%;">
                        Yang Menyerahkan,
                        <br><br><br><br>
                        (.................................................)
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <style>
        .container {
            font-family: Times New Roman !important;
        }

        hr.s2 {
            height: 5px;
            border-left: 0 !important;
            border-right: 0 !important;
            border-top: 1px solid black;
            border-bottom: 2px solid black;
        }

        .frame-kotak span {
            display: inline-block;
            border: 2px solid black;
            padding: 5px 10px;
            margin: 20px 10px;
            font-weight: 600;
        }

        .table-berkas th {
            border-bottom: 1px solid #aaa;
            border-top: 1px solid #aaa;
            border-collapse: collapse;
            padding: 5px;
        }

        .table-berkas td {
            border-bottom: 1px solid #ddd;
            border-collapse: collapse;
            padding: 5px;
        }

        /* .frame-konten {
            display: inline-block;
        }

        .frame-konten span::after {
            content: '......................................................';
        } */

        /* .frame-konten tr:nth-child(even) {
            background-color: #dddddd;
        } */
    </style>

    <script>
        $(document).ready(function() {
            var contentWidth = $('.frame-konten span').width();
            $('.frame-konten span::after').css('width', contentWidth);
        });
    </script>
<?php
    // print_r($meta_key);
}
$html = ob_get_clean();


use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('defaultFont', 'Helvetica');
$options->set('enable_remote', true);

// instantiate and use the dompdf class
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'potrait');
// $dompdf->setPaper(array(0, 0, 300, 300), 'potrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream("Tanda Terima " . $customer_name, array("Attachment" => $download));

function proses_job_desk()
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

    $the_query = new WP_Query(
        array(
            'post_type' => 'job_desk',
            'meta_key' => 'job_desk_draft_kerja',
            'meta_value' => $draft_id,
        )
    );

?>
    <div class="container">
        <table class="table" style="width: 100%;">
            <thead>
                <tr style="text-align: left;">
                    <th scope="row" style="text-align: left;">Status</th>
                    <th scope="row" style="text-align: left;">Judul</th>
                    <th scope="row" style="text-align: left;">Tgl Mulai</th>
                    <th scope="row" style="text-align: left;">Tgl Selesai</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($the_query->have_posts()) {
                    while ($the_query->have_posts()) {
                        $the_query->the_post();

                        $judul = get_post_meta($post->ID, 'judul_job_desk', true);
                        $tanggal_mulai = get_post_meta($post->ID, 'job_desk_start', true);
                        $tanggal_selesai = get_post_meta($post->ID, 'job_desk_end', true);
                        $status = get_post_meta($post->ID, 'job_desk_status', true);
                ?>
                        <tr>
                            <td class="text-center mb-2" style="padding: 5px 0;"><span>
                                    <?php
                                    if ($status == 'Selesai') {
                                        echo 'Selesai';
                                    } elseif ($status == 'Pengerjaan') {
                                        echo 'Pengerjaan';
                                    } else {
                                        echo 'Dalam Tinjauan';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td><span><?php echo $judul; ?></span></td>
                            <td><span><?php echo convertDateFormat($tanggal_mulai); ?></span></td>
                            <td><span><?php echo convertDateFormat($tanggal_selesai); ?></span></td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4">Jobdesk tidak ditemukan untuk order ini. Klik <b>Tambah Job Desk</b> di bawah ini untuk menambahkan job desk baru.</td>
                    </tr>
                <?php
                }
                wp_reset_postdata();
                ?>
            </tbody>
        </table>

    </div>
<?php
    return ob_get_clean();
}
// add_shortcode('tracking-order', 'tracking_order_shortcode');