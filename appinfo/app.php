<?php
declare(strict_types=1);
use OCP\Util;

$app = \OC::$server->query(\OCA\ScienceMesh\AppInfo\ScienceMeshApp::class);

$eventDispatcher = \OC::$server->getEventDispatcher();
$eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function(){
    Util::addScript('sciencemesh', 'open-with');
    Util::addStyle('sciencemesh', 'open-with');
});
\OC::$server->getNavigationManager()->add(function () {
    $urlGenerator = \OC::$server->getURLGenerator();

    return [
        // The string under which your app will be referenced in owncloud
        'id' => 'sciencemesh',

        // The sorting weight for the navigation.
        // The higher the number, the higher will it be listed in the navigation
        'order' => 10,

        // The route that will be shown on startup
        'href' => $urlGenerator->linkToRoute('sciencemesh.app.contacts'),

        // The icon that will be shown in the navigation, located in img/
        'icon' => $urlGenerator->imagePath('sciencemesh', 'app.svg'),

        // The application's title, used in the navigation & the settings page of your app
        'name' => \OC::$server->getL10N('sciencemesh')->t('ScienceMesh'),
    ];
});
