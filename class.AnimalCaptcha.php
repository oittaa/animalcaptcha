<?
require_once("random.php");
class AnimalCaptcha
{
    // CONFIGURATION
    public $img_dir     = "images"; // Directory with captcha images

    public $image_count;      // Number of images to show
    public $good_count;       // Number of good images to display in the grid

    public $rnd_good = true;  // Randomize number of good images
                              // $good_count becomes maximum number

    public $use_imagick = true; // Should use ImageMagick for image processing?
    public $use_gd      = true;  // Should use GD if ImageMagick is unavailable?

    public $max_check_count = 3; // Maximal number of checks per captcha, per user
                                 // 0 if unlimited

    public $session_name;     // Session namespace to store generated captcha stuff
                              // in case site has 2 separate forms using ac

    public function __construct($session_name = 'animal_captcha')
    {
        $this->session_name = $session_name;
    }

    // Generate images for the captcha
    public function generateCaptcha($image_count = 9, $good_count = 4)
    {
        $this->image_count = $image_count;
        $this->good_count = $good_count;

        $_SESSION[$this->session_name] = array();

        $_SESSION[$this->session_name]['ac_check_count'] = 0;

        if ($this->rnd_good == false)
            $good = $this->good_count;
        else
            $good = $good_count === 1 ? 1 : random_int(1, $this->good_count);

        $_SESSION[$this->session_name]['ac_images_good_count'] = $good;

        $good_history = array();
        $bad_history = array();
        $images = array();
        $good_images = glob($this->img_dir . "/good/*.jpg");
        $bad_images = glob($this->img_dir . "/bad/*.jpg");

        // Generate an array of images
        for($i = 0; $i < $this->image_count; $i++)
        {
            // "Good" images
            if ($good > 0)
            {
                $is_good = true;
                do {
                    $filename = $good_images[random_int(0, count($good_images)-1)];
                } while (in_array($filename, $good_history));
                $good_history[] = $filename;
            }

            // "Bad" images
            else
            {
                $is_good = false;
                do {
                    $filename = $bad_images[random_int(0, count($bad_images)-1)];
                } while (in_array($filename, $bad_history));
                $bad_history[] = $filename;
            }

            // Append it to image array
            $images[] = array(
                "is_good" => $is_good,
                "filename" => $filename,
                "id" => random_str(16)
            );
            $good--;
        }

        // Shuffle the array, set ids as keys and generate output
        $images_shuffled = array();
        $output = array();
        while (count($images) > 0) {
            $rand_num = count($images) === 1 ? 0 : random_int(0, count($images)-1);
            $extracted = array_splice($images, $rand_num, 1);
            $images_shuffled[$extracted[0]['id']] = $extracted[0];
            $output[] = $extracted[0]['id'];
        }

        // Save image array into session
        if (isset($_SESSION[$this->session_name]['ac_images']))
            unset ($_SESSION[$this->session_name]['ac_images']);

        $_SESSION[$this->session_name]['ac_images'] = $images_shuffled;

        // Return generated ids
        return $output;
    }

    public function getCaptcha($image_count = 9, $good_count = 1)
    {
        if (isset($_SESSION[$this->session_name]['ac_images']))
        {
            $output = array();
            foreach($_SESSION[$this->session_name]['ac_images'] as $img)
            {
                $output[] = $img['id'];
            }
            return $output;
        }
        else
            return $this->generateCaptcha($image_count, $good_count);
    }

    // ImageMagick's processing
    public function getImageImagick($filename)
    {
        // Load the image
        $image = new Imagick($filename);

        // Randomize HSL and quality
        $h = random_int(95, 105);
        $s = random_int(80, 120);
        $l = random_int(80, 120);
        $quality = random_int(70,95);

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
        $contrast = random_int(0, 10);
        $r = random_int(0, 10);
        $g = random_int(0, 10);
        $b = random_int(0, 10);
        $quality = random_int(70,95);

        // Apply changes to image
        imagefilter($image, IMG_FILTER_CONTRAST, $contrast);
        imagefilter($image, IMG_FILTER_COLORIZE, $r, $g, $b, 0);

        ob_start();
        imagejpeg($image, $img, $quality);
        imagedestroy($image);
        $img = ob_get_contents();
        ob_end_clean();
        return $img;
    }


    // Return an image based on random image ID
    public function getImage($id)
    {
        if (isset($_SESSION[$this->session_name]['ac_images'][$id]))
        {
            $filename = $_SESSION[$this->session_name]['ac_images'][$id]['filename'];

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
        return false;
    }

    // Check image list
    public function check($list)
    {
        // Raise check count
        if (!isset($_SESSION[$this->session_name]['ac_check_count']))
            $_SESSION[$this->session_name]['ac_check_count'] = 1;
        else
            $_SESSION[$this->session_name]['ac_check_count']++;

        // Check if user exceeded check count
        if ($this->max_check_count && $_SESSION[$this->session_name]['ac_check_count'] > $this->max_check_count)
            return "error_regenerate";

        // Check if user submitted correct number of id's
        $check_list = explode(",", $list);
        if (count(array_unique($check_list)) != $_SESSION[$this->session_name]['ac_images_good_count'])
            return "error_not_enough";

        // Check the id's
        foreach ($check_list as $check)
        {
            if (!$_SESSION[$this->session_name]['ac_images'][$check]['is_good'])
                return "error_wrong";
        }

        return "ok";
    }
}
