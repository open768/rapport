<?php
require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$phpinc/appdynamics/account.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-metrics.php");

?>