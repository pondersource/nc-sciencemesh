<?php

namespace OCA\ScienceMesh\Controller;

use Laminas\Diactoros\Response\TextResponse;
use OCA\ScienceMesh\PlainResponse;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\Mail\IMailer;
use OCP\Mail\IMessage;
use OCP\Mail\IMailerException;
use OCP\IURLGenerator;
use OCP\IConfig;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
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
	private $generateToken;
	private $acceptToken;
	private $httpClient;
	private $mailer;

	public function __construct($AppName, ITimeFactory $timeFactory, INotificationManager $notificationManager, IRequest $request, IConfig $config, IUserManager $userManager, IURLGenerator $urlGenerator, $userId, IUserSession $userSession, RevaHttpClient $httpClient, IMailer $mailer) {
		parent::__construct($AppName, $request);
			  
		$this->userId = $userId;
		$this->userManager = $userManager;
		$this->request = $request;
		$this->urlGenerator = $urlGenerator;
		$this->notificationManager = $notificationManager;
		$this->timeFactory = $timeFactory;
		$this->config = $config;
		$this->serverConfig = new \OCA\ScienceMesh\ServerConfig($config, $urlGenerator, $userManager);
		$this->userSession = $userSession;
		$this->httpClient = $httpClient;
		$this->mailer = $mailer;

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

		$notificationsData = [
		];
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
	public function generate() {
		return new TemplateResponse('sciencemesh', 'generate');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function accept() {
		return new TemplateResponse('sciencemesh', 'accept');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function invitationsGenerate() {
		$recipient = $this->request->getParam('email');
		$invitationsData = $this->httpClient->generateTokenFromReva($this->userId, $recipient);
		$inviteLinkStr = $invitationsData["invite_link"];
		
    	if (!$inviteLinkStr) {
			return new PlainResponse("Unexpected response from Reva", Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new PlainResponse("$inviteLinkStr", Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function invitationsSends($AppName) {
		$email = $this->request->getParam('email');
		$token = $this->request->getParam('token');

		$recipientEmail = $email;
		$subject = 'New Token generated by '.$AppName.' send from '.$this->userId ;
		$message = 'You can open this URL to accept the invitation<br>'.$token;

		$mailer = $this->sendNotification($recipientEmail, $subject, $message);
		return new PlainResponse($mailer, Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function contacts() {
		$contactsData = [
		];
		return new TemplateResponse('sciencemesh', 'contacts', $contactsData);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function contactsAccept() {
		$providerDomain = $this->request->getParam('providerDomain');
		$token = $this->request->getParam('token');
		$result = $this->httpClient->acceptInvite($providerDomain, $token, $this->userId);
		return new PlainResponse($result, Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function contactsFindUsers($searchToken = "") {
		$find_users_json = $this->httpClient->findAcceptedUsers($this->userId);

		$find_users = json_decode($find_users_json, false);
		$return_users = array();
		if(strlen($searchToken) > 0){
			if(!empty($find_users)){
				for($i = count($find_users); $i >= 0 ; $i--){
					if(str_contains($find_users[$i]->display_name, $searchToken) and !is_null($find_users[$i])){
						$return_users[] = $find_users[$i];
					}
				}
			}
		}else{
			$return_users = json_decode($find_users_json, false);
		}

		error_log('test:'.json_encode($return_users));
		return new PlainResponse(json_encode($return_users), Http::STATUS_OK);
	}

	public function sendNotification(string $recipient, string $subject, string $message): bool
    {
        try {
            // Create a new email message
            $mail = $this->mailer->createMessage();

            $mail->setTo([$recipient]);
            $mail->setSubject($subject);
            $mail->setPlainBody($message);

            // Set the "from" email address
            $fromEmail = $this->config->getSystemValue('fromemail', 'no-reply@cs3mesh4eosc.eu');	
            $mail->setFrom([$fromEmail]);

            // Send the email
            $this->mailer->send($mail);

			return true;
        } catch (IMailerException $e) {
			error_log(json_encode($e));

			return false;
        }
    }
}
