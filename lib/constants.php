<?php
const FE = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
const ASSETS = FE . "/theme/public/assets/frontend/";
const ROOT = $_SERVER["DOCUMENT_ROOT"];
const MEDIA = FE . "/media/";