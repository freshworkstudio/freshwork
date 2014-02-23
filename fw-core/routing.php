<?php
$router->redirect("css/(.*)",WWW_DIR."css/$1");
$router->redirect("js/(.*)",WWW_DIR."js/$1");
$router->redirect("404",CORE_WWW_DIR."404.php");