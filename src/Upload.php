<?php
/**
 * This file contains the logic for Upload.
 *
 * @package AchttienVijftien\Plugin\Media
 */

namespace AchttienVijftien\Plugin\Media;

/**
 * All upload logic for the plugin.
 */
class Upload {

	/**
	 * Upload constructor.
	 */
	public function __construct() {
		add_action( 'add_attachment', [ $this, 'add_attachment' ] );
	}

	/**
	 * Add the attachment to the database.
	 *
	 * @param int $attachment_id The attachment id.
	 */
	public function add_attachment( int $attachment_id ): void {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$file       = get_attached_file( $attachment_id );

		$path = str_replace( [ $upload_dir['basedir'], basename( $file ) ], '', $file );

		// upload file to media tool.
		$upload_success = Api::upload(
			$file,
			[
				'path' => $path,
			]
		);

		if ( ! $upload_success ) {
			return;
		}

		// add flag to attachment meta.
		add_post_meta( $attachment_id, '_1815_media_uploaded', true );

		// delete file from local filesystem.
		unlink( $file );
	}
}
