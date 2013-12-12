<?
require_once( 'class.AnimalCaptcha.php' );

// Start or acquire a session if needed
if (!isset($_SESSION))
{
	session_start ();
	header("Cache-control: private");
}

$sn = key_exists( 'session_name', $_GET ) ? $_GET['session_name'] : 'animal_captcha';

// Load the class
$ac = new AnimalCaptcha( $sn );
// Pick an action
if (isset($_GET['generate_captcha']))
	print implode( ',', $ac->generateCaptcha() );
else if (isset($_GET['get_image']))
{
	#Header("Content-type: image/jpeg");
	print $ac->getImage($_GET['get_image']);
}
?>
