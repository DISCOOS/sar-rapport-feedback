<?php

function isset_get($array, $name, $default = false)
{
    return isset($array[$name]) ? $array[$name] : $default;
}

function filter_post($name, $filter = FILTER_DEFAULT)
{
    return filter_input(INPUT_POST, $name, $filter, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function redirect($uri) {
    $url = get_url().$uri;
    var_dump($url);
    header("Location: $url");
    exit;
}


function get_uri() {
    $name = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
    $path = str_replace($name, '', $_SERVER['PHP_SELF']);

    $folders = array();
    foreach(scandir(APP_PATH) as $file) {            
        if($file === '.' || $file === '..') {
            continue;
        }
        if(is_dir(APP_PATH . '/' . $file)) { 
            $folders[] = $file;
        }
    }
    
    foreach($folders as $folder){
        $match = strstr($path, $folder);
        if($match){
            return str_replace($match, '', $path);
        }
    }

    return $path;

}

function get_url() {
    $url = '';
    if(isset($_SERVER["SERVER_PROTOCOL"])) {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
        $url = $protocol . "://" . $_SERVER['SERVER_NAME'] . get_uri();
    }
    return $url;
}
