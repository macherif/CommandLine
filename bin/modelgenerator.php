<?php

require_once '../library/CommandLine/ModelsGenerator.php';
$generator = new CommandLine_ModelsGenerator();
$generator->initZendEnv(realpath('../application'));
$generator->run();

?>
