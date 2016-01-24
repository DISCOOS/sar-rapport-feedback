<?php

require 'config.php';

define ('HOST', 'https://api.taiga.io/api/v1/');

function notify($subject, $to, $message) {

    // To send HTML mail, the Content-type header must be set
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

    // Additional headers
    $headers .= 'Reply-To: RG Ettersøkning <rge@hjelpekorps.org>' . "\r\n";
    $headers .= 'From: RG Ettersøkning <rge@hjelpekorps.org>' . "\r\n";
//    $headers .= 'Bcc: rge@hjelpekorps.org' . "\r\n";

    // Mail it
    mail($to, $subject, $message, $headers);

}

function taiga_login() {
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
                'username' => USERNAME,
                'password' => PASSWORD
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

function taiga_get_issue_types($auth) {
    $process = curl_init(HOST . 'issue-types?project=' . PROJECT);
    curl_setopt(
        $process,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8',
            "Authorization: Bearer $auth"
        )
    );

    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    $types = curl_exec($process);
    if ($types !== false) {
        $types = json_decode($types, true);
    }
    curl_close($process);

    return $types;
}

function taiga_get_severity_levels($auth) {
    $process = curl_init(HOST . 'severities?project=' . PROJECT);
    curl_setopt(
        $process,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8',
            "Authorization: Bearer $auth"
        )
    );

    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    $levels = curl_exec($process);
    if ($levels !== false) {
        $levels = json_decode($levels, true);
    }
    curl_close($process);

    return $levels;
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
                'project' => PROJECT,
                'type' => filter_post('type', FILTER_VALIDATE_INT),
                'severity' => filter_post('level', FILTER_VALIDATE_INT),
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
        curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt(
            $process,
            CURLOPT_POSTFIELDS,
            json_encode(
                array(
                    'project' => PROJECT,
                    'type' => filter_post('type', FILTER_VALIDATE_INT),
                    'severity' => filter_post('level', FILTER_VALIDATE_INT),
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
        curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PATCH");
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

function taiga_get_issue_comments($auth, $id)
{
    $process = curl_init(HOST . "history/issue/$id");
    curl_setopt(
        $process,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8',
            "Authorization: Bearer $auth"
        )
    );

    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    $comments = curl_exec($process);
    if ($comments !== false) {
        $history = json_decode($comments, true);
        $comments = array();
        foreach($history as $comment) {
            if(!empty($comment['comment_html'])) {
                $comments[] = array(
                    'html' => $comment['comment_html'],
                    'user' => $comment['user']['name'],
                    'created' => date($comment['created_at'])
                );
            }
        }
    }
    curl_close($process);

    return $comments;

}

function isset_get($array, $name, $default = false)
{
    return isset($array[$name]) ? $array[$name] : $default;
}

function filter_post($name, $filter = FILTER_DEFAULT)
{
    return filter_input(INPUT_POST, $name, $filter, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}
