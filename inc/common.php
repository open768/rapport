<?php
require_once "$home/inc/root.php";
cRoot::set_root($home);

require_once("$phpinc/ckinc/debug.php");
cDebug::check_GET_or_POST();
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/rendercards.php");
require_once("$phpinc/ckinc/renderw3.php");
require_once("$phpinc/ckinc/google.php");
require_once("$phpinc/ckinc/newrelic.php");

//####################################################################
require_once("$ADlib/AD.php");
require_once("$ADlib/common.php");
require_once("$ADlib/account.php");
require_once("$ADlib/metrics.php");
require_once("$root/inc/secret.php");
require_once("$root/inc/render.php");
require_once("$root/inc/metrics.php");

//####################################################################
//no cacheing allowed
//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
date_default_timezone_set('Europe/London');
ob_end_flush(); //no buffering allowed, content is written to screen as soon as available
?>