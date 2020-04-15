<?php
namespace nvRexHelper;

class Form {
    
    public $tableName;
    private $subject;
    private $senderEmail;
    private $senderName;
    private $mailTo;
    private $templateName;
    private $successPage;
    private $debug;
    private $redirectMode;

    public function __construct($data, $tableName, $yform, $form_action="") {
        $oSql = \rex_sql::factory();
        $oSql->setQuery('select * from ' . \rex::getTablePrefix() . 'yform_email_template WHERE name = "'.$data["template"].'" Limit 1');
        $oSql->getRows();
        $this->subject = $data["subject"] ? $data["subject"] : $oSql->getValue("subject");
        $this->senderEmail = $data["mail_from"] ? $data["mail_from"] : $oSql->getValue("mail_from");
        $this->senderName = $data["mail_from_name"] ? $data["mail_from_name"] : $oSql->getValue("mail_from_name");
        $this->mailTo = $data["mail_to"];
        $this->tableName = $tableName;
        $this->templateName = $oSql->getValue("name");
        $this->successPage = $data["REX_LINK_1"];
        $this->redirectMode = $data["redirectMode"] ? false : true;

        if(!$form_action) $form_action = \rex_getUrl('REX_ARTICLE_ID');

        $yform->setObjectparams('form_name', 'table-' . $this->tableName);
        $yform->setObjectparams('form_action', $form_action);
        $yform->setObjectparams('form_ytemplate', 'bootstrap');
        $yform->setObjectparams('form_showformafterupdate', 0);
        $yform->setObjectparams('real_field_names', true);
        $yform->setActionField('db', array($this->tableName, "main_where"));

        $debug = $data["debug"];

        if(!$debug) {
            // Spam Protection -> dazu muss Addon yform_spam_protection installiert sein
            $yform->setValueField('spam_protection', array("honeypot","Bitte nicht ausfüllen.","Ihre Anfrage wurde als Spam erkannt und gelöscht. Bitte versuchen Sie es in einigen Minuten erneut oder wenden Sie sich persönlich an uns.", 0));
        }

        #\nvRexHelper\Spam::addSpamProtection($yform);  

        $this->debug = $debug;
    }

    public function sendMail($yform) {

        // Ab hier beginnen die Vorbereitungen zum E-Mail-Versand
        $yform_email_template_key = $this->templateName; // Key, wie im Backend unter YForm > E-Mail-Templates hinterlegt
        $debug = $this->debug;

        // Array mit Platzhaltern, die im E-Mail-Template ersetzt werden.
        $values = $yform->objparams['value_pool']['email'];
        $oItem = \rex_yform_manager_dataset::get($values["ID"], $this->tableName);

        #$values['custom'] = 'Eigener Platzhalter';

        $yform_email_template = \rex_yform_email_template::getTemplate($yform_email_template_key);
        if (!$yform_email_template && $debug) {
            echo '<p>YForm E-Mail-Template "'.htmlspecialchars($this->templateName).'" wurde nicht gefunden.';
            return;
        }

        if ($debug) { 
            echo '<p>YForm E-Mail-Template "'.htmlspecialchars($this->templateName).'" wurde gefunden.'; 
            echo '<hr /><pre>'; var_dump($yform_email_template); echo '</pre><hr />';
        }
        
        $yform_email_template = \rex_yform_email_template::replaceVars($yform_email_template, $values);

        if ($yform_email_template['attachments'] != '') {
            $f = explode(',', $yform_email_template['attachments']);
            $yform_email_template['attachments'] = array();
            foreach ($f as $v) {
                $yform_email_template['attachments'][] = array('name' => $v, 'path' => \rex_path::media($v));
            }
        } else {
            $yform_email_template['attachments'] = array();
        }

        if (isset($yform->objparams['value_pool']['email_attachments']) && is_array($yform->objparams['value_pool']['email_attachments'])) {
            \dump($yform->objparams['value_pool']['email_attachments']);
            foreach ($yform->objparams['value_pool']['email_attachments'] as $v) {
                $yform_email_template['attachments'][] = ['name' => $v[0], 'path' => \rex_path::pluginData($yform,'manager').'upload/frontend/'.$values["ID"]."_".$v[0]];
            }
        }

        $yform_email_template['mail_to'] = $this->mailTo;
        $yform_email_template['mail_to_name'] = $this->mailTo;
        $yform_email_template['subject'] = $this->subject;
        $yform_email_template['mail_from'] = $this->senderEmail;
        $yform_email_template['mail_from_name'] = $this->senderName;

        $oItem->mail_to = $yform_email_template['mail_to'];
        $oItem->save();

        if ($debug) echo '<hr /><pre>'; var_dump($yform_email_template); echo '</pre><hr />';

        $oItem->email_log = print_r($yform_email_template,1);

    
        if (!\rex_yform_email_template::sendMail($yform_email_template, $yform_email_template["name"])) {
            if ($debug) { echo 'E-Mail konnte nicht gesendet werden.'; }
            $oItem->sent = "Nein";
            $oItem->save();
            return false;

        } else {
            $oItem->sent = "Ja";
            $oItem->save();
            if ($debug) { 
                echo 'E-Mail erfolgreich gesendet.'; 
            } else if ($this->redirectMode == true) {
                rex_redirect($this->successPage, \rex_clang::getCurrentId());
            } 

            return true;
        }
          

       
    }

