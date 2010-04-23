<?

/**************************************************
 * Example form processing file for AnimalCaptcha *
 **************************************************/

// Include AnimalCaptcha class
require_once "class.AnimalCaptcha.php";

// Parse POST request
if (isset($_POST))
{
	// Initialize AnimalCaptcha class
	$ac = new AnimalCaptcha;

	// Didn't send captcha answer?
	if (!$_POST['captcha'])
		die("No captcha answer found!"); // or redirect to error page

	// Check captcha answer
	$check = $ac->check($_POST['captcha']);
	if ($check !== "ok")
		die("Wrong captcha answer!"); // or redirect to error page

	//////////////////////////////////////
	// Put your form processing here... //
	//////////////////////////////////////

	print "Congratulations!<br />Form sucessfully submitted!";
}

?>
