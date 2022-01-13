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
		add_action( 'add_attachment', [ $this, 'add_attachment' ], 999 );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'wp_generate_attachment_metadata' ], 999, 2 );
		add_filter( 'wp_image_editors', [ $this, 'disable_image_editors' ] );
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

		$image_size = wp_getimagesize( $file );
		if ( $image_size ) {
			wp_update_attachment_metadata(
				$attachment_id,
				[
					'width'     => $image_size[0],
					'height'    => $image_size[1],
					'mime-type' => $image_size['mime'],
					'file'      => trailingslashit( $upload_dir['subdir'] ) . basename( $file ),
					'sizes'     => [
						'thumbnail' => [
							'width'     => 150,
							'height'    => 150,
							'file'      => basename( $file ),
							'mime-type' => $image_size['mime'],
						],
					],
				]
			);
		}

		// delete file from local filesystem.
		unlink( $file );
	}

	/**
	 * Makes sure the original image metadata is kept.
	 *
	 * @param array      $metadata The generated metadata.
	 * @param int|string $attachment_id The attachment id.
	 *
	 * @return array
	 */
	public function wp_generate_attachment_metadata( array $metadata, $attachment_id ): array {
		if ( ! get_post_meta( $attachment_id, '_1815_media_uploaded', true ) ) {
			return $metadata;
		}

		if ( ! empty( $metadata ) ) {
			return $metadata;
		}

		return get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
	}

	/**
	 * Disable image editors so image won't be processed after upload.
	 *
	 * @param array $editors Supported image editors.
	 *
	 * @return array
	 */
	public function disable_image_editors( array $editors ): array {
		return [];
	}
}
