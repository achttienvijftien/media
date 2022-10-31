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
		add_filter( 'upload_mimes', [ $this, 'allowed_mime_types' ], 999 );
		add_action( 'add_attachment', [ $this, 'add_attachment' ], 999 );
		add_action( 'delete_attachment', [ $this, 'delete_attachment' ], 999 );
		add_filter( 'wp_save_image_editor_file', [ $this, 'save_image_editor_file' ], 999, 5 );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'wp_generate_attachment_metadata' ], 999, 2 );
		add_filter( 'wp_update_attachment_metadata', [ $this, 'wp_update_attachment_metadata' ], 999, 2 );
	}

	/**
	 * Filters allowed upload mime types.
	 *
	 * @param array $mime_types Allowed mime types.
	 *
	 * @return string[]
	 */
	public function allowed_mime_types( array $mime_types ): array {
		return [
			// Image formats.
			'jpg|jpeg|jpe'                 => 'image/jpeg',
			'gif'                          => 'image/gif',
			'png'                          => 'image/png',
			'bmp'                          => 'image/bmp',
			'tiff|tif'                     => 'image/tiff',
			'webp'                         => 'image/webp',
			'ico'                          => 'image/x-icon',
			'heic'                         => 'image/heic',
			// Text formats.
			'txt|asc|c|cc|h|srt'           => 'text/plain',
			'csv'                          => 'text/csv',
			'tsv'                          => 'text/tab-separated-values',
			'ics'                          => 'text/calendar',
			'rtx'                          => 'text/richtext',
			'css'                          => 'text/css',
			'vtt'                          => 'text/vtt',
			'dfxp'                         => 'application/ttaf+xml',
			// Misc application formats.
			'pdf'                          => 'application/pdf',
			'tar'                          => 'application/x-tar',
			'zip'                          => 'application/zip',
			'gz|gzip'                      => 'application/x-gzip',
			'rar'                          => 'application/rar',
			'7z'                           => 'application/x-7z-compressed',
			// MS Office formats.
			'doc'                          => 'application/msword',
			'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
			'wri'                          => 'application/vnd.ms-write',
			'xla|xls|xlt|xlw'              => 'application/vnd.ms-excel',
			'mdb'                          => 'application/vnd.ms-access',
			'mpp'                          => 'application/vnd.ms-project',
			'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
			'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
			'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
			'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
			'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
			// phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
			'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
			'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
			'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
			'oxps'                         => 'application/oxps',
			'xps'                          => 'application/vnd.ms-xpsdocument',
			// OpenOffice formats.
			'odt'                          => 'application/vnd.oasis.opendocument.text',
			'odp'                          => 'application/vnd.oasis.opendocument.presentation',
			'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
			'odg'                          => 'application/vnd.oasis.opendocument.graphics',
			'odc'                          => 'application/vnd.oasis.opendocument.chart',
			'odb'                          => 'application/vnd.oasis.opendocument.database',
			'odf'                          => 'application/vnd.oasis.opendocument.formula',
			// WordPerfect formats.
			'wp|wpd'                       => 'application/wordperfect',
			// iWork formats.
			'key'                          => 'application/vnd.apple.keynote',
			'numbers'                      => 'application/vnd.apple.numbers',
			'pages'                        => 'application/vnd.apple.pages',
		];
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
	 * Uploads local file to mediatool.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @param string $local_file Local file path.
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

		// if an image, add extra meta data.
		if ( wp_attachment_is_image( $attachment_id ) ) {
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

		// get file path.
		$attachment_path = get_post_meta( $attachment_id, '_wp_attached_file', true );
		if ( ! $attachment_path ) {
			// get attachment meta data.
			$attachment_meta = wp_get_attachment_metadata( $attachment_id );
			// check if file path is set in meta data.
			$attachment_path = $attachment_meta['file'] ?? null;
		}

		// check if file path is set.
		if ( ! $attachment_path ) {
			return;
		}

		// send removal request to mediatool.
		Api::delete( $attachment_path );
	}

	/**
	 * Handles edited image saves.
	 *
	 * @param null|bool $override Whether to override saving the edited image.
	 * @param string $filename Local path of image file.
	 * @param WP_Image_Editor $image Edited image object.
	 * @param string $mime_type Mime type of image.
	 * @param int $attachment_id Attachment ID.
	 *
	 * @return bool|null
	 */
	public function save_image_editor_file( $override, $filename, $image, $mime_type, $attachment_id ): ?bool {
		$saved_image = $image->save( $filename, $mime_type );

		return $this->upload_file( (int) $attachment_id, $saved_image['path'] );
	}

	/**
	 * Makes sure the original metadata is kept.
	 *
	 * @param array $metadata The generated metadata.
	 * @param int|string $attachment_id The attachment id.
	 *
	 * @return array
	 */
	public function wp_generate_attachment_metadata( $metadata, $attachment_id ) {
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
	 * @param int $attachment_id Attachment ID.
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
