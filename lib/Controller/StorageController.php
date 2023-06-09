<?php

namespace OCA\ScienceMesh\Controller;

use OCA\ScienceMesh\AppInfo\Application;
use OCA\ScienceMesh\AppInfo\ScienceMeshApp;
use OCA\ScienceMesh\Share\ScienceMeshShare;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\IURLGenerator;
use OCP\IConfig;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCA\ScienceMesh\ShareProvider\ScienceMeshShareProvider;
use OCP\Share\IShare;
use OCP\IDBConnection;

class StorageController extends Controller {
	private $scienceMeshShareProvider;
	private $userId;
	protected $request;
	protected $connection;

	public function __construct($AppName, IRequest $request, IDBConnection $connection, $userId) {
		parent::__construct($AppName, $request);
		$this->userId = $userId;
		$this->request = $request;
		$this->connection = $connection;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function handlePost($userId){
		
		$id = $this->request->getParam('path');
		$params = ['id' => $id];

		$ch = curl_init();

		curl_setopt_array($ch, array(
			CURLOPT_URL => $_SERVER['HTTP_ORIGIN'].'/index.php/apps/files_sharing/api/externalShares?id='.$id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_HTTPHEADER => array(
				'Cookie:'.$_SERVER['HTTP_COOKIE'], 'requesttoken:'.$_SERVER['HTTP_REQUESTTOKEN'],'Content-type: application/x-www-form-urlencoded; charset=UTF-8'
			  )
		  ));
		
		$response = curl_exec($ch);		
		curl_close($ch);

		$sql = $this->connection->getQueryBuilder();
		$sql->delete('notifications')
			->where($sql->expr()->eq('object_id', $sql->createParameter('object_id')))
			->setParameter('object_id', $id)
			->andWhere($sql->expr()->like('user', $sql->createParameter('user')))
			->setParameter('user', $userId);

		$sql->execute();
	}

	
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function handleDelete($userId){
		$id = $this->request->getParam('path');
		$params = ['id' => $id];

		$ch = curl_init();

		curl_setopt_array($ch, array(
			CURLOPT_URL => $_SERVER['HTTP_ORIGIN'].'/index.php/apps/files_sharing/api/externalShares?id='.$id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'DELETE',
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_HTTPHEADER => array(
				'Cookie:'.$_SERVER['HTTP_COOKIE'], 'requesttoken:'.$_SERVER['HTTP_REQUESTTOKEN'],'Content-type: application/x-www-form-urlencoded; charset=UTF-8'
			  )
		  ));
		
		$response = curl_exec($ch);		
		curl_close($ch);

		$sql = $this->connection->getQueryBuilder();
		$sql->delete('notifications')
			->where($sql->expr()->eq('object_id', $sql->createParameter('object_id')))
			->setParameter('object_id', $id)
			->andWhere($sql->expr()->like('user', $sql->createParameter('user')))
			->setParameter('user', $userId);
		$sql->execute();

	}


}
