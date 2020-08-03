<?php
namespace nvRexHelper;

class Form 
{
    public $tableName;
    private $subject;
    private $senderEmail;
    private $senderName;
    private $mailTo;
    private $templateName;
    private $successPage;
    private $debug;
    private $redirectMode;

    public function __construct($data, $yform, $form_action="") 
    {
        $oSql = \rex_sql::factory();
        $oSql->setQuery('select * from ' . \rex::getTablePrefix() . 'yform_email_template WHERE name = "'.$data["template"].'" Limit 1');
        $oSql->getRows();
        $this->subject = $data["subject"] ? $data["subject"] : $oSql->getValue("subject");
        $this->senderEmail = $data["mail_from"] ? $data["mail_from"] : $oSql->getValue("mail_from");
        $this->senderName = $data["mail_from_name"] ? $data["mail_from_name"] : $oSql->getValue("mail_from_name");
        $this->mailTo = $data["mail_to"];
        $this->tableName = $data["tableName"];
        $this->templateName = $oSql->getValue("name");
        $this->successPage = $data["REX_LINK_1"];
        $this->redirectMode = $data["redirectMode"] ? false : true;
        $this->successParam = $data["successParam"] ? true : false;
        $this->successAnchor = $data["successAnchor"] ?: "";

        if(!$form_action) $form_action = \rex_getUrl('REX_ARTICLE_ID');

        $yform->setObjectparams('form_name', 'table-' . $this->tableName);
        $yform->setObjectparams('form_action', $form_action);
        $yform->setObjectparams('form_ytemplate', 'bootstrap');
        $yform->setObjectparams('form_showformafterupdate', 0);
        $yform->setObjectparams('real_field_names', true);
        $yform->setActionField('db', array($this->tableName, "main_where"));

        $debug = $data["debug"];

        if(!$debug) 
        {
            // Spam Protection -> dazu muss Addon yform_spam_protection installiert sein
            $yform->setValueField('spam_protection', array("honeypot","Bitte nicht ausfüllen.","Ihre Anfrage wurde als Spam erkannt und gelöscht. Bitte versuchen Sie es in einigen Minuten erneut oder wenden Sie sich persönlich an uns.", 0));
        }

        #\nvRexHelper\Spam::addSpamProtection($yform);  

        $this->debug = $debug;
    }

    public function sendMail($yform) 
    {

        // Ab hier beginnen die Vorbereitungen zum E-Mail-Versand
        $yform_email_template_key = $this->templateName; // Key, wie im Backend unter YForm > E-Mail-Templates hinterlegt
        $debug = $this->debug;

        // Array mit Platzhaltern, die im E-Mail-Template ersetzt werden.
        $values = $yform->objparams['value_pool']['email'];
        $oItem = \rex_yform_manager_dataset::get($values["ID"], $this->tableName);

        #$values['custom'] = 'Eigener Platzhalter';

        $yform_email_template = \rex_yform_email_template::getTemplate($yform_email_template_key);
        if (!$yform_email_template && $debug) 
        {
            echo '<p>YForm E-Mail-Template "'.htmlspecialchars($this->templateName).'" wurde nicht gefunden.';
            return;
        }

        if ($debug) 
        { 
            echo '<p>YForm E-Mail-Template "'.htmlspecialchars($this->templateName).'" wurde gefunden.'; 
            \dump($yform_email_template);
        }
        
        $yform_email_template = \rex_yform_email_template::replaceVars($yform_email_template, $values);

        $yform_email_template['attachments'] = [];

        // Hänge die Anhänge die im E-Mail Template spezifiziert sind an
        // Wird von string in array convertiert
        // damit später Dateien im Formular an das Gesamte array angehängt werden können

        if ($yform_email_template['attachments'] != '') 
        {
            
            $files = explode(',', $yform_email_template['attachments']);
            
            foreach ($files as $file) 
            {
                $yform_email_template['attachments'][] = [
                    'name' => $file, 
                    'path' => \rex_path::media($files)
                ];
            }

        }

        // Hänge Dateien die über Yform Upload Field im Formular sind als
        // Anhänge an die E-Mail
        $files = $yform->objparams['value_pool']['files'];

        if (isset($files) && is_array($files)) 
        {

            if ($debug) \dump($files);

            foreach ($files as $file) 
            {
                $path = \rex_path::pluginData("yform", 'manager') . 'upload/frontend/' . $values["ID"] . "_" . $file[0];

                $yform_email_template['attachments'][] = [
                    'name' => $file[0], 
                    'path' => $path
                ];
            }
        }

        $yform_email_template['mail_to'] = $this->mailTo;
        $yform_email_template['mail_to_name'] = $this->mailTo;
        $yform_email_template['subject'] = $this->subject;
        $yform_email_template['mail_from'] = $this->senderEmail;
        $yform_email_template['mail_from_name'] = $this->senderName;

        $oItem->mail_to = $yform_email_template['mail_to'];
        $oItem->save();

        if ($debug) \dump($yform_email_template);

        $oItem->email_log = print_r($yform_email_template,1);

        // sende die email
        if (!\rex_yform_email_template::sendMail($yform_email_template, $yform_email_template["name"])) 
        {
          
            // Behandle Fehler
            if ($debug) { echo '<h1>E-Mail konnte nicht gesendet werden.</h1>'; }
           
            $oItem->sent = "Nein";
            $oItem->save();
            return false;

        } 
        else 
        {

            // Behandle Success
            $oItem->sent = "Ja";
            $oItem->save();
           
            if ($debug) 
            { 
                echo '<h1>E-Mail erfolgreich gesendet.</h1>'; 
            } 
            else if ($this->redirectMode == true) 
            {
                \rex_redirect($this->successPage, \rex_clang::getCurrentId());
            } 
            else if ($this->successParam)
            {
                $url = \rex_getUrl(\rex_article::getCurrentId(), \rex_clang::getCurrentId(), ["success" => "true"]);

                if ($this->successAnchor)
                {
                    $url .= '#' . $this->successAnchor;
                }
                
                header("Location: " . $url);
                exit();
            }


            return true;
        }
          
    }

