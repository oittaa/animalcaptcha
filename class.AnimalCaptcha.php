<?
class AnimalCaptcha 
{
	// CONFIGURATION
	public $img_dir     = "img/animalcaptcha"; // Directory with captcha images

	public $good_images = 12; // Number of good images
	public $bad_images  = 45; // Number of bad images

	public $image_count = 9;  // Number of images to show
	public $good_count  = 1;  // Number of good images to display in the grid

	public $rnd_good = true;  // Randomize number of good images
							  // $good_count becomes maximum number
	
	public $use_imagick = true; // Should use ImageMagick for image processing?
	public $use_gd      = true;  // Should use GD if ImageMagick is unavailable?

	public $max_check_count = 3; // Maximal number of checks per captcha, per user
								 // 0 if unlimited

	// Generate images for the captcha
	public function generateCaptcha()
	{
		$_SESSION['ac_check_count'] = 0;

		if ($this->rnd_good == false)
			$good = $this->good_count;
		else
			$good = rand(1, $this->good_count);

		$_SESSION['ac_images_good_count'] = $good;

		$good_history = array();
		$bad_history = array();
		$images = array();

		// Generate an array of images
		for($i = 0; $i < $this->image_count; $i++)
		{
			// "Good" images
			if ($good > 0)
			{
				$is_good = true;
				$id = substr(md5(rand()), 0, 8);
				$img_num = rand(1, $this->good_images);
				while (in_array($img_num, $good_history))
					$img_num = rand(1, $this->good_images);

				$good_history[] = $img_num;
			}

			// "Bad" images
			else
			{
				$is_good = false;
				$id = substr(md5(rand()), 0, 8);
				$img_num = rand(1, $this->bad_images);
				while (in_array($img_num, $bad_history))
					$img_num = rand(1, $this->bad_images);

				$bad_history[] = $img_num;
			}

			// Append it to image array
			$images[] = array(
				"is_good" => $is_good,
				"filename" => $this->img_dir . "/" . ($is_good ? "good" : "bad") . "/" . $img_num . ".jpg",
				"id" => $id
			);
			$good--;
		}

		// Shuffle the array
		shuffle($images);

		// Set id's as keys
		foreach ($images as $key => $image)
		{
			$images[$image['id']] = $image;
			unset($images[$key]);
		}
		
		// Save image array into session
		if (isset($_SESSION['ac_images']))
			unset ($_SESSION['ac_images']);

		$_SESSION['ac_images'] = $images;

		// Print image ids to the browser
		$output = array();
		foreach ($images as $image)
		{
			$output[] = $image['id'];
		}
		return $output;
	}
	
	public function getCaptcha()
	{
		if ( isset( $_SESSION['ac_images'] ) )
		{
			$output = array();
			foreach( $_SESSION['ac_images'] as $img )
			{
				$output[] = $img['id'];
			}
			return $output;
		}
		else
			return $this->generateCaptcha();
	}

	// ImageMagick's processing
	public function getImageImagick($filename)
	{
		// Load the image
		$image = new Imagick($filename);

		// Randomize HSL and quality
		$h = rand(95, 105);
		$s = rand(80, 120);
		$l = rand(80, 120);
		$quality = rand(70,95);

		// Apply changes to image
		$image->setImageCompression(Imagick::COMPRESSION_JPEG); 
		$image->setImageCompressionQuality($quality);
		$image->modulateImage($h, $s, $l);

		return $image;
	}

	// PHP GD processing when Imagick is not available
	public function getImageGD($filename)
	{
		// Load the image
		$image = imagecreatefromjpeg($filename);
		$img = null;

		// Randomize contrast, colorization and quality
		$contrast = rand(-10, 10);
		$r = rand(-10, 10);
		$g = rand(-10, 10);
		$b = rand(-10, 10);
		$quality = rand(70,95);

		// Apply changes to image
		imagefilter($image, IMG_FILTER_CONTRAST, $contrast);
		imagefilter($image, IMG_FILTER_COLORIZE, $r, $g, $b, 0);

		imagejpeg($image, $img, $quality);
		imagedestroy($image);
		return $img;
	}


	// Return an image based on random image ID
	public function getImage($id)
	{
		if ($_SESSION['ac_images'][$id])
		{
			$filename = $_SESSION['ac_images'][$id]['filename'];

			// Pass the file to image processing or just display it
			if ($this->use_imagick && extension_loaded('imagick'))
				return $this->getImageImagick($filename);

			else if ($this->use_gd && extension_loaded('gd'))
				return $this->getImageGD($filename);

			else
			{
				return file_get_contents($filename);
			}
		}
	}

	// Check image list
	public function check($list)
	{
		// Raise check count
		if (!isset($_SESSION['ac_check_count']))
			$_SESSION['ac_check_count'] = 1;
		else 
			$_SESSION['ac_check_count']++;

		// Check if user exceeded check count
		if ($this->max_check_count && $_SESSION['ac_check_count'] > $this->max_check_count)
			return "error_regenerate";

		// Check if user submitted correct number of id's
		$check_list = explode(",", $list);
		if (count(array_unique($check_list)) != $_SESSION['ac_images_good_count'])
			return "error_not_enough";

		// Check the id's
		foreach ($check_list as $check)
		{
			if (!$_SESSION['ac_images'][$check]['is_good'])
				return "error_wrong";
		}
		
		return "ok";
	}
}
?>
