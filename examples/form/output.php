<?php
$yform = new rex_yform();
$oForm = new nvRexHelper\Form(rex_var::toArray("REX_VALUE[1]")[0], $yform);

if (rex::isBackend()) 
{
    $oForm->getBackendOutput();
    return;
}


$sUrl = rex_getUrl(9);
$sDataText = 'Ich habe die <a target="_blank" href="' . $sUrl . '">Datenschutzerklärung</a> gelesen. *';

$yform->setValueField('choice', array('gender', 'Anrede *', '{"Herr": "Herr", "Frau": "Frau", "Divers": "Divers"}', '', '', '', '', '', 'Bitte Wählen *', '', '{"required": "true"}', '', '', '0'));
$yform->setValueField('text', array('firstname', 'Vorname *', '', '0', '{"required":"true"}'));
$yform->setValueField('text', array('lastname', 'Nachname *', '', '0', '{"required":"true"}'));
$yform->setValueField('email', array('email', 'E-Mail *', '', '0', '{"required":"true"}'));
$yform->setValueField('text', array('company', 'Firma', '', '0'));
$yform->setValueField('text', array('phone', 'Telefon', '', '0'));
$yform->setValueField('textarea', array('message', 'Nachricht *', '', '0', '{"required":"true"}'));
$yform->setValueField('checkbox', array('dataprotection', $sDataText, '0', '1', '{"required":"true"}'));
$yform->setValueField('submit', array('submit', 'Abschicken', '', 'no_db'));


$form = $yform->getForm();

if (!$form) 
{
    $oForm->sendMail($yform);
    return;
}
?>

<div class="nv-module-contact-form container">
    <div class="row">
        <div class="col-lg-8">
            <?= $form ?>
            <p>Alle mit einem * gekennzeichneten Felder sind Pflichtfelder</p>
        </div>
    </div>
</div>