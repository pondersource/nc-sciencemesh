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

class StorageController extends Controller {
	private $scienceMeshShareProvider;
	private $userId;

	public function __construct($AppName, IRequest $request, IConfig $config, IUserManager $userManager, IURLGenerator $urlGenerator, $userId) {
		parent::__construct($AppName, $request);
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function handlePost($userId){
		// $scienceMeshApplication = new Application();
		// $this->scienceMeshShareProvider = $scienceMeshApplication->getScienceMeshShareProvider();
		// $getReceivedShares = $this->scienceMeshShareProvider->getReceivedShares($userId);
		// error_log(json_encode($getReceivedShares));
		

		// $curl = curl_init();

		// curl_setopt_array($curl, array(
		// 	CURLOPT_URL => 'https://oc2.docker/index.php/apps/files_sharing/api/externalShares',
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_MAXREDIRS => 10,
		// 	CURLOPT_TIMEOUT => 30,
		// 	CURLOPT_FOLLOWLOCATION => false,
		// 	CURLOPT_CUSTOMREQUEST => 'POST',
		// 	CURLOPT_POSTFIELDS => array('id'=>2),
		// ));

		// $response = curl_exec($curl);
		// $info = curl_getinfo($curl);

		// curl_close($curl);
		// echo $response;
	}

	public function handleDelete(){
		error_log(json_encode($this->request->getParams()));
	}


}
