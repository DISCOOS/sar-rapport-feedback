<?php

require 'smtp.php';
require 'taiga.php';
require 'recaptcha.php';
require 'vendor/autoload.php';

// Start the session
session_start();


// Initialize
$type = 0;
$types[] = array('id' => 0, 'name' => '');
$level = 0;
$levels[] = array('id' => 0, 'name' => '');

$status = array('id' => 0, 'name' => 'Ny');

$err = array();
$comments = array();

$result = '';

$id = isset_get($_GET, 'id');

if(!$id || strtolower($id)=='new') {
    $id = false;
}
$new = ($id === false);

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
            $err['desc'] = 'Skriv inn din tilbakemelding';
        }

        // Check if type has been selected
        if (!$type) {
            $err['type'] = 'Velg type tilbakemelding';
        }

        // Check if severity level has been selected
        if (!$level) {
            $err['level'] = 'Velg alvorlighetsgrad';
        }

        // Check if name has been entered
        if (!$name) {
            $err['name'] = 'Skriv inn ditt navn';
        }

        // Check if email has been entered and is valid
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err['email'] = 'Skriv inn en gyldig e-postadresse';
        }

        // Check anti-bot test is correct
        if (!recaptcha_verify()) {
            $err['human'] = 'Du må klikke på boksen over for å bekrefte at du ikke er en robot';
        }

        // If there are no errors, send the email
        if (count($err) == 0) {

            $issue = ($new ? taiga_create_issue($auth) : taiga_edit_issue_by_ref($auth, $id));
            
            if ($issue) {
                $id = $issue['ref'];
                $href = "https://tree.taiga.io/project/rge-sar-rapport/issue/$id";

                if($id) {
                    $comments = taiga_get_issue_comments($auth, $issue['id']);
                    $result = 'Takk! <a href="' . $href . '">Tilbakemelding ' . $id . '</a> er registrert. ';
                    $result .= 'Vi vil ta kontakt når din tilbakemelding er behandlet.';
                    if($new) {
                        if(notify('Tilbakemelding ' . $id . ' er registrert', $email, $name, $result)) {
                            $result .= 'Kvittering er sendt til ' . $email . '.';
                        }
                    }

                } 
                $result = '<div class="alert alert-success">' . $result . '</div>';
                $comments = taiga_get_issue_comments($auth, $issue['id']);

            } else {
                $result = '<div class="alert alert-danger">Beklager, din henvendelse kunne ikke registres.</div>';
            }
        } else if($id) {
            $issue = taiga_get_issue_by_ref($auth, $id);
            $comments = taiga_get_issue_comments($auth, $issue['id']);
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

// Keep result until next reload
if($new && $id) {
    $_SESSION['result'] = $result;
    redirect("feedback/$id");
}
$result = isset_get($_SESSION, 'result', '');
unset($_SESSION['result']);


// Prepare twogjs
$view=array(
    'id' => $id,
    'type' => $type,
    'types' => $types,
    'level' => $level,
    'levels' => $levels,
    'status' => $status,
    'comments' => $comments,
    'subject' => isset($subject) ? $subject : '',
    'description' => isset($description) ? $description : '',
    'assigned' => isset($assigned) ? $assigned : '',
    'name' => isset($name) ? $name : '',
    'email' => isset($email) ? $email : '',
    'result' => $result,
    'err' => $err,
    'view' => 'feedback.form.twig'
);


$loader = new Twig_Loader_Filesystem('twig/templates');
$twig = new Twig_Environment($loader, array(
    'cache' => 'twig/cache',
));

echo $twig->render("feedback.twig", $view);

?>
