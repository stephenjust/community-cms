<?php
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	define('SECURITY',1);
	define('ROOT','../../');
	include(ROOT.'include.php');
	$referer = $_SERVER['HTTP_REFERER'];
	if(ereg('/$',$referer)) {
		$referer .= 'index';
		}
	$referer_directory = dirname($referer);
	if($referer_directory == "") {
		die('Security breach.');
		}
	$current_directory = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
	if($current_directory == $referer_directory.'/admin/scripts') {
		$file_path = ROOT.'content_blocks/blocks.info';
		$file_handle = fopen($file_path,'r');
		$file = fread($file_handle,filesize($file_path));
		fclose($file_handle);
		$block_entries = explode("\n",$file);
		$num_entries = count($block_entries);
		$i = 1;
		while ($i <= $num_entries) {
			$entry = explode('#',$block_entries[$i - 1]);
			if($entry[0] == $_GET['blocktype']) {
				$attributes = explode('&',$entry[1]);
				$num_attributes = count($attributes);
				$j = 1;
				if ($num_attributes == 0) {
					echo '<input type="hidden" name="attributes" value="" />';
					echo 'No options.';
					}
				$allattributes = NULL;
				while ($j <= $num_attributes) {
					$atb = explode('=',$attributes[$j - 1]);
					echo $atb[0].'=';
					if ($atb[1] == 'int') {
						echo '<input type="text" maxlength="9" size="3" name="'.$atb[0].'" /><br />';
						}
					$allattributes .= $atb[0];
					if ($j != $num_attributes) {
						$allattributes .= ',';
						}
					$j++;
					}
				echo '<input type="hidden" name="attributes" value="'.$allattributes.'" />';
				}
			$i++;
			}
		} else {
		die('Security breach.');
		}
?>