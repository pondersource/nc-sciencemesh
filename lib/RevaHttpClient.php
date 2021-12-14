<?php
/**
 * @copyright Copyright (c) 2021, PonderSource
 *
 * @author Yvo Brevoort <yvo@pondersource.nl>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\ScienceMesh;

use OCP\IConfig;

/**
 * Class RevaHttpClient
 *
 * This class is a helper to handle the outbound HTTP connections from Nextcloud to Reva
 *
 * @package OCA\ScienceMesh\RevaHttpClient
 */
class RevaHttpClient {
	private $client;
	private $revaUrl;
	private $revaUser;
	private $revaSharedSecret;
		
	/**
	 * RevaHttpClient constructor.
	 *
	 */
	public function __construct(IConfig $config) {
		$this->serverConfig = new \OCA\ScienceMesh\ServerConfig($config);
		$this->revaUrl = $this->serverConfig->getIopUrl();
		$this->revaSharedSecret = $this->serverConfig->getRevaLoopbackSecret();
		$this->curlDebug = true;
	}

	private function curlGet($url, $user, $params = []) {
		$ch = curl_init();
		if (sizeof($params)) {
			$url .= "?" . http_build_query($params);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($this->revaUser && $this->revaSharedSecret) {
			curl_setopt($ch, CURLOPT_USERPWD, $user.":".$this->revaSharedSecret);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}

		if ($this->curlDebug) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			$streamVerboseHandle = fopen('php://temp', 'w+');
			curl_setopt($ch, CURLOPT_STDERR, $streamVerboseHandle);
		}
		
		$output = curl_exec($ch);

		if ($this->curlDebug) {
			rewind($streamVerboseHandle);
			$verboseLog = stream_get_contents($streamVerboseHandle);
			$output = $verboseLog . $output;
		}
		
		return $output;
	}
	private function curlPost($url, $user, $params = []) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		// curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_PRETTY_PRINT));
		if ($this->revaSharedSecret) {
			error_log("POST to Reva $url $user:$this->revaSharedSecret ".json_encode($params));
			curl_setopt($ch, CURLOPT_USERPWD, $user.":".$this->revaSharedSecret);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public function revaGet($method, $user, $params = []) {
		$url = $this->revaUrl . $method;
		return $this->curlGet($url, $user, $params);
	}
		
	public function revaPost($method, $user, $params = []) {
		$url = $this->revaUrl . $method;
		return $this->curlPost($url, $user, $params);
	}
	
	public function createShare($user, $params) {
		if (!isset($params['path'])) {
			throw new Exception("Missing path", 400);
		}
		if (!isset($params['recipientUsername'])) {
			throw new Exception("Missing recipientUsername", 400);
		}
		if (!isset($params['recipientHost'])) {
			throw new Exception("Missing recipientHost", 400);
		}
		return $this->revaPost('send', $user, $params);
	}
	public function ocmProvider() {
		return $this->revaGet('ocm-provider');
	}
	public function findAcceptedUsers($user) {
		$users = $this->revaPost('find-accepted-users', $user);
		return $users;

		/*
			$users = [
				"accepted_users" => [
					[
						"id" => [
							"idp" => "https://revanc2.docker",
							"opaque_id" => "marie"
						],
						"display_name" => "Marie Curie",
						"mail" => "marie@revanc2.docker"
					]
				]
			];
			return $users;
		*/
	}
}
