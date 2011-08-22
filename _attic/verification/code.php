<?PHP
	session_id($_GET['session_name']);
	session_start();   //$_GET['session_name']);
/*
	require_once 'include'.DIRECTORY_SEPARATOR.'myTrace.class.php';
	$trace = &new myTrace(null,'logs'.DIRECTORY_SEPARATOR.'error.log');


	$trace->p('GET: '.print_r($_GET,true));
	$trace->p('SESSION: '.print_r($_SESSION,true));
	$trace->p('Session_id: '.session_id().' session name: '.session_name());
*/
	// images defaults
	$picWidth  = "140";
	$picHeight = "35";
	$pt_size   = "18";
	$spacing   = 0.6;
	$params    = array("linespacing" => $spacing);

	define('FONT_DIR',dirname(__FILE__).'/ttfs');

	///////////////////////////
	// random color sets
	///////////////////////////
	if (!empty($bg))
	{

		$exp = explode("|",$bg);

		// background color
		$cust_bg_r = $exp[0];
		$cust_bg_g = $exp[1];
		$cust_bg_b = $exp[2];

		$cust_defined = 1;

	}

	if (!empty($tx))
	{

		$exp = explode("|",$tx);

		// background color
		$cust_tx_r = $exp[0];
		$cust_tx_g = $exp[1];
		$cust_tx_b = $exp[2];

		$cust_defined = 1;

	}

	if (!empty($ln))
	{

		$exp = explode("|",$ln);

		// background color
		$cust_ln_r = $exp[0];
		$cust_ln_g = $exp[1];
		$cust_ln_b = $exp[2];

		$cust_defined = 1;

	}

	######### set 1
	$random_color_key = 0; // first one set to "= 0;", all others set to ++;
	// background color
	$rand_bg_r[$random_color_key] = '51';
	$rand_bg_g[$random_color_key] = '51';
	$rand_bg_b[$random_color_key] = '51';

	// text color
	$rand_tx_r[$random_color_key] = '204';
	$rand_tx_g[$random_color_key] = '204';
	$rand_tx_b[$random_color_key] = '204';
	// grid line color
	$rand_ln_r[$random_color_key] = '153';
	$rand_ln_g[$random_color_key] = '153';
	$rand_ln_b[$random_color_key] = '153';

	######### set 2
	$random_color_key++;
	// background color
	$rand_bg_r[$random_color_key] = '153';
	$rand_bg_g[$random_color_key] = '0';
	$rand_bg_b[$random_color_key] = '0';
	// text color
	$rand_tx_r[$random_color_key] = '255';
	$rand_tx_g[$random_color_key] = '204';
	$rand_tx_b[$random_color_key] = '204';
	// grid line color
	$rand_ln_r[$random_color_key] = '255';
	$rand_ln_g[$random_color_key] = '102';
	$rand_ln_b[$random_color_key] = '102';

	######### set 3
	$random_color_key++;
	// background color
	$rand_bg_r[$random_color_key] = '51';
	$rand_bg_g[$random_color_key] = '0';
	$rand_bg_b[$random_color_key] = '102';
	// text color
	$rand_tx_r[$random_color_key] = '204';
	$rand_tx_g[$random_color_key] = '204';
	$rand_tx_b[$random_color_key] = '255';
	// grid line color
	$rand_ln_r[$random_color_key] = '153';
	$rand_ln_g[$random_color_key] = '102';
	$rand_ln_b[$random_color_key] = '255';

	######### set 3
	$random_color_key++;
	// background color
	$rand_bg_r[$random_color_key] = '0';
	$rand_bg_g[$random_color_key] = '51';
	$rand_bg_b[$random_color_key] = '0';
	// text color
	$rand_tx_r[$random_color_key] = '153';
	$rand_tx_g[$random_color_key] = '255';
	$rand_tx_b[$random_color_key] = '153';
	// grid line color
	$rand_ln_r[$random_color_key] = '0';
	$rand_ln_g[$random_color_key] = '204';
	$rand_ln_b[$random_color_key] = '0';

	####################################################
	####            END OF CONFIG SECTION           ####
	####################################################

	$string = $_SESSION['S_IMAGE_DECODED'];

//    $split = preg_split("//",$string);

