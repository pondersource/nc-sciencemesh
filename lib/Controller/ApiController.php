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
	public function __construct($AppName,
								IRequest $request,
								IURLGenerator $urlGenerator,
								IL10N $trans,
								ILogger $logger,
								AppConfig $config,
								IConfig $sciencemeshConfig,
								IDBConnection $db,
		$userId
	)
	{
        parent::__construct($AppName, $request);
		$this->serverConfig = new \OCA\ScienceMesh\ServerConfig($sciencemeshConfig);

		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
		$this->config = $config;
		$this->sciencemeshConfig = $sciencemeshConfig;
		$this->userId = $userId;
        $this->db = $db;
		$this->request = $request;

        // here we will add the calling function
        // needs route check and authenticatation
        if(!$this->authentication($this->request)) return new DataResponse(["status" => "401", "message" => "authentication failed", "data" => NULL]);

		$method = is_string($this->request->getParam('method')) ? $this->request->getParam('method') : 'authentication';

		return $this->$method($this->request);
    }

    public function authentication($request){
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

		if($row[0]['configvalue'] == $request->getParam('apikey')) return true;
		else return false;
        return $row;
    }

	public function addToken($request){
        
		if(!$request->getParam('tokenValue') and !$request->getParam('initiator') and !$request->getParam('expiry_date') and !$request->getParam('description')){
			return new TextPlainResponse(['message' => 'values are not provided properly!','status' => 412, 'data' => null], Http::STATUS_OK);
		}

		$qb = $this->db->getQueryBuilder();
		

        $qb->select('*')
		->from('ocm_tokens')
		->where(
			$qb->expr()->eq('token', $qb->createNamedParameter('tokenValue', IQueryBuilder::PARAM_STR))
		)
		->andWhere(
			$qb->expr()->gt('expiry_date', $qb->createNamedParameter('revaLoopbackSecret', IQueryBuilder::PARAM_STR))
		);
		


        $qb->insert('ocm_tokens')
		->values(
			array(
				'token' => $qb->createNamedParameter($request->getParam('tokenValue'), IQueryBuilder::PARAM_STR),
				'initiator' => $qb->createNamedParameter($request->getParam('initiator'), IQueryBuilder::PARAM_STR),
				'expiration' => $qb->createNamedParameter($request->getParam('expiry_date'), IQueryBuilder::PARAM_STR),
				'description' => $qb->createNamedParameter($request->getParam('description'), IQueryBuilder::PARAM_STR)
			)
		);
		
        $cursor = $qb->execute();

		if($cursor)
			return new TextPlainResponse(['message' => 'Token added!','status' => 200, 'data' => $cursor], Http::STATUS_OK);
		else
			return new TextPlainResponse(['message' => 'Token is not added!','status' => 200, 'data' => 0], Http::STATUS_OK);

	}

	
	public function getToken(){

	}

	
	public function tokensList(){

	}

	
	public function addRemoteUser(){

	}

	
	public function getRemoteUser(){

	}


    
}