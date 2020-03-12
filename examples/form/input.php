<?php
$id = 1;
$mf = new MForm();

$mf->addFieldset("Inhalt");
nvRexHelper\Form::getInput($id, $mf);
echo MBlock::show($id, $mf->show(), ["min" => 1, "max" => 1]);