    public function getBackendOutput() 
    { ?>
        <ul class="list-group">
            <li class="list-group-item"><strong>Debug Modus</strong></li>
            <li class="list-group-item"><?=$this->debug ? "Ja" : "Nein"?></li>
            <li class="list-group-item"><strong>Automatische Weiterleitung zur Erfolgsseite?</strong></li>
            <li class="list-group-item"><?=$this->redirectMode ? "Ja" : "Nein"?></li>
            <li class="list-group-item"><strong>E-Mail-Template</strong></li>
            <li class="list-group-item"><?=$this->templateName?></li>
            <li class="list-group-item"><strong>Erfrolgsparameter</strong></li>
            <li class="list-group-item"><?=$this->successParam ? "Ja" : "Nein"?></li>
            <li class="list-group-item"><strong>Erfrolgsanker</strong></li>
            <li class="list-group-item"><?=$this->successAnchor ? "#" . $this->successAnchor : ""?></li>
            <li class="list-group-item"><strong>Tabelle</strong></li>
            <li class="list-group-item"><?=$this->tableName?></li>
            <li class="list-group-item"><strong>Empfänger E-Mail</strong></li>
            <li class="list-group-item"><?=$this->mailTo?></li>  
            <li class="list-group-item"><strong>Betreff</strong></li>
            <li class="list-group-item"><?=$this->subject?></li>
            <li class="list-group-item"><strong>Absender E-Mail</strong></li>
            <li class="list-group-item"><?=$this->senderEmail?></li>        
            <li class="list-group-item"><strong>Absender Name</strong></li>
            <li class="list-group-item"><?=$this->senderName?></li>         
            <li class="list-group-item"><strong>Erfolgsseite</strong></li>
            <li class="list-group-item"><?=\rex_getUrl($this->successPage) ?></li>
        </ul>
        <?php
    }

    public static function getInput($id, $mform) 
    {
        $aArr = array();
        $oSql = \rex_sql::factory();
        $oSql->setQuery('select * from ' . \rex::getTablePrefix() . 'yform_email_template ORDER BY name ASC');
       
        for($i=0; $i<$oSql->getRows(); $i++) 
        {
            $aArr[$oSql->getValue("name")] = $oSql->getValue("name")." (Betreff: ".$oSql->getValue("subject")." | Absender E-Mail: ".$oSql->getValue("mail_from")." | Absender Name: ".$oSql->getValue("mail_from_name").")";
            $oSql->next();
        }

        $aTables = [];
        $sql = \rex_sql::factory();
        $query = "SELECT * FROM rex_yform_table WHERE status='1'";
        $sql->setQuery($query);

        foreach ($sql as $row) 
        {
            $aTables[$sql->getValue("table_name")] = $sql->getValue("name");
        }

        $mform->addSelectField("$id.0.debug",["Nein", "Ja"], ["Debug Modus"]);
        $mform->addSelectField("$id.0.redirectMode",["Ja", "Nein"], ["Automatische Weiterleitung zur Erfolgsseite?"]);
        $mform->addSelectField("$id.0.template", $aArr, ["label" => "E-Mail-Template"]);
        $mform->addSelectField("$id.0.successParam", ["Nein", "Ja"], ["Erfolgsparameter?"]);
        $mform->addTextField("$id.0.successAnchor", ["Erfolgs Anker"]);
        $mform->addSelectField("$id.0.tableName", $aTables, ["Tabelle"]);
        $mform->addTextField("$id.0.mail_to", ["label" => "Empfänger E-Mail"]);
        $mform->addTextField("$id.0.subject", ["label" => "Abweichender Betreff E-Mail (Optional, sonst Standardwert aus E-Mail-Template)"]);
        $mform->addTextField("$id.0.mail_from", ["label" => "Abweichender Absender E-Mail (Optional, sonst Standardwert aus E-Mail-Template)"]);
        $mform->addTextField("$id.0.mail_from_name", ["label" => "Abweichender Absender Name (Optional, sonst Standardwert aus E-Mail-Template)"]);
        $mform->addLinkField("$id.0.page_id_success", ["label" => "Erfolgsseite"]);
    }
}