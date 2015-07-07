<?php
// Start or acquire a session if needed
if (!isset($_SESSION))
{
    session_start();
    header("Cache-control: private");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>AnimalCaptcha example</title>
	<script type="text/javascript" src="js/mootools.js"></script>
	<script type="text/javascript" src="js/ac.js"></script>
	<script type="text/javascript">
	var ac;
	window.addEvent("domready", function ()
	{
		ac = new AnimalCaptcha("ac", "form"); 
		ac.addEvent("imagePicked", captchaImagePicked);

		function captchaCheck(response)
		{
			if (response == "ok")
			{
				$('form').submit();
			}
			else
			{
				if (response == "error_not_enough")
					alert("Wrong!\nYou didn't check them all!");
				else if (response == "error_wrong")
					alert("Wrong!\nYou didn't check only elephants!");
				else if (response == "error_regenerate")
				{
					alert("Wrong!\nYou've exceeded try count for this captcha.\nGenerating new one...");
					ac.requestCaptcha();
				}
				else
					alert ("Unknown error!");
			}
		}

		function captchaImagePicked(count)
		{
			$('selected_num').set('text', count);
		}

		$('form').addEvent("submit", function(e){
			e.preventDefault();
			ac.checkCaptcha(captchaCheck);
		});
	});

	</script>
	<style type="text/css">
		#ac {
			border: 1px solid #777;
			width: 268px;
			height: 268px;
		}
		.ac_image_div {
			float: left;
			width: 80px;
			height: 80px;
			padding: 4px;
			cursor: pointer;
		}
		.ac_image {
			border: 2px solid #999;
		}
		.ac_image_div.selected .ac_image {
			border: 2px solid #0f0 !important;
		}
	</style>
</head>

<body>
	<h3>AnimalCaptcha example</h3>
<?php
if (!empty($_POST))
{
    require_once "class.AnimalCaptcha.php";

    // Initialize AnimalCaptcha class
    $ac = new AnimalCaptcha;

    // Didn't send captcha answer?
    if (empty($_POST['captcha']))
    {
        print "No captcha answer found!"; // or redirect to error page
    }
    // Check captcha answer
    else if ($ac->check($_POST['captcha']) !== "ok")
    {
        print "Wrong captcha answer!"; // or redirect to error page
    }
    else
    {
        //////////////////////////////////////
        // Put your form processing here... //
        //////////////////////////////////////
        print "Congratulations!<br />Form sucessfully submitted!";
    }
}
?>
	<hr>
	<form method='post' action='example.php' id='form'>
	<p>Check if you are a human.<br />Select <em>all</em> elephants from the images below.</p>
	<div id="ac">
	</div>
	<p>Selected <span id='selected_num'>0</span> elephant(s).</p>

	<input type="submit" value="Submit!" />
	</form>
</body>
</html>
