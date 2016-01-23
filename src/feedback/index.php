<?php

define ('HOST', 'https://api.taiga.io/api/v1/');

if (isset($_POST["submit"])) {
    $name = isset_get($_POST, 'name');
    $email = isset_get($_POST, 'email');
    $subject = isset_get($_POST, 'subject');
    $type = intval(isset_get($_POST, 'type'));
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

    // Check if name has been entered
    if (!$name) {
        $errName = 'Skriv inn ditt navn';
    }

    // Check if email has been entered and is valid
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errEmail = 'Skriv inn en gyldig e-postadresse';
    }

    //Check if simple anti-bot test is correct
    if (intval($human) !== 5) {
        $errHuman = 'Ditt svar er feil, prøv igjen';
    }

    // If there are no errors, send the email
    if (!$errSubject && !$errDesc && !$errType && !$errName && !$errEmail && !$errHuman) {
        if ($auth = taiga_login()) {
            if (isset($_GET['id'])) {
                $issue = taiga_edit_issue($auth, $_GET['id']);
            } else {
                $issue = taiga_create_issue($auth);
            }
            if ($issue) {
                $result = '<div class="alert alert-success">Takk! <a href="' . $issue . '">Tilbakemelding ' . $issue . '</a> er registrert. Vi vil ta kontakt når din tilbakemelding er behandlet. </div>';

                if(true || !isset($_GET['id'])) {
                    notify('Tilbakemelding ' . $issue . '</a> er registrert', $email, $result);
                }
            }
        }
    }
    if (!isset($result) && !($errSubject || $errDesc || $errType || $errName || $errEmail || $errHuman)) {
        $result = '<div class="alert alert-danger">Beklager, din henvendelse kunne ikke registres.</div>';
    }
} else {
    if (isset($_GET['id'])) {

        if ($auth = taiga_login()) {
            if ($issue = taiga_get_issue($auth, $_GET['id'])) {
                $subject = isset_get($issue, 'subject');
                $type = isset_get($issue, 'type');
                $description = isset_get($issue, 'description');
                if ($attrs = taiga_get_issue_attributes($auth, $_GET['id'])) {
                    $attrs = $attrs['attributes_values'];
                    $name = isset_get($attrs, '1164');
                    $email = isset_get($attrs, '1165');
                }
            }
        }
    }
}

function notify($subject, $to, $message) {

    // To send HTML mail, the Content-type header must be set
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

    // Additional headers
    $headers .= 'Reply-To: RG Ettersøkning <rge@hjelpekorps.org>' . "\r\n";
    $headers .= 'From: RG Ettersøkning <rge@hjelpekorps.org>' . "\r\n";
//    $headers .= 'Bcc: rge@hjelpekorps.org' . "\r\n";

    // Mail it
    $result = mail($to, $subject, $message, $headers);

    var_dump($result);
}

function taiga_login()
{
    $process = curl_init(HOST . 'auth');
    curl_setopt(
        $process,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json'
        )
    );
    curl_setopt(
        $process,
        CURLOPT_POSTFIELDS,
        json_encode(
            array(
                'type' => 'normal',
                'username' => 'username',
                'password' => 'password'
            )
        )
    );

    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    $user = curl_exec($process);
    if ($user !== false) {
        $user = json_decode($user, true);
        $user = $user['auth_token'];

    }
    curl_close($process);

    return $user;
}

function taiga_create_issue($auth)
{

    $process = curl_init(HOST . 'issues');
    curl_setopt(
        $process,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8',
            "Authorization: Bearer $auth"
        )
    );
    curl_setopt(
        $process,
        CURLOPT_POSTFIELDS,
        json_encode(
            array(
                'project' => 101250,
                'type' => filter_post('type', FILTER_VALIDATE_INT),
                'subject' => filter_post('subject'),
                'description' => filter_post('description'),
                'reporter_name' => filter_post('name'),
                'reporter_email' => filter_post('email')
            )
        )
    );

    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    $issue = curl_exec($process);
    if ($issue !== false) {
        $issue = json_decode($issue, true);
        $issue = $issue['id'];
        taiga_edit_issue_attributes($auth, $issue);

    }
    curl_close($process);

    return $issue;
}

function taiga_get_issue($auth, $id)
{
    $process = curl_init(HOST . "issues/$id");
    curl_setopt(
        $process,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8',
            "Authorization: Bearer $auth"
        )
    );

    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    $issue = curl_exec($process);
    if ($issue !== false) {
        $issue = json_decode($issue, true);
    }
    curl_close($process);

    return $issue;

}

function taiga_edit_issue($auth, $id)
{

    if ($issue = taiga_get_issue($auth, $id)) {

        $version = $issue['version'];
        $process = curl_init(HOST . "issues/$id");
        curl_setopt(
            $process,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json; charset=utf-8',
                "Authorization: Bearer $auth"
            )
        );
        curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt(
            $process,
            CURLOPT_POSTFIELDS,
            json_encode(
                array(
                    'project' => 101250,
                    'type' => filter_post('type', FILTER_VALIDATE_INT),
                    'subject' => filter_post('subject'),
                    'description' => filter_post('description'),
                    'version' => $version
                )
            )
        );

        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        $issue = curl_exec($process);
        if ($issue !== false) {
            taiga_edit_issue_attributes($auth, $id);
            $issue = $id;
        }
        curl_close($process);
    }

    return $issue;
}


