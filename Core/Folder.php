<?php

class Folder {
	
	public static function getContent($sPath, $bUseFileInfo = false) {
		if (file_exists($sPath)) {
			return self::_getDirectoryContent($sPath, $bUseFileInfo);
		}
	}

	private static function _getDirectoryContent($path, $finfo = false) {
		// use finfo for files
		if ($finfo) {
			$finfo = finfo_open();
		}

		if ($handle = opendir($path)) {
			while (($file = readdir($handle)) !== false) {
				// no file, no . in name
				$aInfo = array();
				if (!is_file($file) && strpos($file, '.') == false) {
					if ($file != '.') {
						$aInfo['name'] = $file;

						$aContent['dirs'][] = $aInfo;
					}
				} else {
					if ($finfo) {
						$sFileInfo = finfo_file($finfo, $path.'/'.$file);

						if (strpos($sFileInfo, 'image data') !== false) {
							$aInfo['type'] = 'image';
						} elseif (strpos($sFileInfo, 'MPEG v4') !== false) {
							$aInfo['type'] = 'video';
						} elseif (strpos($sFileInfo, 'Audio file') !== false) {
							$aInfo['type'] = 'audio';
						} else {
							$aInfo['type'] = 'data';
						}
					}

					$aInfo['name'] = $file;

					$aContent['files'][] = $aInfo;
				}
				// if (is_file($file)) {
				// 	$aContent['files'][] = $file;
				// } else {
				// 	if ($file != '.') {
				// 		$aContent['dirs'][] = $file;
				// 	}
				// }
				$aContent['all'] = $file;
			}
			closedir($handle);

			return $aContent;
		}
		return false;
	}
}