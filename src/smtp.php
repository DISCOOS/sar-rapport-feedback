<?php

function notify($subject, $to, $name, $body) {

    try {

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

    $from = array (REPLY_TO_EMAIL => REPLY_TO_NAME);
    $message->setFrom($from)->setReplyTo($from)->setBody($body, 'text/html');

    $message->setTo(array($to => $name));
    $mailer->send($message, $failed);

    return empty($failed) ? true : $failed;
    } catch(Swift_TransportException $e) {
	return $e->getMessage();
    }
}
