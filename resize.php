<?php
	// inspired by Mark Setchell's answer at https://stackoverflow.com/questions/35301104/resize-image-keep-proportion-add-white-background
	// php rect.php -w dst_width -h dst_height -b #background_color_hash -i input_file_name_with.ext -o output_file_name_with.ext
	$shortopts = "";
	$shortopts .= "w::"; // optional: target width, default 540
	$shortopts .= "h::"; // optional: target height, default 300
	$shortopts .= "b::"; // optional: background color hash
	$shortopts .= "i:"; // required: input file name
	$shortopts .= "o:"; // required: output file name

	$options = getopt($shortopts);

	$dst_image_w = 540;
	$dst_image_h = 300;
	$r = 255;
	$g = 255;
	$b = 255;

	$src_ext = "png";
	$dst_ext = "png";

	if (array_key_exists("w", $options)) {
		$dst_image_w = $options["w"];
	}

	if (array_key_exists("h", $options)) {
		$dst_image_h = $options["h"];
	}	

	if (array_key_exists("b", $options)) {
		list($r, $g, $b) = sscanf($options["b"], "#%02x%02x%02x");
	}

	if (array_key_exists("i", $options)) {
		$src_image_file = $options["i"];
		$src_ext = explode(".", strtolower($src_image_file))[1];
	} else {
		exit("ERROR: Enter input file name.\n");
	}

	if (array_key_exists("o", $options)) {
		$dst_image_file = $options["o"];
		$dst_ext = explode(".", strtolower($dst_image_file))[1];
	} else {
		exit("ERROR: Enter output file name.\n");
	}
	
	// Load original image as per extension
	switch ($src_ext) {
	case "png":
		$src_image  = imagecreatefrompng($src_image_file);
		break;
	case "jpg":
	case "jpeg":
		$src_image  = imagecreatefromjpeg($src_image_file);
		break;
	case "gif":
		$src_image  = imagecreatefromgif($src_image_file);
		break;
	case "bmp":
		$src_image  = imagecreatefrombmp($src_image_file);
		break;
	default:
		exit("Only png/jpg/jpeg/gif/bmp extensions supported");
	}

	$src_w = imagesx($src_image); // image width
	$src_h = imagesy($src_image); // image height

	// Create output canvas and fill with background color
	$dst_image = imagecreatetruecolor($dst_image_w, $dst_image_h);
	$bg_color = imagecolorallocate($dst_image, $r, $g, $b);
	imagefill($dst_image, 0, 0, $bg_color);

	// if src is smaller than dst, place in the center
	if ($src_w <= $dst_image_w and $src_h <= $dst_image_h) {
		$dst_w = $src_w;
		$dst_h = $src_h;
		printf("Source smaller than destination: %dx%d\n", $src_w, $src_h);
	} elseif ($src_w < $src_h) {
		// portrait
		$dst_w = intval($dst_image_h * $src_w / $src_h);
		$dst_h = $dst_image_h;
		if ($dst_w > $dst_image_w) {
			$dst_w = $dst_image_w;
			$dst_h = intval($dst_image_w * $src_h / $src_w);
		}
		printf("Source is portrait: %dx%d\n", $src_w, $src_h);
	} else {
		// landscape
		$dst_w = $dst_image_w;
		$dst_h = intval($dst_image_w * $src_h / $src_w);
		if ($dst_h > $dst_image_h) {
			$dst_h = $dst_image_h;
			$dst_w = intval($dst_image_h * $src_w / $src_h);
		}
		printf("Source is landscape: %dx%d\n", $src_w, $src_h);
	}
	imagecopyresampled(
		$dst_image,
		$src_image,
		intval(($dst_image_w - $dst_w)/2),
		intval(($dst_image_h - $dst_h)/2),
		0,
		0,
		$dst_w,
		$dst_h,
		$src_w,
		$src_h
	);
	printf("Destination size: %dx%d\n", $dst_w, $dst_h);
	switch ($dst_ext) {
	case "png":
		imagepng($dst_image, $dst_image_file);
		break;
	case "jpg":
	case "jpeg":
		imagejpeg($dst_image, $dst_image_file);
		break;
		break;
	case "gif":
		imagegif($dst_image, $dst_image_file);
		break;
		break;
	case "bmp":
		imagebmp($dst_image, $dst_image_file);
		break;
	default:
		exit("Only png/jpg/jpeg/gif/bmp extensions supported");
	}
?>
