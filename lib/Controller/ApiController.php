<?php

namespace OCA\ScienceMesh\Controller;

use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCA\ScienceMesh\AppConfig;
use OCA\ScienceMesh\Crypt;
use OCA\ScienceMesh\DocumentService;
use OCA\ScienceMesh\RevaHttpClient;
use OCP\AppFramework\Http\DataResponse;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\AppFramework\Http\TextPlainResponse;
use OCP\AppFramework\Http;
use OCA\Sciencemesh\ServerConfig;
use OCP\IConfig;

/**
 * Settings controller for the administration page
 */
class ApiController extends Controller
{
    private $logger;
	private $config;
	private $urlGenerator;
	private $serverConfig;
	private $sciencemeshConfig;
	private $userId;
	private $db;
	const CATALOG_URL = "https://iop.sciencemesh.uni-muenster.de/iop/mentix/sitereg";

	/**
	 * @param string $AppName - application name
	 * @param IRequest $request - request object
	 * @param IURLGenerator $urlGenerator - url generator service
	 * @param IL10N $trans - l10n service
	 * @param ILogger $logger - logger
	 * @param AppConfig $config - application configuration
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		IL10N $trans,
		ILogger $logger,
		AppConfig $config,
		IConfig $sciencemeshConfig,
		IDBConnection $db,
		$userId
	) {
        parent::__construct($AppName, $request);
		$this->serverConfig = new \OCA\ScienceMesh\ServerConfig($sciencemeshConfig);

		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
		$this->config = $config;
		$this->sciencemeshConfig = $sciencemeshConfig;
        $this->db = $db;
		$this->request = $request;
    }

    /**
     * Check if the request is authenticated by comparing the request's API key with the stored revaLoopbackSecret.
     *
     * @param IRequest $request
     * @return bool
     */
    public function authentication($request)
    {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from('appconfig')
            ->where(
                $qb->expr()->eq('appid', $qb->createNamedParameter('sciencemesh', IQueryBuilder::PARAM_STR))
            )
            ->andWhere(
                $qb->expr()->eq('configkey', $qb->createNamedParameter('revaLoopbackSecret', IQueryBuilder::PARAM_STR))
            );

        $cursor = $qb->execute();
        $row = $cursor->fetchAll();

        if ($row[0]['configvalue'] == $this->request->getHeader('apikey')) {
            return true;
        } else {
            return false;
        }
    }
	
