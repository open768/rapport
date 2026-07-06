<?php
$root = "do not use";

class cAppGlobals {
    static ?string $root;
    static ?string $appPages;
    static ?string $jsHome, $jsWidgets, $jsAppRest, $jsExtra, $jsImages, $jsInc;
    static ?string $jsThumbNailer, $jsCropper, $jsMosaicer;

    static ?string $phpInc = null;
    static ?string $ckPhpInc = null;
    static ?string $title = "title not set";
    static ?string $dbRoot = null;
    static ?string $ADlib = null;

    static function init(?string $psHome) {
        self::$root = realpath($psHome);

        //configurable things 
        self::$phpInc = self::$root . "/../phpinc";               //disk location of where phpinc can be found by PHP
        self::$ckPhpInc = self::$phpInc . "/ckinc";               //dont modify this line

        require_once self::$ckPhpInc . "/debug.php";              //dont modify this line its needed for the next line
        self::$jsInc = "$psHome/../jsinc";                        //DEV url where jsinc can be found on your webserver 


        //========================================================================================================
        //                   dont modify below this line
        //========================================================================================================
        //app  stuff 
        self::$dbRoot = self::$root . "/[db]";
        self::$appPages = self::$root . "/pages";
        self::$ADlib = self::$phpInc ."/appd";

        //JS stuff 
        self::$jsHome = "$psHome/js";
        self::$jsExtra = self::$jsInc . "/extra";
        self::$jsAppRest = "$psHome/php/rest";
        self::$jsImages = "$psHome/images";
        self::$jsWidgets = self::$jsHome . "/widgets";
    }
}
cAppGlobals::init($home);
