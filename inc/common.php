<?php
require_once "$home/inc/root.php";
cRoot::set_root($home);

require_once("$phpinc/ckinc/debug.php");
cDebug::check_GET_or_POST();
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	

//####################################################################
require_once("$appdlib/appdynamics.php");
require_once("$appdlib/common.php");
require_once("$appdlib/account.php");
require_once("$appdlib/metrics.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-metrics.php");

?>