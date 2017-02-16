<?php

require 'smtp.php';
require 'taiga.php';
require 'recaptcha.php';
require 'vendor/autoload.php';

// Initialize
$type = 0;
$types[] = array('id' => 0, 'name' => '');
$level = 0;
$levels[] = array('id' => 0, 'name' => '');

$status = array('id' => 0, 'name' => 'Ny');

$comments = array();

$result = '';

$id = isset_get($_GET, 'id');

if(strtolower($id)=='new') {
   unset($id);
}

// Get information from Taiga
if($auth = taiga_login()) {

	//taiga_delete_project_by_slug('rge-fsor-v1')	
	//taiga_edit_project_by_id($auth, 121064);
        //taiga_list_projects($auth);
	//exit;


    $types = array_merge($types, taiga_get_issue_types($auth));
    $levels = array_merge($levels, taiga_get_severity_levels($auth));

    if (isset($_POST["submit"])) {
        $name = isset_get($_POST, 'name');
        $email = isset_get($_POST, 'email');
        $subject = isset_get($_POST, 'subject');
        $type = intval(isset_get($_POST, 'type'));
        $level = intval(isset_get($_POST, 'level'));
        $description = isset_get($_POST, 'description');
        $human = isset_get($_POST, 'human');

        // Check if title has been entered
        if (!$subject) {
            $errSubject = 'Skriv inn en kort beskrivende tekst';
        }

        //Check if message has been entered
        if (!$description) {
            $errDesc = 'Skriv inn din tilbakemelding';
        }

        // Check if type has been selected
        if (!$type) {
            $errType = 'Velg type tilbakemelding';
        }

        // Check if severity level has been selected
        if (!$level) {
            $errLevel = 'Velg alvorlighetsgrad';
        }

        // Check if name has been entered
        if (!$name) {
            $errName = 'Skriv inn ditt navn';
        }

        // Check if email has been entered and is valid
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errEmail = 'Skriv inn en gyldig e-postadresse';
        }

        // Check anti-bot test is correct
        if (!recaptcha_verify()) {
            $errHuman = 'Du må klikke på boksen over for å bekrefte at du ikke er en robot';
        }

        // If there are no errors, send the email
        if (!$errSubject && !$errDesc && !$errType && !$errLevel && !$errName && !$errEmail && !$errHuman) {
            if ($id) {
                $issue = taiga_edit_issue_by_ref($auth, $id);
            } else {
                $issue = taiga_create_issue($auth);
            }
            if ($issue) {

                $ref = $issue['ref'];
		$href = "https://tree.taiga.io/project/rge-sar-rapport/issue/$ref";

                if($id) {
                    $comments = taiga_get_issue_comments($auth, $issue['id']);
		    $result = 'Takk! <a href="' . $href . '">Tilbakemelding ' . $ref . '</a> er registrert. ';
                    $result .= 'Vi vil ta kontakt når din tilbakemelding er behandlet.';

                } else {
                    $result = 'Takk! <a href="' . $href . '">Tilbakemelding ' . $ref . '</a> er registrert. ';
                    if(notify('Tilbakemelding ' . $ref . ' er registrert', $email, $name, $result)) {
                        $result .= 'Kvittering er sendt til ' . $email . '. ';
                    }
                    $result .= 'Vi vil ta kontakt når din tilbakemelding er behandlet.';

                }
                $result = '<div class="alert alert-success">' . $result . '</div>';
                $comments = taiga_get_issue_comments($auth, $issue['id']);

            }
        }
        if (!isset($result) && !($errSubject || $errDesc || $errType || $errLevel || $errName || $errEmail || $errHuman)) {
            $result = '<div class="alert alert-danger">Beklager, din henvendelse kunne ikke registres.</div>';
        }
    } else {
        if ($id) {
            if ($issue = taiga_get_issue_by_ref($auth, $id)) {
                $subject = isset_get($issue, 'subject');
                $type = isset_get($issue, 'type');
                $status = isset_get($issue, 'status_extra_info');
		        $assigned = isset_get($issue, 'assigned_to_extra_info');
		        $level = isset_get($issue, 'severity');
                $description = isset_get($issue, 'description');
                if ($attrs = taiga_get_issue_attributes($auth, $issue['id'])) {
                    $attrs = $attrs['attributes_values'];
                    $name = isset_get($attrs, '1671');
                    $email = isset_get($attrs, '1672');
                }
                $comments = taiga_get_issue_comments($auth, $issue['id']);
            }
        }
    }
} else {
    $result = '<div class="alert alert-danger">Beklager, din henvendelse kan ikke registres. Prøv igjen senere.</div>';
}

$view='feedback.form.php';

require 'feedback.page.php';

$loader = new Twig_Loader_Filesystem('/twig/templates');
$twig = new Twig_Environment($loader, array(
    'cache' => '/twig/cache',
));

?>
