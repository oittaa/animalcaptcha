<?
require_once("class.AnimalCaptcha.php");

// Start or acquire a session if needed
if (!isset($_SESSION))
{
    session_start();
    header("Cache-control: private");
}

$sn = key_exists('session_name', $_GET) ? $_GET['session_name'] : 'animal_captcha';

// Load the class
$ac = new AnimalCaptcha($sn);
// Pick an action
if (isset($_GET['generate_captcha']))
    print implode(',', $ac->generateCaptcha());
else if (isset($_GET['get_image']))
{
    if ($img = $ac->getImage($_GET['get_image']))
    {
        header("Content-type: image/jpeg");
        print $img;
    }
    else if (function_exists('http_response_code'))
    {
        http_response_code(404);
    }
    else
    {
        header("HTTP/1.0 404 Not Found");
    }
}
else if (isset($_GET['check']))
{
    print $ac->check($_GET['check']);
}
