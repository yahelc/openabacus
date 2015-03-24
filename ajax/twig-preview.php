<?php
include "ajax.inc.php";

include "lib/twig.php";
echo generateSQLFromTwig($_POST["twig"], $twig, $template);

?>