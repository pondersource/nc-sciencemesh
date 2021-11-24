<?php
namespace OCA\ScienceMesh\Controller;

use OCA\ScienceMesh\ServerConfig;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\Contacts\IManager;
use OCP\IURLGenerator;
use OCP\IConfig;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Notification\IManager as INotificationManager;
use OCP\IUserSession;
use OCA\ScienceMesh\RevaHttpClient;

class AppController extends Controller {
        private $userId;
        private $userManager;
        private $urlGenerator;
        private $config;
        private $userSession;

        public function __construct($AppName, ITimeFactory $timeFactory, INotificationManager $notificationManager, IRequest $request, IConfig $config, IUserManager $userManager, IURLGenerator $urlGenerator, $userId, IUserSession $userSession){
                parent::__construct($AppName, $request);
              
                $this->userId = $userId;
                $this->userManager = $userManager;
                $this->request     = $request;
                $this->urlGenerator = $urlGenerator;
                $this->notificationManager = $notificationManager;
                $this->timeFactory = $timeFactory;
                $this->config = new \OCA\ScienceMesh\ServerConfig($config, $urlGenerator, $userManager);
                $this->userSession = $userSession;
        }

        /**
         * @NoAdminRequired
         * @NoCSRFRequired
         */
        public function launcher() {
                $revaClient = new RevaHttpClient();
/*
                $revaResult = $revaClient->createShare(array(
                        "path" => "/share",
                        "recipientUsername" => "marie",
                        "recipientHost" => "localhost:17000"
                ));
*/
                $revaResult = $revaClient->ocmProvider();  
                $launcherData = array(
                        "reva" => json_encode($revaResult, JSON_PRETTY_PRINT)
                );

                $templateResponse = new TemplateResponse('sciencemesh', 'launcher', $launcherData);
		$policy = new ContentSecurityPolicy();
		$policy->addAllowedStyleDomain("data:");
		$policy->addAllowedScriptDomain("'self'");
		$policy->addAllowedScriptDomain("'unsafe-inline'");
		$policy->addAllowedScriptDomain("'unsafe-eval'");
                $templateResponse->setContentSecurityPolicy($policy);
                return $templateResponse;
        }

        /**
         * @NoAdminRequired
         * @NoCSRFRequired
         */
        public function notifications() {
                $user = $this->userSession->getUser();
                //$user = $this->userManager->get("alice");
                $shortMessage = "ScienceMesh notification!";
                $longMessage = "A longer notification message from ScienceMesh";
                $notification = $this->notificationManager->createNotification();

                $time = $this->timeFactory->getTime();
                $datetime = new \DateTime();
                $datetime->setTimestamp($time);

                try {
                        $acceptAction = $notification->createAction();
                        $acceptAction->setLabel('accept')->setLink("shared", "GET");

                        $declineAction = $notification->createAction();
                        $declineAction->setLabel('decline')->setLink("shared", "GET");

                        $notification->setApp('sciencemesh')
                                ->setUser($user->getUID())
                                ->setDateTime($datetime)
                                ->setObject('sciencemesh', dechex($time))
                                ->setSubject('remote_share', [$shortMessage])
                                ->addAction($acceptAction)
                                ->addAction($declineAction)
                        ;
                        if ($longMessage !== '') {
                                $notification->setMessage('remote_share', [$longMessage]);
                        }

                        $this->notificationManager->notify($notification);
                } catch (\InvalidArgumentException $e) {
                        return new DataResponse(null, Http::STATUS_INTERNAL_SERVER_ERROR);
                }

                $notificationsData = array(
                );
                $templateResponse = new TemplateResponse('sciencemesh', 'notifications', $notificationsData);
                $policy = new ContentSecurityPolicy();
                $policy->addAllowedStyleDomain("data:");
                $policy->addAllowedScriptDomain("'self'");
                $policy->addAllowedScriptDomain("'unsafe-inline'");
                $policy->addAllowedScriptDomain("'unsafe-eval'");
                $templateResponse->setContentSecurityPolicy($policy);
                return $templateResponse;
        }

        /**
         * @NoAdminRequired
         * @NoCSRFRequired
         */
        public function invitations() {
                $invitationsData = array(
                );
                $templateResponse = new TemplateResponse('sciencemesh', 'invitations', $invitationsData);
                $policy = new ContentSecurityPolicy();
                $policy->addAllowedStyleDomain("data:");
                $policy->addAllowedScriptDomain("'self'");
                $policy->addAllowedScriptDomain("'unsafe-inline'");
                $policy->addAllowedScriptDomain("'unsafe-eval'");
                $templateResponse->setContentSecurityPolicy($policy);
                return $templateResponse;
        }

        /**
         * @NoAdminRequired
         * @NoCSRFRequired
         */
        public function contacts() {
                $contactsData = array(
                );
                $templateResponse = new TemplateResponse('sciencemesh', 'contacts', $contactsData);
        	$policy = new ContentSecurityPolicy();
		$policy->addAllowedStyleDomain("data:");
		$policy->addAllowedScriptDomain("'self'");
		$policy->addAllowedScriptDomain("'unsafe-inline'");
		$policy->addAllowedScriptDomain("'unsafe-eval'");
                $templateResponse->setContentSecurityPolicy($policy);
                return $templateResponse;
        }
}