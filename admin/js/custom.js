(function( $ ) {
	'use strict';

	$(function () {

		if (window.location.href.indexOf('post_type=draft_kerja') > -1 && window.location.href.indexOf('edit.php') > -1) {
			$('[data-bs-toggle="tooltip"]').tooltip();
		}


		// Periksa apakah kita berada di halaman buat post type 'data_pelanggan'
		if (window.location.href.indexOf('post_type=data_pelanggan') > -1 && window.location.href.indexOf('post-new.php') > -1) {
			$('#title-prompt-text').text('Nama Lengkap Pelanggan.');
		}

		if (window.location.href.indexOf('post_type=draft_kerja') > -1 && window.location.href.indexOf('post-new.php') > -1) {
			$('#title-prompt-text').text('Kode Project');
			// Periksa saat halaman dimuat
			checkAndFillPostTitle();

			// Periksa setiap kali nilai input berubah
			$(document).on('input', 'input[name="post_title"]', function () {
				checkAndFillPostTitle();
			});

			function checkAndFillPostTitle() {
				// Ambil nilai dari input post_title
				var postTitleValue = $('input[name="post_title"]').val();

				// Jika nilai kosong, isi dengan nomor unik
				if (postTitleValue.trim() === '') {
					// Nomor unik 'P-' diikuti dengan timestamp
					var uniqueNumber = 'CS-' + Date.now();

					// Set nilai input post_title
					$('input[name="post_title"]').val(uniqueNumber);
				}
			}
		}

	});

})( jQuery );
