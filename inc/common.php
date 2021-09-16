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
require_once("$ADlib/appdynamics.php");
require_once("$ADlib/common.php");
require_once("$ADlib/account.php");
require_once("$ADlib/metrics.php");
require_once("$root/inc/secret.php");
require_once("$root/inc/render.php");
require_once("$root/inc/metrics.php");

?>