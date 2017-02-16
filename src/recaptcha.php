<?php

function recaptcha_verify() {
    $process = curl_init(RECAPTCHA_VERIFY_URL);
    
    curl_setopt(
        $process,
        CURLOPT_HTTPHEADER,
        array(
            'Content-type: application/x-www-form-urlencoded'
        )
    );

//var_dump(filter_post('g-recaptcha-response'));

    curl_setopt(
        $process,
        CURLOPT_POSTFIELDS,
        http_build_query(
            array(
                'secret' => RECAPTCHA_SECRET_KEY,
                'response' => filter_post('g-recaptcha-response'),
                'remoteip' => get_client_ip(),
            )
        )
    );

    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($process);

    if ($result !== false) {
        $result = json_decode($result, true);
	$result = $result['success'];
    }

    curl_close($process);

    return $result;
}

