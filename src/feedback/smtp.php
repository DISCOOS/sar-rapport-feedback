<?php

function notify($subject, $to, $name, $body) {

    $failed = array();

    // Create the Transport
    $transport = \Swift_SmtpTransport::newInstance(
        SMTP_HOST,
        SMTP_PORT,
        SMTP_ENCRYPTION)
        ->setUsername(SMTP_USERNAME)
        ->setPassword(SMTP_PASSWORD);


    // Create the Mailer using your created Transport
    $mailer = \Swift_Mailer::newInstance($transport);

    // Create a message
    $message = \Swift_Message::newInstance($subject);

    $from = array ('rge@hjelpekorps.org' => 'RG Ettersøkning');
    $message->setFrom($from)->setBody($body, 'text/html');

    $to = $this->prepareAddresses(array($to => $name));

    $message->setTo($to);
    $mailer->send($message, $failed);

    return empty($failed) ? true : $failed;

}
