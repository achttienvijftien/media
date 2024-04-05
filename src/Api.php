<?php
/**
 * This file contains the logic for Api.
 *
 * @package AchttienVijftien\Plugin\Media
 */

namespace AchttienVijftien\Plugin\Media;

/**
 * All api logic for the plugin.
 */
class Api {

	/**
	 * Holds access token.
	 *
	 * @var array|null
	 */
	private static ?array $access_token = null;

	/**
	 * Retrieves access token.
	 *
	 * @param string $scope Scope of this access token actions.
	 *
	 * @return array|null
	 * @todo: move access token to options.
	 */
	private static function get_access_token( $scope = 'UPLOAD' ): ?array {
		if ( null === self::$access_token || empty( self::$access_token[ $scope ] ) ) {
			global $wp_version;

			$config = Config::get_instance();

			// support versions up to WordPress 6.2.0 for some period of time.
			if ( version_compare( $wp_version, '6.2.0', '<' ) ) {
				$response = \Requests::post(
					rtrim( $config->get( 'api_url' ), '/' ) . '/api/oauth2/token',
					[],
					[
						'grant_type' => 'client_credentials',
						'scope'      => $scope,
					],
					[
						'auth' => [
							$config->get( 'client_id' ),
							$config->get( 'client_secret' ),
						],
					]
				);

			} else {
				$response = \WpOrg\Requests\Requests::post(
					rtrim( $config->get( 'api_url' ), '/' ) . '/api/oauth2/token',
					[],
					[
						'grant_type' => 'client_credentials',
						'scope'      => $scope,
					],
					[
						'auth' => [
							$config->get( 'client_id' ),
							$config->get( 'client_secret' ),
						],
					],
				);
			}

			if ( ! $response->success ) {
				return null;
			}

			self::$access_token[ $scope ] = json_decode( $response->body, true );
		}

		return self::$access_token[ $scope ];
	}

	/**
	 * Upload file to API.
	 *
	 * @param string $file_path Local file path.
	 * @param array  $data Data to send with upload request.
	 *
	 * @return bool|null
	 */
	public static function upload( string $file_path, array $data = [] ) {
		$access_token = self::get_access_token();

		if ( null === $access_token ) {
			return null;
		}

		if ( ! file_exists( $file_path ) ) {
			return null;
		}

		$data['media'] = new \CURLFile( $file_path );

		$response = wp_safe_remote_post(
			rtrim( Config::get_instance()->get( 'api_url' ), '/' ) . '/api/upload',
			[
				'headers' => [
					'Authorization' => $access_token['token_type'] . ' ' . $access_token['access_token'],
				],
				'body'    => $data,
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = json_decode( $response['body'] ?? '', true );

		return ! empty( $response_body['success'] ) && $response_body['success'];
	}

	/**
	 * Delete file.
	 *
	 * @param string $file_path File path of file to be deleted.
	 *
	 * @return bool
	 */
	public static function delete( string $file_path ): bool {
		$access_token = self::get_access_token( 'DELETE' );

		if ( null === $access_token ) {
			return false;
		}

		$response = wp_safe_remote_request(
			rtrim( Config::get_instance()->get( 'api_url' ), '/' ) . '/api/delete?' . http_build_query( [ 'path' => $file_path ] ),
			[
				'method'  => 'DELETE',
				'headers' => [
					'Authorization' => $access_token['token_type'] . ' ' . $access_token['access_token'],
				],
			],
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = json_decode( $response['body'] ?? '', true );

		return ! empty( $response_body['success'] ) && $response_body['success'];
	}
}
