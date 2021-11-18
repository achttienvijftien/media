<?php
/**
 * This file contains the logic for RequestsTransportCurl.
 *
 * @package AchttienVijftien\Plugin\Media
 */

namespace AchttienVijftien\Plugin\Media;

use CURLFile;
use Requests;
use Requests_Transport_cURL;

/**
 * Overrides WordPress Requests Requests_Transport_cURL to accept file uploads.
 */
class RequestsTransportCurl extends Requests_Transport_cURL {

	/**
	 * Overrides setup_handle of parent class skipping the flattening of CURLFile objects.
	 *
	 * @param string       $url URL to request.
	 * @param array        $headers Headers to send with the request.
	 * @param array|string $data Data to send with the request.
	 * @param array        $options Options for the request.
	 */
	protected function setup_handle( $url, $headers, $data, $options ) {
		parent::setup_handle( $url, $headers, $data, $options );

		if ( Requests::POST !== $options['type'] ) {
			return;
		}

		$redo = false;
		foreach ( $data as $key => $value ) {
			if ( $value instanceof CURLFile ) {
				$redo = true;
				break;
			}
		}

		if ( ! $redo ) {
			return;
		}

		// @codingStandardsIgnoreStart WordPress.WP.AlternativeFunctions.curl_curl_setopt
		curl_setopt( $this->handle, CURLOPT_POSTFIELDS, $data );
	}
}
