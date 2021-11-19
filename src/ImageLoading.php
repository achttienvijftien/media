<?php
/**
 * This file contains the logic for ImageLoading.
 *
 * @package AchttienVijftien\Plugin\Media
 */

namespace AchttienVijftien\Plugin\Media;

/**
 * All public facing logic for the plugin.
 */
class ImageLoading {

	/**
	 * ImageLoading constructor.
	 */
	public function __construct() {
		add_filter( 'wp_get_attachment_image_src', [ $this, 'get_attachment_image_src' ], 10, 3 );
		add_filter( 'wp_get_attachment_url', [ $this, 'get_attachment_url' ], 10, 2 );
	}

	/**
	 * Checks if current image is uploaded to API.
	 *
	 * @param int $attachment_id The attachment id.
	 *
	 * @return bool
	 */
	private function is_uploaded( int $attachment_id ): bool {
		return (bool) get_post_meta( $attachment_id, '_1815_media_uploaded', true );
	}

	/**
	 * Setup image options for slug.
	 *
	 * @param array $size Size array of image.
	 *
	 * @return array
	 */
	private function get_image_options( array $size ): array {
		$options = [];

		if ( ! empty( $size['width'] ) ) {
			$options['width'] = (int) $size['width'];
		}

		if ( ! empty( $size['height'] ) ) {
			$options['height'] = (int) $size['height'];
		}

		return $options;
	}

	/**
	 * Get the image src for the image.
	 *
	 * @param array        $image The image src.
	 * @param int          $attachment_id The attachment id.
	 * @param string|array $size The size.
	 *
	 * @return array The image src.
	 */
	public function get_attachment_image_src( array $image, int $attachment_id, $size ): array {
		if ( ! $image ) {
			return $image;
		}

		if ( ! $this->is_uploaded( $attachment_id ) ) {
			return $image;
		}

		if ( 'full' === $size ) {
			return $image;
		}

		$image_sizes = wp_get_additional_image_sizes();
		if ( is_string( $size ) && isset( $image_sizes[ $size ] ) ) {
			$options = $this->get_image_options( $image_sizes[ $size ] );
		} else {
			$options = $this->get_image_options(
				[
					'width'  => $image[1],
					'height' => $image[2],
					'crop'   => false,
				]
			);
		}

		// build new image url.
		$image[0] = str_replace( 'i/full', 'i/' . http_build_query( $options ), $image[0] );

		return $image;
	}

	/**
	 * Get the image src for the image.
	 *
	 * @param string $url The image url.
	 * @param int    $attachment_id The attachment id.
	 *
	 * @return string The image url.
	 */
	public function get_attachment_url( string $url, int $attachment_id ): string {
		if ( ! $this->is_uploaded( $attachment_id ) ) {
			return $url;
		}

		$upload_dir = wp_get_upload_dir();

		return str_replace(
			$upload_dir['baseurl'],
			rtrim( Config::get_instance()->get( 'media_url' ), '/' ) . '/i/full',
			$url
		);
	}
}