//    $string = implode(" ",$split);
//$string = '1d19d';
	// image manipulation
	$jpeg = ImageCreate($picWidth,$picHeight);

	if ($random_color_key > 0){ $rand = rand(0,$random_color_key); } else { $rand = $random_color_key; }

	// override random bg colors
	if (!empty($bg))
	{

		$rand_bg_r[$rand] = $cust_bg_r;
		$rand_bg_g[$rand] = $cust_bg_g;
		$rand_bg_b[$rand] = $cust_bg_b;

	}

	// override random text color
	if (!empty($tx))
	{

		$rand_tx_r[$rand] = $cust_tx_r;
		$rand_tx_g[$rand] = $cust_tx_g;
		$rand_tx_b[$rand] = $cust_tx_b;

	}

	// override random line color
	if (!empty($ln))
	{

		$rand_ln_r[$rand] = $cust_ln_r;
		$rand_ln_g[$rand] = $cust_ln_g;
		$rand_ln_b[$rand] = $cust_ln_b;

	}


	if (empty($cust_defined))
	{

		$flip_flop = rand(0,1);
		if ($flip_flop)
		{

			$hold_r = $rand_bg_r[$rand];
			$hold_b = $rand_bg_b[$rand];
			$hold_g = $rand_bg_g[$rand];

			$rand_bg_r[$rand] = $rand_tx_r[$rand];
			$rand_bg_b[$rand] = $rand_tx_b[$rand];
			$rand_bg_g[$rand] = $rand_tx_g[$rand];

			$rand_tx_r[$rand] = $hold_r;
			$rand_tx_b[$rand] = $hold_b;
			$rand_tx_g[$rand] = $hold_g;

		}

	}

	$bg = ImageColorAllocate($jpeg,$rand_bg_r[$rand],$rand_bg_g[$rand],$rand_bg_b[$rand]);

	$tx = ImageColorAllocate($jpeg,$rand_tx_r[$rand],$rand_tx_g[$rand],$rand_tx_b[$rand]);

	$ln = ImageColorAllocate($jpeg,$rand_ln_r[$rand],$rand_ln_g[$rand],$rand_ln_b[$rand]);

	ImageFilledRectangle($jpeg,0,0,200,200,$bg);

	if (!empty($_GET['font']))
	{
		$font = $_GET['font'];
	}
	else
	{

		$dir = opendir(FONT_DIR);
		while($file = readdir($dir))
		{

			if (eregi("\.ttf$",$file))
			{

			    $fonts[] = $file;

			}

		}

		$font = $fonts[rand(0,(count($fonts) - 1))];

	}

	//$box = ImageTTFBBox ($pt_size, 0, dirname(__FILE__) . "/ttfs/$font", $string);

	//die('c:\\ttfs\\$font');

	$box = ImageTTFBBox ($pt_size, 0, FONT_DIR.'/'.$font, $string);

	$font_width = $box[2] - $box[0];

	$font_height = $box[1] - $box[7];

	$tx_x = floor(($picWidth - $font_width) / 2) - 1;

	$tx_y = $picHeight - floor(($picHeight - $font_height) / 2);

	//ImageFTText($jpeg, $pt_size, 0, $tx_x, $tx_y, $tx, dirname(__FILE__) . "/ttfs/$font","$string",Array());
	ImageFTText($jpeg, $pt_size, 0, $tx_x, $tx_y, $tx, FONT_DIR.'/'.$font,$string,Array());
	//var_dump($string);die();

	$skip_lines_h = ceil($picHeight / rand(5,6));

	$skip_lines_v = ceil($picWidth / rand(18,20));

	$diag_lines = rand(0,2);


	// draw horizontal lines
	for($pt=0;$pt<$picWidth;$pt += $skip_lines_h)
	{

		ImageLine($jpeg,0,$pt,$picWidth,$pt,$ln);

	}

	// draw vertical lines
	for($pt=0;$pt<$picWidth;$pt += $skip_lines_v)
	{

		ImageLine($jpeg,$pt,0,$pt,$picWidth,$ln);

	}

	$skip_lines_d = @floor($skip_lines_v * 2);

	// draw diag lines right
	for($pt=@ceil(($skip_lines_d/2));$pt<$picWidth+($skip_lines_d*10);$pt += $skip_lines_d)
	{

		ImageLine($jpeg,$pt,0,($pt - ($skip_lines_d * 10)),$picWidth,$ln);

	}

	// draw diag lines left
	for($pt=@ceil(($skip_lines_d/2));$pt<$picWidth+($skip_lines_d*10);$pt += $skip_lines_d)
	{

		ImageLine($jpeg,($pt - ($skip_lines_d * 10)),0,$pt,$picWidth,$ln);

	}

	// finish last 2 sides - adding border with imageline
	ImageLine($jpeg,0,($picHeight - 1),($picWidth - 1),($picHeight - 1),$ln);

	ImageLine($jpeg,($picWidth - 1),0,($picWidth - 1),($picWidth - 1),$ln);

	ImageInterlace($jpeg,1);

	header("Content-type: image/jpeg");

	header("Content-Disposition: inline; filename=\"".md5(uniqid(rand(), true)).".jpg\"");

	ImageJPEG($jpeg);

	ImageDestroy($jpeg);

?>
