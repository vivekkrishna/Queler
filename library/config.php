<?php //Database connection settings
defined('DBH') ? null : define('DBH', $_SERVER['RDS_HOSTNAME']);
defined('DBU') ? null : define('DBU', $_SERVER['RDS_USERNAME']);
defined('DBPW') ? null : define('DBPW', $_SERVER['RDS_PASSWORD']);
defined('DBN') ? null : define('DBN', $_SERVER['RDS_DB_NAME']);
defined('DBTP') ? null : define('DBTP', $_SERVER['QuelerDBTablesPrefix']);

//Define your web accessible link to this script, including http:// or https:// with TRAILING SLASH / in the end !IMPORTANT
defined('WEB_LINK') ? null : define('WEB_LINK', 'http://www.queler-env.b7zekbinek.us-west-2.elasticbeanstalk.com/');
defined('ERROR_LINK') ? null : define('ERROR_LINK', WEB_LINK);
defined('UPL_FILES') ? null : define('UPL_FILES', WEB_LINK . 'public');

//Facebook API Credentials, get them from https://developers.facebook.com/apps
$facebook_api = array("secret" => $_SERVER['QuelerFacebookSecret'], "id" => $_SERVER['QuelerFacebookClientID']);

//Google API Credentials, get them from https://console.developers.google.com
$google_api = array("secret" => $_SERVER['QuelerGoogleWebSigninClientSecret'], "id" => $_SERVER['QuelerGoogleWebSigninClientID']);

//Google Captcha Info, get them from https://www.google.com/recaptcha/admin
$captcha_info = array("secret" => $_SERVER['QuelerGoogleRecaptchaSecretKey'], "sitekey" => $_SERVER['QuelerGoogleRecaptchaSiteKey']);

//Google Analytics Info, get them from https://analytics.google.com/analytics/web/
$analytics_info = Array("UA" => $_SERVER['QuelerGoogleAnalyticsID']);

//AddThis Info, get them from https://www.addthis.com/dashboard/
$addthis_info = Array("ra" => $_SERVER['QuelerAddThisID']);

$current = dirname(__FILE__);

require_once($current . "/url_mapper.php");

?>