	/**
	 * @NoCSRFRequired
	 */
	public function addToken($initiator, $request){
		if(!$this->authentication($this->request)) return new DataResponse((['message' => 'Authentication failed!','status' => 412, 'data' => null]), Http::STATUS_INTERNAL_SERVER_ERROR);

		if(!$this->request->getHeader('tokenValue') and !$initiator and !$this->request->getHeader('expiry_date') and !$this->request->getHeader('description')){
			return new DataResponse(['message' => 'values are not provided properly!','status' => 412, 'data' => null], Http::STATUS_OK);
		}

		$qb = $this->db->getQueryBuilder();

        $qb->select('*')
		->from('ocm_tokens')
		->where(
			$qb->expr()->eq('initiator', $qb->createNamedParameter($initiator, IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->eq('token', $qb->createNamedParameter($this->request->getHeader('tokenValue'), IQueryBuilder::PARAM_STR))
		);
        $cursor = $qb->execute();
        $row = $cursor->fetchAll();
		
		$expiry_date = isset($expiry_date) ? $expiry_date : time();

		if(empty($row)){
			$qb->insert('ocm_tokens')
			->values(
				array(
					'token' => $qb->createNamedParameter($this->request->getHeader('tokenValue'), IQueryBuilder::PARAM_STR),
					'initiator' => $qb->createNamedParameter($initiator, IQueryBuilder::PARAM_STR),
					'expiration' => $qb->createNamedParameter($expiry_date, IQueryBuilder::PARAM_STR),
					'description' => $qb->createNamedParameter($this->request->getHeader('description'), IQueryBuilder::PARAM_STR)
				)
			);
			$cursor = $qb->execute();
		}else{
			$cursor = 0;
		}

		if($cursor)
			return new DataResponse((['message' => 'Token added!','status' => 200, 'data' => $cursor]), Http::STATUS_OK);
		else if($cursor == 0)
			return new DataResponse((['message' => 'Token already exists!','status' => 204, 'data' => 0]), Http::STATUS_OK);
		else
			return new DataResponse((['message' => 'Token added failed!','status' => 400, 'data' => 0]), Http::STATUS_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @NoCSRFRequired
	 */
	public function getToken($initiator){

		if(!$this->authentication($this->request)) return new DataResponse((['message' => 'Authentication failed!','status' => 412, 'data' => null]), Http::STATUS_INTERNAL_SERVER_ERROR);

		$qb = $this->db->getQueryBuilder();

        $qb->select('*')
		->from('ocm_tokens')
		->where(
			$qb->expr()->eq('initiator', $qb->createNamedParameter($initiator, IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->eq('token', $qb->createNamedParameter($this->request->getHeader('tokenValue'), IQueryBuilder::PARAM_STR))
		);

        $cursor = $qb->execute();
        $row = $cursor->fetchAll();

		if(empty($row)){
			return new DataResponse((['message' => 'No Token found!','status' => 201, 'data' => '']), Http::STATUS_OK);
		}else{
			return new DataResponse((['message' => 'Token found!','status' => 200, 'data' => $row]), Http::STATUS_OK);
		}
		
	}

	
	/**
	 * @NoCSRFRequired
	 */
	public function tokensList($initiator){

		if(!$this->authentication($this->request)) return new DataResponse((['message' => 'Authentication failed!','status' => 412, 'data' => null]), Http::STATUS_INTERNAL_SERVER_ERROR);

		$qb = $this->db->getQueryBuilder();

        $qb->select('*')
		->where(
			$qb->expr()->eq('initiator', $qb->createNamedParameter($initiator, IQueryBuilder::PARAM_STR))
		)
		->from('ocm_tokens');

        $cursor = $qb->execute();
        $row = $cursor->fetchAll();
		return new DataResponse((['message' => 'Token listed!','status' => 200, 'data' => $row]), Http::STATUS_OK);
	}

	
	/**
	 * @NoCSRFRequired
	 */
	public function addRemoteUser($initiator){

		if(!$this->authentication($this->request)) return new DataResponse((['message' => 'Authentication failed!','status' => 412, 'data' => null]), Http::STATUS_INTERNAL_SERVER_ERROR);
		
		if(!$this->request->getHeader('opaqueUserId') and !$this->request->getHeader('idp') and !$this->request->getHeader('email') and !$this->request->getHeader('displayName')){
			return new DataResponse((['message' => 'values are not provided properly!','status' => 412, 'data' => null]), Http::STATUS_OK);
		}

		$qb = $this->db->getQueryBuilder();
		

        $qb->select('*')
		->from('ocm_remote_users')
		->where(
			$qb->expr()->eq('opaque_user_id', $qb->createNamedParameter($this->request->getHeader('opaqueUserId'), IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->eq('idp', $qb->createNamedParameter($this->request->getHeader('idp'), IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->eq('email', $qb->createNamedParameter($this->request->getHeader('email'), IQueryBuilder::PARAM_STR))
		);
        $cursor = $qb->execute();
        $row = $cursor->fetchAll();
		

		if(empty($row)){
			$qb->insert('ocm_remote_users')
			->values(
				array(
					'initiator' => $qb->createNamedParameter($initiator, IQueryBuilder::PARAM_STR),
					'opaque_user_id' => $qb->createNamedParameter($this->request->getHeader('opaqueUserId'), IQueryBuilder::PARAM_STR),
					'idp' => $qb->createNamedParameter($this->request->getHeader('idp'), IQueryBuilder::PARAM_STR),
					'email' => $qb->createNamedParameter($this->request->getHeader('email'), IQueryBuilder::PARAM_STR),
					'display_name' => $qb->createNamedParameter($this->request->getHeader('displayName'), IQueryBuilder::PARAM_STR)
				)
			);
			$cursor = $qb->execute();
		}else{
			$cursor = 0;
		}

		if($cursor || !empty($row))
			if(!empty($row))
				return new DataResponse((['message' => 'User exists!','status' => 201, 'data' => $row]), Http::STATUS_OK);
			if($cursor)
				return new DataResponse((['message' => 'User added!','status' => 200, 'data' => $cursor]), Http::STATUS_OK);
		else
			return new DataResponse((['message' => 'User does not added!','status' => 201, 'data' => 0]), Http::STATUS_OK);
	}

	
	/**
	 * @NoCSRFRequired
	 */
	public function getRemoteUser($initiator){

		if(!$this->authentication($this->request)) return new DataResponse((['message' => 'Authentication failed!','status' => 412, 'data' => null]), Http::STATUS_INTERNAL_SERVER_ERROR);

		$qb = $this->db->getQueryBuilder();

        $qb->select('*')
		->from('ocm_remote_users')
		->where(
			$qb->expr()->eq('initiator', $qb->createNamedParameter($initiator, IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->eq('idp', $qb->createNamedParameter($this->request->getHeader('idp'), IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->eq('opaque_user_id', $qb->createNamedParameter($this->request->getHeader('opaqueUserId'), IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->eq('email', $qb->createNamedParameter($this->request->getHeader('email'), IQueryBuilder::PARAM_STR))
		);

        $cursor = $qb->execute();
        $row = $cursor->fetchAll();

		if(empty($row)){
			return new DataResponse((['message' => 'User not found!','status' => 201, 'data' => '']), Http::STATUS_OK);
		}else{
			return new DataResponse((['message' => 'User found!','status' => 200, 'data' => $row]), Http::STATUS_OK);
		}
		
	}


	/**
	 * @NoCSRFRequired
	 */
	public function findRemoteUser($initiator){

		$qb = $this->db->getQueryBuilder();

        $qb->select('*')
		->from('ocm_remote_users')
		->where(
			$qb->expr()->eq('initiator', $qb->createNamedParameter($initiator, IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->orX(
				$qb->expr()->like('opaque_user_id', $qb->createNamedParameter($this->request->getHeader('opaqueUserId'), IQueryBuilder::PARAM_STR)),
				$qb->expr()->like('idp', $qb->createNamedParameter($this->request->getHeader('idp'), IQueryBuilder::PARAM_STR)),
				$qb->expr()->like('email', $qb->createNamedParameter($this->request->getHeader('email'), IQueryBuilder::PARAM_STR)),
				$qb->expr()->like('display_name', $qb->createNamedParameter($this->request->getHeader('displayName'), IQueryBuilder::PARAM_STR))
			)
		);

        $cursor = $qb->execute();
        $row = $cursor->fetchAll();

		if(empty($row)){
			return new DataResponse((['message' => 'User not found!','status' => 200, 'data' => '']), Http::STATUS_OK);
		}else{
			return new DataResponse((['message' => 'User found!','status' => 200, 'data' => $row]), Http::STATUS_OK);
		}
		
	}
}