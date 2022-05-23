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
		// regular image handling.
		add_filter( 'pre_option_upload_url_path', [ $this, 'overwrite_upload_url_path' ] );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'get_attachment_image_src' ], 10, 3 );

		// media library handling.
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'prepare_attachment_for_js' ], 10, 2 );

		// srcset handling.
		add_filter( 'wp_calculate_image_srcset_meta', [ $this, 'wp_calculate_image_srcset_meta' ] );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'wp_calculate_image_srcset' ], 10, 5 );

		// dns prefetch.
		add_filter( 'wp_resource_hints', [ $this, 'dns_prefetch' ], 10, 2 );
	}

	/**
	 * Overwrites upload_url_path option.
	 *
	 * @param mixed $value Current value.
	 *
	 * @return string
	 */
	public function overwrite_upload_url_path( $value ) {
		return rtrim( Config::get_instance()->get( 'media_url' ), '/' ) . '/i/full';
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
	 * Replaces url with filtered url based on requested image size.
	 *
	 * @param string $full_url Full url.
	 * @param array  $size Size array of image.
	 *
	 * @return string
	 */
	private function replace_url( string $full_url, array $size ) {
		$options = $this->get_image_options( $size );

		// if no options, return original url.
		if ( ! $options ) {
			return $full_url;
		}

		// build new image url.
		return str_replace( 'i/full', 'i/' . http_build_query( $options ), $full_url );
	}

	/**
	 * Get the image src for the image.
	 *
	 * @param array|bool   $image The image src.
	 * @param int|string   $attachment_id The attachment id.
	 * @param string|array $size The size.
	 *
	 * @return array|bool The image src.
	 */
	public function get_attachment_image_src( $image, $attachment_id, $size ) {
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
			$size = $image_sizes[ $size ];
		} else {
			$size = [
				'width'  => $image[1],
				'height' => $image[2],
				'crop'   => false,
			];
		}

		// replace url.
		$image[0] = $this->replace_url( $image[0], $size );

		return $image;
	}

	/**
	 * Sets correct sizes urls for media library.
	 *
	 * @param array    $response Response array.
	 * @param \WP_Post $attachment Attachment object.
	 *
	 * @return array
	 */
	public function prepare_attachment_for_js( $response, $attachment ) {
		if ( ! $this->is_uploaded( (int) $attachment->ID ) ) {
			return $response;
		}

		$image_sizes = wp_get_additional_image_sizes();
		foreach ( $response['sizes'] as $size_name => &$size ) {
			if ( is_string( $size_name ) && isset( $image_sizes[ $size_name ] ) ) {
				$size_input = $image_sizes[ $size_name ];
			} else {
				$size_input = [
					'width'  => $size['width'],
					'height' => $size['height'],
					'crop'   => false,
				];
			}

			$size['url'] = $this->replace_url( $response['url'], $size_input );
		}

		return $response;
	}

	/**
	 * Overwrite image meta with all currently available image sizes.
	 *
	 * @param array $meta Database stored meta data of image.
	 *
	 * @return array
	 */
	public function wp_calculate_image_srcset_meta( $meta ) {
		$image_sizes = wp_get_additional_image_sizes();
		$filetype    = wp_check_filetype( $meta['file'] );

		$sizes = [];
		foreach ( $image_sizes as $handle => $image_size ) {
			$sizes[ $handle ] = [
				'file'      => basename( $meta['file'] ),
				'width'     => $image_size['width'],
				'height'    => $image_size['height'],
				'mime-type' => $filetype['type'],
			];
		}

		$meta['sizes'] = $sizes ?? $meta['sizes'];

		return $meta;
	}

	/**
	 * Overwrite image srcset sources with media url of given sources.
	 *
	 * @param array      $sources Sources array defined by default WP function.
	 * @param array      $sizes Sizes of original image.
	 * @param string     $src Image url.
	 * @param array      $meta Image meta data.
	 * @param string|int $attachment_id Attachment id.
	 *
	 * @return array
	 */
	public function wp_calculate_image_srcset( $sources, $sizes, $src, $meta, $attachment_id ) {
		// skip if not uploaded to api.
		if ( ! $this->is_uploaded( $attachment_id ) ) {
			return $sources;
		}

		// loop through sources to replace urls where needed.
		foreach ( $sources as &$source ) {
			foreach ( $meta['sizes'] as $size ) {
				// check if width matches width source and if ratio is within 1px.
				if ( $size['width'] === $source['value'] &&
					wp_image_matches_ratio(
						(int) $size['width'],
						(int) $size['height'],
						(int) $sizes[0],
						(int) $sizes[1]
					)
				) {
					$source['url'] = $this->replace_url( $source['url'], $size );
					continue 2;
				}
			}
		}

		return $sources;
	}

	/**
	 * Add media url to dns prefetch.
	 *
	 * @param array  $urls Urls to hint.
	 * @param string $relation_type Relation type.
	 *
	 * @return array
	 * @since 0.3.0
	 */
	public function dns_prefetch( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' !== $relation_type && 'preconnect' !== $relation_type ) {
			return $urls;
		}

		$config = Config::get_instance();
		$url    = wp_parse_url( $config->get( 'media_url' ) );

		if ( ! $url ) {
			return $urls;
		}

		$urls[] = '//' . $url['host'];

		return $urls;
	}
}
