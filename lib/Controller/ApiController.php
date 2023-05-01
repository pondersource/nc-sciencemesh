<?php

namespace OCA\ScienceMesh\Controller;


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

        // here we will add the calling function
        // needs route check and authenticatation
        if(!$this->authentication()) return new DataResponse(["status" => "401", "message" => "authentication failed", "data" => NULL]);
    }

    private function authentication(){

    }

    
}