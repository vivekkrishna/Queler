<?php //Database connection settings
defined('DBH') ? null : define ('DBH' , 'aa1ipe9l9hafvx3.c8xa5yyvumqn.us-west-2.rds.amazonaws.com');
defined('DBU') ? null : define ('DBU' , 'choppak');
defined('DBPW') ? null : define ('DBPW' , '10011993');
defined('DBN') ? null : define ('DBN' , 'ebdb');
defined('DBTP') ? null : define ('DBTP' , 'Queler_Prod_');

//Define your web accessible link to this script, including http:// or https:// with TRAILING SLASH / in the end !IMPORTANT
defined('WEB_LINK') ? null : define('WEB_LINK' , 'http://www.queler-env.b7zekbinek.us-west-2.elasticbeanstalk.com/');
defined('ERROR_LINK') ? null : define('ERROR_LINK' , WEB_LINK );
defined('UPL_FILES') ? null : define('UPL_FILES' , WEB_LINK.'public');

//Facebook API Credentials, get them from https://developers.facebook.com/apps
$facebook_api = array("secret"=>"", "id" => "");

//Google API Credentials, get them from https://console.developers.google.com
$google_api = array("secret"=>"", "id" => "");

//Google Captcha Info, get them from https://www.google.com/recaptcha/admin
$captcha_info = array("secret"=>"6LfE7KwUAAAAAE9FhtmSn7L3vQu208Hl0kHtuKCE", "sitekey" => "6LfE7KwUAAAAAFTv83SWOY_l0mbglLsDJx4DBe1o");

//Google Analytics Info, get them from https://analytics.google.com/analytics/web/
$analytics_info = Array("UA" => "AnalyticsID" );

//AddThis Info, get them from https://www.addthis.com/dashboard/
$addthis_info = Array("ra" => "AddThisID" );

$current = dirname(__FILE__);

require_once($current ."/url_mapper.php");

?>