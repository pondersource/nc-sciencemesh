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
use OCP\AppFramework\Controller;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class PageController extends Controller {


	private $userId;
	protected $connection;

	public function __construct($AppName, IRequest $request, $UserId, IDBConnection $connection){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->connection = $connection;
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
	public function getInternalMetrics() {
		$payload = $this->getMetrics();
		return new DataResponse($payload);
	}

	private function getMetrics() {
		$queryBuilder = $this->connection->getQueryBuilder();
		$queryBuilder->select($queryBuilder->createFunction('count(*)'))
			->from('users');
		$result = $queryBuilder->execute();
		$count = $result->fetchColumn();

		$params = ['total_users' => $count];
		return $params;
	}


}