function taiga_get_issue_attributes($auth, $id)
{
    $process = curl_init(HOST . "issues/custom-attributes-values/$id");
    curl_setopt(
        $process,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8',
            "Authorization: Bearer $auth"
        )
    );

    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    $attributes = curl_exec($process);
    if ($attributes !== false) {
        $attributes = json_decode($attributes, true);
    }
    curl_close($process);

    return $attributes;

}


function taiga_edit_issue_attributes($auth, $id)
{


    if ($attributes = taiga_get_issue_attributes($auth, $id)) {

        $version = $attributes['version'];

        $process = curl_init(HOST . "issues/custom-attributes-values/$id");
        curl_setopt(
            $process,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json; charset=utf-8',
                "Authorization: Bearer $auth"
            )
        );
        curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PUT");
        // Id's collected using https://api.taiga.io/api/v1/issue-custom-attributes?project=101250
        curl_setopt(
            $process,
            CURLOPT_POSTFIELDS,
            json_encode(
                array(
                    'attributes_values' => array(
                        // reporter_name
                        '1164' => filter_post('name'),
                        // reporter_email
                        '1165' => filter_post('email')
                    ),
                    'version' => $version,
                    'issue' => $id
                )
            )
        );
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        if ($attributes = curl_exec($process)) {
            $attributes = json_decode($attributes, true);
        }
        curl_close($process);
    }

    return $attributes;
}


function isset_get($array, $name, $default = false)
{
    return isset($array[$name]) ? $array[$name] : $default;
}

function filter_post($name, $filter = FILTER_DEFAULT)
{
    return filter_input(INPUT_POST, $name, $filter, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <title>SAR-rapport - tilbakemelding</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Bootstrap Core CSS -->
    <link href="../css/bootstrap.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../css/heroic-features.css" rel="stylesheet">
</head>

<body>
<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-navbar-collapse-1">
                <span class="sr-only"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="http://sar.hovedredningssentralen.no/" target="_blank">SAR-rapport</a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li>
                    <a href="https://www.korsveien.no" target="_blank">Korsveien</a>
                </li>
                <li>
                    <a href="http://www.hjelpekorps.org" target="_blank">Norges Røde Kors Hjelpekorps</a>
                </li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
</nav>

<!-- Page Content -->
<div class="container">

    <!-- Jumbotron Header -->
    <header class="jumbotron hero-spacer">
        <h1>Vi ønsker tilbakemeldinger!</h1>

        <p>På denne siden kan du rapportere feil og komme med forslag til forbedringer av SAR-rapport</p>
    </header>

    <!-- Feedback form -->
    <form class="form-horizontal" role="form" method="post" action="<?php if (isset($_GET['id'])) {
        echo $_GET['id'];
    } ?>">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Emne</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="subject" name="subject"
                               placeholder="Kort beskrivende tekst" value="<?php echo $subject; ?>">
                        <?php if (isset($errSubject)) {
                            echo "<p class='text-danger'>$errSubject</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="type" class="col-sm-2 control-label">Tilbakemelding</label>

                    <div class="col-sm-10">
                        <!-- Id's collected from https://tree.taiga.io/project/username-sar-rapport/admin/project-values/types -->
                        <select id="type" name="type" class="form-control" value="<?php echo $type; ?>">
                            <option value="0" <?php if ($type === '0') {
                                echo("selected");
                            } ?>></option>
                            <option value="305570" <?php if ($type === 305570) {
                                echo("selected");
                            } ?>>Feil
                            </option>
                            <option value="305571" <?php if ($type === 305571) {
                                echo("selected");
                            } ?>>Spørsmål
                            </option>
                            <option value="305572" <?php if ($type === 305572) {
                                echo("selected");
                            } ?>>Forslag
                            </option>
                        </select>
                        <?php if (isset($errType)) {
                            echo "<p class='text-danger'>$errType</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description" class="col-sm-2 control-label">Beskrivelse</label>

                    <div class="col-sm-10">
                        <textarea class="form-control" rows="4" id="description"
                                  name="description"><?php echo $description; ?></textarea>
                        <?php if (isset($errDesc)) {
                            echo "<p class='text-danger'>$errDesc</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Navn</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Ditt navn"
                               value="<?php echo $name; ?>">
                        <?php if (isset($errName)) {
                            echo "<p class='text-danger'>$errName</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-2 control-label">Email</label>

                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="example@domain.com" value="<?php echo $email; ?>">
                        <?php if (isset($errEmail)) {
                            echo "<p class='text-danger'>$errEmail</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="human" class="col-sm-2 control-label">2 + 3 = ?</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="human" name="human" placeholder="Ditt svar"
                               value="<?php echo $human; ?>">
                        <?php if (isset($errHuman)) {
                            echo "<p class='text-danger'>$errHuman</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <?php echo $result; ?>
                    </div>
                </div>

            </div>
            <div class="panel-footer">
                <div class="form-group text-right">
                    <div class="col-sm-12">
                        <input id="submit" name="submit" type="submit" value="Send" class="btn btn-primary">
                    </div>
                </div>

            </div>
    </form>

</div>


<!-- Footer -->
<footer>
    <div class="row">
        <div class="col-lg-12">
            <p><b>SAR-rapport</b> | Norges Røde Kors Hjelpekorps</p>
        </div>
    </div>
</footer>

</div>

<!-- jQuery -->
<script src="../js/jquery.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="../js/bootstrap.js"></script>

</body>
</html>
