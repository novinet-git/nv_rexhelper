<?php
define("FE", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
define("ASSETS", FE . "/theme/public/assets/frontend/");
define("ROOT", $_SERVER["DOCUMENT_ROOT"]);
define("MEDIA", FE . "/media/");