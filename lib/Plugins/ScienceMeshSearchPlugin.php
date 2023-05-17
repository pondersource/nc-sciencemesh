<?php

namespace OCA\ScienceMesh\Plugins;

use OC\User\User;
use OCP\IConfig;
use OCP\Share;
use OCP\IUserManager;
use OCP\IUserSession;
use OCA\ScienceMesh\RevaHttpClient;
use OCA\ScienceMesh\AppInfo\ScienceMeshApp;
use OCP\Contacts\IManager;
use OCP\Util\UserSearch;

class ScienceMeshSearchPlugin {
	protected $shareeEnumeration;

	/** @var IManager */
	protected $contactsManager;

	/** @var int */
	protected $offset = 0;

	/** @var int */
	protected $limit = 10;

	/** @var UserSearch*/
	protected $userSearch;

	/** @var IConfig */
	private $config;

	/** @var string */
	private $userId = '';

	public function __construct(IManager $contactsManager, IConfig $config, IUserManager $userManager, IUserSession $userSession, UserSearch $userSearch) {
		$this->config = $config;
		$user = $userSession->getUser();
		$this->contactsManager = $contactsManager;
		$this->userSearch = $userSearch;
		if ($user !== null) {
			$this->userId = $user->getUID();
		}
		$this->shareeEnumeration = $this->config->getAppValue('core', 'shareapi_allow_share_dialog_user_enumeration', 'yes') === 'yes';
		$this->revaHttpClient = new RevaHttpClient($this->config);
	}

	public function search($search, $limit, $offset, ISearchResult $searchResult) {
		$users = json_decode($this->revaHttpClient->findAcceptedUsers($this->userId), true);

		$result = [];
		foreach ($users as $user) {
			$domain = (str_starts_with($user['id']['idp'], "http") ? parse_url($user['id']['idp'])["host"] : $user['id']['idp']);
			$exactResults[] = [
				"label" => "Label",
				"uuid" => $user['id']['opaque_id'],
				"name" => $user['display_name'] ."@". $domain, // FIXME: should this be just the part before the @ sign?
				"type" => "ScienceMesh",
				"value" => [
					"shareType" => IShare::TYPE_SCIENCEMESH,
					"shareWith" => $user['id']['opaque_id'] ."@". $domain, // FIXME: should this be just the part before the @ sign?
					"server" => $user['id']['idp']
				]
			];
		}

		error_log("returning other results:");
		error_log(var_export($otherResults, true));

		$result = array_merge($result, $otherResults);

		return $result;
	}
}
