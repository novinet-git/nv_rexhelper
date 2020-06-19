<?php
define("FE", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
define("ASSETS", FE . "/theme/public/assets/frontend/");
define("ROOT", rex_path::base());
define("MEDIA", FE . "/media/");