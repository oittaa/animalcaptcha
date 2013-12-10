<?
require_once( 'class.AnimalCaptcha.php' );

// Start or acquire a session if needed
if (!isset($_SESSION))
{
	session_start ();
	header("Cache-control: private");
}

// Load the class
$ac = new AnimalCaptcha;

// Pick an action
if (isset($_GET['generate_captcha']))
	print implode( ',', $ac->generateCaptcha() );
else if (isset($_GET['get_image']))
{
	Header("Content-type: image/jpeg");
	print $ac->getImage($_GET['get_image']);
}
?>
