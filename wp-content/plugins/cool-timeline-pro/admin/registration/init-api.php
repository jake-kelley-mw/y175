<?php
namespace CoolTimelineProREG;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTP_ApiConf{
    const PLUGIN_NAME = 'Cool Timeline Pro';
    const PLUGIN_VERSION = CTLPV;
    const PLUGIN_PREFIX = 'ctp';
    const PLUGIN_AUTH_PAGE = 'timeline-addons-license';
    const PLUGIN_URL = CTP_PLUGIN_URL;
}

    require_once 'class.settings-api.php';
    require_once 'CoolTimelineProBase.php';
    require_once 'api-auth-settings.php';

	new CTP_Settings();