    public function getBackendOutput() { ?>
        <ul class="list-group">
            <li class="list-group-item"><strong>Debug Modus</strong></li>
            <li class="list-group-item"><?=$this->debug ? "Ja" : "Nein"?></li>
            <li class="list-group-item"><strong>Automatische Weiterleitung zur Erfolgsseite?</strong></li>
            <li class="list-group-item"><?=$this->redirectMode ? "Ja" : "Nein"?></li>
            <li class="list-group-item"><strong>E-Mail-Template</strong></li>
            <li class="list-group-item"><?=$this->templateName?></li>
            <li class="list-group-item"><strong>Empfänger E-Mail</strong></li>
            <li class="list-group-item"><?=$this->mailTo?></li>  
            <li class="list-group-item"><strong>Betreff</strong></li>
            <li class="list-group-item"><?=$this->subject?></li>
            <li class="list-group-item"><strong>Absender E-Mail</strong></li>
            <li class="list-group-item"><?=$this->senderEmail?></li>        
            <li class="list-group-item"><strong>Absender Name</strong></li>
            <li class="list-group-item"><?=$this->senderName?></li>         
            <li class="list-group-item"><strong>Erfolgsseite</strong></li>
            <li class="list-group-item"><?=rex_getUrl($this->successPage) ?></li>
        </ul>
        <?php
    }

    public static function getInput($id, $mform) {
        $aArr = array();
        $oSql = \rex_sql::factory();
        $oSql->setQuery('select * from ' . \rex::getTablePrefix() . 'yform_email_template ORDER BY name ASC');
        for($i=0; $i<$oSql->getRows(); $i++)
        {
            $aArr[$oSql->getValue("name")] = $oSql->getValue("name")." (Betreff: ".$oSql->getValue("subject")." | Absender E-Mail: ".$oSql->getValue("mail_from")." | Absender Name: ".$oSql->getValue("mail_from_name").")";
            $oSql->next();
        }
        $mform->addSelectField("$id.0.debug",["Nein", "Ja"], ["Debug Modus"]);
        $mform->addSelectField("$id.0.redirectMode",["Ja", "Nein"], ["Automatische Weiterleitung zur Erfolgsseite?"]);
        $mform->addSelectField("$id.0.template", $aArr, ["label" => "E-Mail-Template"]);
        $mform->addTextField("$id.0.mail_to", ["label" => "Empfänger E-Mail"]);
        $mform->addTextField("$id.0.subject", ["label" => "Abweichender Betreff E-Mail (Optional, sonst Standardwert aus E-Mail-Template)"]);
        $mform->addTextField("$id.0.mail_from", ["label" => "Abweichender Absender E-Mail (Optional, sonst Standardwert aus E-Mail-Template)"]);
        $mform->addTextField("$id.0.mail_from_name", ["label" => "Abweichender Absender Name (Optional, sonst Standardwert aus E-Mail-Template)"]);
        $mform->addLinkField("$id.0.page_id_success", ["label" => "Erfolgsseite"]);
    }
}