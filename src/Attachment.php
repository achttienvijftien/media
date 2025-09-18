<?php
/**
 * Handles a single attachment.
 *
 * @package AchttienVijftien\Plugin\Media
 */

namespace AchttienVijftien\Plugin\Media;

/**
 * Class Attachment
 */
class Attachment {

	/**
	 * @var \WP_Post|null
	 */
	private ?\WP_Post $post = null;

	/**
	 * @param int $attachment_id
	 */
	private function __construct( private readonly int $attachment_id ) {
	}

	/**
	 * Gets Attachment from ID.
	 *
	 * @param int $attachment_id The attachment ID.
	 *
	 * @return self
	 */
	public static function from_id( int $attachment_id ): self {
		return new self( $attachment_id );
	}

	/**
	 * Gets post of attachment.
	 *
	 * @return \WP_Post
	 * @throws \Exception
	 */
	public function get_post(): \WP_Post {
		if ( null === $this->post ) {
			$post = get_post( $this->attachment_id );

			if ( ! $post instanceof \WP_Post ) {
				throw new \Exception( 'Attachment not found' );
			}

			$this->post = $post;
		}

		return $this->post;
	}

	/**
	 * Checks whether the attachment is an image. SVGs are not counting as images.
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function is_image(): bool {
		$attachment = $this->get_post();

		if ( 'image/svg+xml' === $attachment->post_mime_type ) {
			return false;
		}

		return wp_attachment_is_image( $attachment );
	}
}
