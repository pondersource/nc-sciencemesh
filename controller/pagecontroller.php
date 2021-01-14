<?php
/**
 * ownCloud - sciencemesh
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Hugo Gonzalez Labrador <github@hugo.labkode.com>
 * @copyright Hugo Gonzalez Labrador 2020
 */

namespace OCA\ScienceMesh\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\ILogger;
use OCP\AppFramework\Controller;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Http\Client\IClientService;
use OCP\AppFramework\Http;

class PageController extends Controller {
    	private $logger;
	private $userId;
	protected $connection;

	/** @var IClientService */
	private $httpClientService;

	public function __construct($AppName, 
		IRequest $request,
		$UserId,
		IDBConnection $connection, 
		IClientService $httpClientService,
                ILogger $logger) {

		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->connection = $connection; 
		$this->httpClientService = $httpClientService;
		$this->logger = $logger;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$params = ['user' => $this->userId];
		return new TemplateResponse('sciencemesh', 'main', $params);  // templates/main.php
	}

	/**
	 * Simply method that posts back the payload of the request
	 * @NoAdminRequired
	 */
	public function doEcho($echo) {
		return new DataResponse(['echo' => $echo]);
	}

	/**
	 * @PublicPage
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getMetrics() {
		// for localhost requests is needed to add
		// 'allow_local_remote_servers' => true,
		// to config.php
		$client = $this->httpClientService->newClient();
		try {
			$response = $client->get("http://localhost:5550/metrics", [
				'timeout' => 10,
				'connect_timeout' => 10,
			]);

			if ($response->getStatusCode() === Http::STATUS_OK) {
				//$result = json_decode($response->getBody(), true);
				//return (is_array($result)) ? $result : [];
				echo($response->getBody());
				return new Http\Response();
			}
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @PublicPage
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getInternalMetrics() {
		$metrics = $this->getInternal();
		$settings = $this->loadSettings();
		if (!$settings) {
			return new JSONResponse([]);
		}

		$payload = ["metrics" => $metrics, "settings" => $settings];
		return new JSONResponse($payload);
	}

	private function loadSettings(){
		$query = \OC::$server->getDatabaseConnection()->getQueryBuilder();
		$query->select('*')->from('sciencemesh');
		$result = $query->execute();
		$row = $result->fetch();
		$result->closeCursor();
		return $row;
	}

	private function getInternal() {
		$queryBuilder = $this->connection->getQueryBuilder();
		$queryBuilder->select($queryBuilder->createFunction('count(*)'))
			->from('users');
		$result = $queryBuilder->execute();
		$count = $result->fetchColumn();
		$hostname = \OCP\Util::getServerHostName();
		$params = [
			'total_users' => intval($count),
		];
		return $params;
	}


/*
    {
        "Name": "OC-Test@WWU",
        "FullName": "ownCloud Test at University of Muenster",
        "Homepage": "http://oc-test.uni-muenster.de",            
        "Description": "ownCloud Test Instance of University of Muenster",
        "CountryCode": "DE",
        "Services": [
            {
                "Type": {
                    "Name": "REVAD"
                },
                "Name": "oc-test.uni-muenster.de - REVAD",
                "URL": "https://oc-test.uni-muenster.de/revad",
                "IsMonitored": true,
                "Properties": {
                    "METRICS_PATH": "/revad/metrics"
                },
                "Host": "octest-test.uni-muenster.de"
            }
        ]
    }
*/

}
