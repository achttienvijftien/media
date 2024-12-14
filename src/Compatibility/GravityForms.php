<?php
/**
 * This file implements support for Gravity Forms (file uploads).
 *
 * @package AchttienVijftien\Plugin\Media\Compatibility
 */

namespace AchttienVijftien\Plugin\Media\Compatibility;

use AchttienVijftien\Plugin\Media\Api;
use AchttienVijftien\Plugin\Media\Config;

/**
 * Class GravityForms
 */
class GravityForms {

	/**
	 * GravityForms constructor.
	 */
	public function __construct() {
		add_filter( 'gform_save_field_value', [ $this, 'upload_file_to_api' ], 99, 3 );
	}

	/**
	 * Uploads processed file upload to media API.
	 *
	 * @param mixed     $value The form entry field value.
	 * @param array     $entry The whole entry.
	 * @param \GF_Field $field Currently handled field.
	 *
	 * @return mixed
	 */
	public function upload_file_to_api( $value, array $entry, \GF_Field $field ) {
		if ( ! $field instanceof \GF_Field_FileUpload ) {
			return $value;
		}

		if ( empty( $entry['status'] ) ) {
			return $value;
		}

		if ( '[]' === $value ) {
			return $value;
		}

		$media_url     = Config::get_instance()->get( 'media_url' );
		$decoded_value = json_decode( $value, true );
		foreach ( $decoded_value as $key => $url ) {
			if ( ! $url ) {
				continue;
			}

			$filetype = wp_check_filetype( $url );
			if ( ! $filetype['ext'] ) {
				continue;
			}

			$upload_dir = wp_upload_dir();
			$filename   = basename( $url );
			$path       = str_replace( [ $upload_dir['baseurl'], $filename ], '', $url );
			$local_file = $upload_dir['basedir'] . $path . $filename;

			// upload file to media tool.
			$upload_success = Api::upload(
				$local_file,
				[
					'path' => $path,
				]
			);

			if ( ! $upload_success ) {
				continue;
			}

			// delete file from local filesystem.
			wp_delete_file( $local_file );

			$decoded_value[ $key ] = rtrim( $media_url, '/' ) . '/dl' . $path . $filename;
		}

		return wp_json_encode( $decoded_value );
	}
}
