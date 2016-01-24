<?php

require 'taiga.php';

// Initalize
$type = 0;
$types = array('id' => 0, 'name' => '');

// Get information from Taiga
if($auth = taiga_login()) {

    $types = array_merge($types, taiga_get_issue_types($auth));

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
        if (!isset($result) && !($errSubject || $errDesc || $errType || $errName || $errEmail || $errHuman)) {
            $result = '<div class="alert alert-danger">Beklager, din henvendelse kunne ikke registres.</div>';
        }
    } else {
        if (isset($_GET['id'])) {
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
} else {
    $result = '<div class="alert alert-danger">Beklager, din henvendelse kan ikke registres. Prøv igjen senere.</div>';
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
                               placeholder="Kort beskrivende tekst"
                               value="<?php if (isset($subject)) { echo $subject; }?>">
                        <?php if (isset($errSubject)) {
                            echo "<p class='text-danger'>$errSubject</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="type" class="col-sm-2 control-label">Tilbakemelding</label>

                    <div class="col-sm-10">
                        <!-- Id's collected from https://tree.taiga.io/project/username-sar-rapport/admin/project-values/types -->
                        <select id="type" name="type" class="form-control">
                        <?php foreach($types as $item) { ?>
                            <option value="$type" <?php if ($type === $item['id']) {
                                echo("selected");
                            } ?>><?php echo $item['name'] ?></option>
                        </select>
                        <?php } ?>
                        <?php if (isset($errType)) {
                            echo "<p class='text-danger'>$errType</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description" class="col-sm-2 control-label">Beskrivelse</label>

                    <div class="col-sm-10">
                        <textarea class="form-control" rows="4" id="description" name="description">
                            <?php if (isset($description)) { echo $description; }?>
                        </textarea>
                        <?php if (isset($errDesc)) {
                            echo "<p class='text-danger'>$errDesc</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Navn</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Ditt navn"
                               value="<?php if (isset($name)) { echo $name; }?>">
                        <?php if (isset($errName)) {
                            echo "<p class='text-danger'>$errName</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-2 control-label">Email</label>

                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="example@domain.com" value="<?php if (isset($email)) { echo $email; }?>">
                        <?php if (isset($errEmail)) {
                            echo "<p class='text-danger'>$errEmail</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="human" class="col-sm-2 control-label">2 + 3 = ?</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="human" name="human" placeholder="Ditt svar"
                               value="<?php if (isset($human)) { echo $human; }?>">
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

<!-- jQuery -->
<script src="../js/jquery.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="../js/bootstrap.js"></script>

</body>
</html>
