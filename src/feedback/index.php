<?php

require 'smtp.php';
require 'taiga.php';
require '../vendor/autoload.php';

// Initialize
$type = 0;
$types[] = array('id' => 0, 'name' => '');
$level = 0;
$levels[] = array('id' => 0, 'name' => '');

$status = array('id' => 0, 'name' => 'Ny');

$comments = array();

// Get information from Taiga
if($auth = taiga_login()) {

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

        //Check if simple anti-bot test is correct
        if (intval($human) !== 5) {
            $errHuman = 'Ditt svar er feil, prøv igjen';
        }

        // If there are no errors, send the email
        if (!$errSubject && !$errDesc && !$errType && !$errLevel && !$errName && !$errEmail && !$errHuman) {
            if (isset($_GET['id'])) {
                $issue = taiga_edit_issue_by_ref($auth, $_GET['id']);
            } else {
                $issue = taiga_create_issue($auth);
            }
            if ($issue) {
                $ref = $issue['ref'];

                if(isset($issue['id'])) {
                    $comments = taiga_get_issue_comments($auth, $issue['id']);
		    $result = 'Takk! <a href="' . $ref . '">Tilbakemelding ' . $ref . '</a> er registrert. Vi vil ta kontakt når din tilbakemelding er behandlet.';
		    $status = notify('Tilbakemelding ' . $ref . ' er registrert', $email, $result);
		    var_dump($status);
                } else {
                    $result = 'Takk! <a href="' . $ref . '">Tilbakemelding ' . $ref . '</a> er registrert. ';
                    if(notify('Tilbakemelding ' . $ref . ' er registrert', $email, $result)) {
                        $result .= 'Kvittering er sendt til ' . $email . '. ';
                    }
                    $result .= 'Vi vil ta kontakt når din tilbakemelding er behandlet.';

                }
                $result = '<div class="alert alert-success">' . $result . '</div>';
            }
        }
        if (!isset($result) && !($errSubject || $errDesc || $errType || $errLevel || $errName || $errEmail || $errHuman)) {
            $result = '<div class="alert alert-danger">Beklager, din henvendelse kunne ikke registres.</div>';
        }
    } else {
        if (isset($_GET['id'])) {
            if ($issue = taiga_get_issue_by_ref($auth, $_GET['id'])) {
                $subject = isset_get($issue, 'subject');
                $type = isset_get($issue, 'type');
                $status = isset_get($issue, 'status_extra_info');
		        $assigned = isset_get($issue, 'assigned_to_extra_info');
		        $level = isset_get($issue, 'severity');
                $description = isset_get($issue, 'description');
                if ($attrs = taiga_get_issue_attributes($auth, $issue['id'])) {
                    $attrs = $attrs['attributes_values'];
                    $name = isset_get($attrs, '1164');
                    $email = isset_get($attrs, '1165');
                }
                $comments = taiga_get_issue_comments($auth, $issue['id']);
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
                    <a href="https://www.korsveien.no/sites/hjkaksjonsrapportering/default.aspx" target="_blank">Korsveien</a>
                </li>
                <li>
                    <a href="http://www.hjelpekorps.org" target="_blank">Norges Røde Kors Hjelpekorps</a>
                </li>
		<li>
		    <a href="https://goo.gl/zjQPzA" target="_blank">Brukerveiledning</a>
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

        <p>På denne siden kan du rapportere feil, stille spørsmål, komme med forslag til forbedringer og be om bistand</p>
    </header>

    <!-- Feedback form -->
    <form class="form-horizontal" role="form" method="post" action="<?php if (isset($_GET['id'])) {echo $_GET['id'];} ?>">
        <div class="panel panel-default">
	    <div class="panel-heading text-right">
		<b>Status</b> <span class="label label-primary"><?=$status['name']?></span>&nbsp&nbsp<b>Ansvarlig</b> <span class="label label-primary"><?=($assigned['full_name_display'] ? $assigned['full_name_display'] : 'Ikke tildelt')?></span>
	    </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Emne</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="subject" name="subject"
                               placeholder="Kort beskrivende tekst"
                               value="<?php if (isset($subject)) { echo $subject; }?>"
			       <? if(!$auth) { echo "disabled"; }?>>
                        <?php if (isset($errSubject)) {
                            echo "<p class='text-danger'>$errSubject</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="type" class="col-sm-2 control-label">Type</label>

                    <div class="col-sm-4">
                        <select id="type" name="type" class="form-control" <? if(!$auth) { echo "disabled"; }?>>
                        <?php foreach($types as $item) { ?>
<option value="<?=$item['id']?>" <?php if ($type === $item['id']) { echo("selected");}?>> <?php echo $item['name']; ?></option>
                        <?php } ?>
                        </select>
                        <?php if (isset($errType)) {
                            echo "<p class='text-danger'>$errType</p>";
                        } ?>
                    </div>
                    <label for="level" class="col-sm-2 control-label">Alvorlighetsgrad</label>

                    <div class="col-sm-4">
                        <select id="level" name="level" class="form-control" <? if(!$auth) { echo "disabled"; }?>>
                            <?php foreach($levels as $item) { ?>
<option value="<?=$item['id']?>" <?php if ($level === $item['id']) { echo("selected");}?>> <?php echo $item['name']; ?></option>
                            <?php } ?>
                        </select>
                        <?php if (isset($errLevel)) {
                            echo "<p class='text-danger'>$errLevel</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description" class="col-sm-2 control-label">Beskrivelse</label>

                    <div class="col-sm-10">
                        <textarea class="form-control" rows="4" id="description" name="description" <? if(!$auth) { echo "disabled"; }?>><?php if (isset($description)) { echo $description; }?></textarea>
                        <?php if (isset($errDesc)) {
                            echo "<p class='text-danger'>$errDesc</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Navn</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Ditt navn"
                               value="<?php if (isset($name)) { echo $name; }?>"
			       <? if(!$auth) { echo "disabled"; }?>>
                        <?php if (isset($errName)) {
                            echo "<p class='text-danger'>$errName</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-2 control-label">Email</label>

                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="example@domain.com" value="<?php if (isset($email)) { echo $email; }?>"
			       <? if(!$auth) { echo "disabled"; }?>>
                        <?php if (isset($errEmail)) {
                            echo "<p class='text-danger'>$errEmail</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="human" class="col-sm-2 control-label">2 + 3 = ?</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="human" name="human" placeholder="Ditt svar"
                               value="<?php if (isset($human)) { echo $human; }?>"
			       <? if(!$auth) { echo "disabled"; }?>>
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
                <? if(!empty($comments )) { ?>
		<div class="form-group">
			<label for="comments" class="col-sm-2 control-label">Comments</label>
                	<div id="comments" class="col-sm-10">
                <? foreach($comments as $comment) { ?>
				<div class ="list-group-item">
					<div class="list-group-heading"><b><?=$comment['user'];?></b> <span class="label label-default pull-right"><?=date('d M Y H:i', strtotime($comment['created']));?></span></div><br/>
					<div class="list-group-item-text"><?=$comment['html'];?></div>
				</div>
		<? } ?>
			</div>
                </div>
                <? } ?>

            </div>
            <div class="panel-footer">
                <div class="form-group">
                    <div class="col-sm-12 text-right">
                        <a href="/sar-rapport/feedback" class="btn btn-default" role="button">Ny tilbakemelding</a>
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
