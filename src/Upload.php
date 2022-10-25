<?php
/**
 * This file contains the logic for Upload.
 *
 * @package AchttienVijftien\Plugin\Media
 */

namespace AchttienVijftien\Plugin\Media;

use WP_Image_Editor;

/**
 * All upload logic for the plugin.
 */
class Upload {

	/**
	 * Upload constructor.
	 */
	public function __construct() {
		add_action( 'add_attachment', [ $this, 'add_attachment' ], 999 );
		add_action( 'delete_attachment', [ $this, 'delete_attachment' ], 999 );
		add_filter( 'wp_save_image_editor_file', [ $this, 'save_image_editor_file' ], 999, 5 );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'wp_generate_attachment_metadata' ], 999, 2 );
		add_filter( 'wp_update_attachment_metadata', [ $this, 'wp_update_attachment_metadata' ], 999, 2 );
	}

	/**
	 * Gets minimum image sizes.
	 *
	 * @param string $path Path to image.
	 * @param string $mime Mime type of image.
	 *
	 * @return array[]
	 */
	private function get_image_sizes( string $path, string $mime ): array {
		return [
			'thumbnail' => [
				'width'     => 150,
				'height'    => 150,
				'file'      => basename( $path ),
				'mime-type' => $mime,
			],
		];
	}

	/**
	 * Uploads local image to mediatool.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $local_file Local image file path.
	 *
	 * @return bool
	 */
	private function upload_file( int $attachment_id, string $local_file ): bool {
		$upload_dir = wp_upload_dir();
		$path       = str_replace( [ $upload_dir['basedir'], basename( $local_file ) ], '', $local_file );

		// upload file to media tool.
		$upload_success = Api::upload(
			$local_file,
			[
				'path' => $path,
			]
		);

		if ( ! $upload_success ) {
			return false;
		}

		// add flag to attachment meta.
		add_post_meta( $attachment_id, '_1815_media_uploaded', true );

		$image_size = wp_getimagesize( $local_file );
		if ( $image_size ) {
			wp_update_attachment_metadata(
				$attachment_id,
				[
					'_by_1815_media' => true,
					'width'          => $image_size[0],
					'height'         => $image_size[1],
					'mime-type'      => $image_size['mime'],
					'file'           => trailingslashit( $upload_dir['subdir'] ) . basename( $local_file ),
					'sizes'          => $this->get_image_sizes( $local_file, $image_size['mime'] ),
				]
			);
		}

		// delete file from local filesystem.
		unlink( $local_file );

		return true;
	}

	/**
	 * Add the attachment to the database.
	 *
	 * @param int $attachment_id The attachment id.
	 */
	public function add_attachment( $attachment_id ): void {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}

		$file = get_attached_file( $attachment_id );
		$this->upload_file( (int) $attachment_id, $file );
	}

	/**
	 * Deletes attachment from mediatool.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return void
	 */
	public function delete_attachment( $attachment_id ): void {
		$is_uploaded = get_post_meta( $attachment_id, '_1815_media_uploaded', true );
		if ( ! $is_uploaded ) {
			return;
		}

		// get attachment meta data.
		$attachment_meta = wp_get_attachment_metadata( $attachment_id );

		// check if file path is set.
		if ( empty( $attachment_meta['file'] ) ) {
			return;
		}

		// send removal request to mediatool.
		Api::delete( $attachment_meta['file'] );
	}

	/**
	 * Handles edited image saves.
	 *
	 * @param null|bool       $override Whether to override saving the edited image.
	 * @param string          $filename Local path of image file.
	 * @param WP_Image_Editor $image Edited image object.
	 * @param string          $mime_type Mime type of image.
	 * @param int             $attachment_id Attachment ID.
	 *
	 * @return bool|null
	 */
	public function save_image_editor_file( $override, $filename, $image, $mime_type, $attachment_id ): ?bool {
		$saved_image = $image->save( $filename, $mime_type );

		return $this->upload_file( (int) $attachment_id, $saved_image['path'] );
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
	 * Resets image sizes created by WordPress core or external plugins.
	 *
	 * @param array $meta The attachment meta data.
	 * @param int   $attachment_id Attachment ID.
	 *
	 * @return mixed
	 */
	public function wp_update_attachment_metadata( $meta, $attachment_id ) {
		if ( ! empty( $meta['_by_1815_media'] ) ) {
			unset( $meta['_by_1815_media'] );

			return $meta;
		}

		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return $meta;
		}

		unset( $meta['sizes'] );
		$meta['sizes'] = $this->get_image_sizes( $meta['file'], $meta['mime-type'] );

		return $meta;
	}
}
