<?php
$yform = new rex_yform();
$oForm = new nvRexHelper\Form(rex_var::toArray("REX_VALUE[1]")[0], "nv_contact_requests", $yform, "");
$oSettings = new nvModuleSettings(REX_MODULE_ID);

if (rex::isBackend()) {
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

if (!$form) {
    $oForm->sendMail($yform);
    return;
}
?>

<fullscreenmodule class="nv-module-contact-form <?= $oSettings->settings->marginBottom ?>">
    <container>
        <row>
            <div class="col-12 col-lg-8 mb-5 mb-lg-0">
                <?= $form ?>
                <p>Alle mit einem * gekennzeichneten Felder sind Pflichtfelder</p>
            </div>
            <div class="col-12 col-lg-4">
                <div class="bg-light p-5">
                    <strong>Mainau Akademie e.V.<br>
                        Schloss Mainau<br>
                        78465 Insel Mainau</strong>
                    <br><br>
                    <a href="mailto:anfragen@mainau-akademie.de"><i class="fal fa-envelope mr-2"></i> anfragen@mainau-akademie.de</a>
                    <br>
                    <a href="tel:+497531303202"><i class="fal fa-phone mr-2"></i> +49 (0) 7531 303-202</a>
                </div>

            </div>
        </row>
    </container>
</fullscreenmodule>