<?php

define ('HOST', 'https://taiga.io/api/v1/');

if (isset($_POST["submit"])) {
    $name = isset_get($_POST, 'name');
    $email = isset_get($_POST,'email');
    $subject = isset_get($_POST,'subject');
    $type = isset_get($_POST,'type');
    $description = isset_get($_POST, 'description');
    $human = isset_get($_POST,'human');

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
        if($auth = taiga_login()) {
            if(taiga_create_issue($auth)) {
                $result = '<div class="alert alert-success">Takk! Vi vil ta kontakt når din tilbakemelding er behandlet.</div>';
            }
        }
    }
    if(!isset($result)) {
        $result = '<div class="alert alert-danger">Beklager, din henvendelse kunne ikke registres. Vennligst prøv igjen senere.</div>';
    }
}

function taiga_login() {
    $process = curl_init(HOST.'auth');
    curl_setopt($process, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
    curl_setopt($process, CURLOPT_POSTFIELDS, array(
            'type' => 'normal',
            'username' => '',
            'password' => ''
        ));

    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    $user = curl_exec($process);
    if($user !== FALSE) {
        $user = json_decode($user, true)['auth_token'];

    }
    curl_close($process);
    return $user;
}

function taiga_create_issue($auth) {
    $process = curl_init(HOST.'issues');
    curl_setopt($process, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml',
            "Authorization: Bearer $auth"
        ));
    curl_setopt($process, CURLOPT_POSTFIELDS, array(
            'project' => 1,
            'type' => filter_post('type', FILTER_VALIDATE_INT),
            'subject' => filter_post('subject'),
            'description' => filter_post('description'),
            'reporter_name' => filter_post('name'),
            'reporter_email' => filter_post('email')
        ));

    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    $issue = curl_exec($process);
    if($issue !== FALSE) {
        $issue = json_decode($issue, true)['id'];

    }
    curl_close($process);
    return $issue;
}

function isset_get($array, $name, $default = false) {
    return isset($array[$name]) ? $array[$name] : $default;
}
function filter_post($name, $filter = FILTER_DEFAULT) {
    return filter_input(INPUT_POST, $name, $filter, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>SAR-rapport - tilbakemelding</title>
</head>
<body>
<form class="form-horizontal" role="form" method="post" action="index.php">
    <div class="form-group">
        <label for="name" class="col-sm-2 control-label">Emne</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="subject" name="subject" placeholder="Kort beskrivende tekst" value="<?php echo $subject; ?>">
            <?php echo "<p class='text-danger'>$errSubject</p>";?>
        </div>
    </div>
    <div class="form-group">
        <label for="type" class="col-sm-2 control-label" value="<?php echo $type; ?>">Tilbakemelding</label>
        <div class="col-md-2">
            <select id="type" name="type" class="form-control">
                <option value="0"></option>
                <option value="1">Feil</option>
                <option value="2">Spørsmål</option>
                <option value="3">Forslag</option>
            </select>
            <?php echo "<p class='text-danger'>$errType</p>";?>
        </div>
    </div>
    <div class="form-group">
        <label for="description" class="col-sm-2 control-label">Beskrivelse</label>
        <div class="col-sm-10">
            <textarea class="form-control" rows="4" id="description" name="description"><?php echo $description;?></textarea>
            <?php echo "<p class='text-danger'>$errDesc</p>";?>
        </div>
    </div>
    <div class="form-group">
        <label for="name" class="col-sm-2 control-label">Navn</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="name" name="name" placeholder="Ditt navn" value="<?php echo $name; ?>">
            <?php echo "<p class='text-danger'>$errName</p>";?>
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="col-sm-2 control-label">Email</label>
        <div class="col-sm-10">
            <input type="email" class="form-control" id="email" name="email" placeholder="example@domain.com" value="<?php echo $email; ?>">
            <?php echo "<p class='text-danger'>$errEmail</p>";?>
        </div>
    </div>
    <div class="form-group">
        <label for="human" class="col-sm-2 control-label">2 + 3 = ?</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="human" name="human" placeholder="Ditt svar" value="<?php echo $human; ?>">
            <?php echo "<p class='text-danger'>$errHuman</p>";?>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2">
            <input id="submit" name="submit" type="submit" value="Send" class="btn btn-primary">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2">
            <?php echo $result; ?>
        </div>
    </div>
</form>


</body>
</html>