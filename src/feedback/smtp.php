<?php

function notify($subject, $to, $body) {

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

    $from = $this->prepareAddress('RG Ettersøkning <rge@hjelpekorps.org>');
    $message->setFrom($from)->setBody($body, 'text/html');

    $to = $this->prepareAddresses($to);

    $message->setTo($to);
    $mailer->send($message, $failed);

    return empty($failed) ? true : $failed;

}
