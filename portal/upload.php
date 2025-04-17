<?php
require('build.php');
// error_reporting(E_ALL);

// Make sure file is not cached (as it happens for example on iOS devices)
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
// Support CORS
header("Access-Control-Allow-Origin: *");
// other CORS headers if any...
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	exit; // finish preflight CORS requests here
}
*/

// 5 minutes execution time
@set_time_limit(5 * 60);

// Uncomment this one to fake upload time
 usleep(5000);

// Settings
$targetDir = PB . UP . 'tmp/';

$cleanupTargetDir = true; // Remove old files
$maxFileAge = 5 * 3600; // Temp file age in seconds

// Create target dir
if (!file_exists($targetDir)) {
	@mkdir($targetDir, 0777);
}
$sl->nocomment();
// Get a file name
/*
if (isset($_REQUEST["name"])) {
	$fileName = $_REQUEST["name"];
} elseif (!empty($_FILES)) {
	$fileName = $_FILES["file"]["name"];
} else {
	$fileName = uniqid("file_");
}
*/
$fileName = uniqid("file_");
#$fileName = $_FILES["file"]["name"];

$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

// Chunking might be enabled
$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


// Remove old temp files
if ($cleanupTargetDir) {
	if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
	}

	while (($file = readdir($dir)) !== false) {
		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

		// If temp file is current file proceed to the next
		if ($tmpfilePath == "{$filePath}.part") {
			continue;
		}

		// Remove temp file if it is older than the max age and is not the current file
		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
			@unlink($tmpfilePath);
		}
	}
	closedir($dir);
}


// Open temp file
if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
	die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
}

if (!empty($_FILES)) {
	if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
	}

	// Read binary input stream and append it to temp file
	if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
	}
} else {
	if (!$in = @fopen("php://input", "rb")) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
	}
}

while ($buff = fread($in, 4096)) {
	fwrite($out, $buff);
}

@fclose($out);
@fclose($in);

// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
	if (isset($_REQUEST["name"])) {
		$fileName = $_REQUEST["name"];
	} elseif (!empty($_FILES)) {
		$fileName = $_FILES["file"]["name"];
	}
  if(@$sl->post['filename'] == 'original')
    $fileName = $_FILES["file"]["name"];

	// error_reporting(E_ALL);
	// Strip the temp .part suffix off
	rename("{$filePath}.part", $filePath);

  /*
	require(PB. '/includes/vendor/verot/class.upload.php/src/class.upload.php');
	require(PB. '/includes/vendor/verot/class.upload.php/src/lang/class.upload.tr_TR.php');
  */

	$ext = strtolower($sl->GetExtension($_FILES["file"]["name"]));

	if(!empty($get['name']))
		$fileName = $get['name']. '-' .time();

	$target = UP. 'images/'. date('Y-m-d').'/';

	if (!file_exists($targetDir)) {
		@mkdir($targetDir, 0777);
	}

	$settings = array(
		'width' =>  '4000',
		'height' =>  '4000',
		'crop' =>  '0',
		'quality' =>  '80',
		'convert' =>  'jpg',
		'type' => '',
    'resize' => []
	);
	$settings = array_merge($settings, $sl->post);

	if($ext == 'pdf' AND @$get['pdf_convert'] == '1') {
		$_filePath = str_replace('pdf','jpg',$filePath);

		$pdf = new Spatie\PdfToImage\Pdf($filePath);
		$pdf->setPage(1)
				->saveImage($_filePath);
				unlink($filePath);
				$filePath = $_filePath;
	}

	switch (@$sl->post['type']) {
    case 'just-response':
      $json['file'] = str_replace(PB, '', $filePath);
			$json['jsonrpc'] = '2.0';

			#unlink($filePath);
			echo json_encode($json);
      exit;
    break;
		default:
			if(!is_file($filePath)) {

			}

			$handle = new \Verot\Upload\Upload($filePath, $sl->languages('code'), 'tr_TR');

			if(!$handle->uploaded) {
				$json['file'] = '';
				$json['jsonrpc'] = '2.0';
				$json['status'] = 'error';
				$json['message'] = 'Dosya kayıt sorunu. Hata detayı:'.$handle->error;
				echo json_encode($json);
				exit;
			}

			$handle->file_auto_rename				= true;
			$handle->file_safe_name					= true;
			$handle->file_new_name_body			= $sl->createslug($sl->RemoveExtension($fileName));
			$handle->file_overwrite					= false;

      /*
			$handle->forbidden = array('application/*');
			$handle->allowed = array(
				'application/pdf',
				'application/mp4',
				'application/msword',
				'application/msexcel',
				'application/mspowerpoint',
				'image/*'
			);
			*/

			//$handle->image_convert					= $settings['convert'];
			$handle->file_new_name_ext			= $ext;
			$handle->image_ratio						= true;

			$handle->image_watermark				= false;

			$handle->image_resize						= false;
			$handle->image_ratio_fill				= false;

			if($settings['crop'] == '1')
				$handle->image_ratio_crop      	= true;
			else
				$handle->image_ratio_crop      	= false;

			$handle->image_y								= $settings['height'];
			$handle->image_x								= $settings['width'];
			$handle->jpeg_quality						= $settings['quality'];
			$handle->image_no_enlarging			= true;
      $handle->image_ratio_no_zoom_in = true;

			$handle->process(PB. $target);
      $images = array();

			if ($handle->processed) {
				$images['default'] = $target . $handle->file_dst_name;
				$json['status'] = 'ok';
				$json['message'] = '';

        $resize = $settings['resize'];

        if(!empty($resize)) {
          for($i=0;$i<sizeof($resize);$i++) {
            $handle->image_ratio_crop      	= true;
            $handle->image_ratio_fill				= true;
            $handle->image_resize						= true;
            $handle->image_y								= $resize[$i]['height'];
      			$handle->image_x								= $resize[$i]['width'];
      			$handle->jpeg_quality						= $resize[$i]['quality'];
      			$handle->image_no_enlarging			= true;
            $handle->image_ratio_no_zoom_in = true;

      			$handle->process(PB. $target);

      			if ($handle->processed) {
      				$images['sizes'][$resize[$i]['width'] . 'x' . $resize[$i]['height']] = $target . $handle->file_dst_name;
      			}
          }
        }
			} else {
				$json['status'] = 'error';
				$json['message'] = 'Dosya kayıt sorunu 2. Hata detayı: '.$handle->error;
			}

      if($json['status'] == 'ok') {
        switch (@$sl->post['type']) {
          case 'images':
            $sl->db->Query("INSERT INTO
              `images` VALUES
              (
                NUll,
                '".serialize($images)."',
                '0',
                '{$sl->post['group']}',
                '".time()."',
                '{$sl->post['id']}',
                '',
                '{$sl->post['lang']}',
                '{$sl->post['uniq_']}',
                '{$sl->post['slug']}',
                '0'
              )
            ");

            $json['id'] = $sl->db->GetLastInsertID();
            $file = $images['default'];
          break;

          default:
            $file = $images['default'];
          break;
        }
      }

      $json['file'] = $file;
			$json['jsonrpc'] = '2.0';

			unlink($filePath);
			echo json_encode($json);
			exit();
		break;
	}

/*
	$json['file'] = $image;
	$json['jsonrpc'] = '2.0';
	$json['result'] = null;
	echo json_encode($json);

	$_large = $sl->VerifyFileName(str_replace('tmp','galleries',$large));
	$_medium = $sl->VerifyFileName(str_replace('tmp','galleries',$medium));
	$_thumb = $sl->VerifyFileName(str_replace('tmp','galleries',$thumb));

	rename(PB.$large,PB.$_large);
	rename(PB.$medium,PB.$_medium);
	rename(PB.$thumb,PB.$_thumb);

	$galleries = array('large' => $_large, 'thumb' => $_thumb, 'medium' => $_medium, 'album' => $get['album']);

	$values = serialize($galleries);
	$db->Query("INSERT INTO `pages_additional` (`id`,`page_id`,`type`,`value`) VALUES (NULL, '".$get['id']."','1','".$values."')");
	$json['id'] = $db->GetLastInsertID();
*/
}

// Return Success JSON-RPC response
die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
