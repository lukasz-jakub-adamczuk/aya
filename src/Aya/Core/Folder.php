<?php

namespace Aya\Core;

class Folder {
    
    public static function getContent($sPath, $bUseFileInfo = false, $aExclude = []) {
        if (file_exists($sPath)) {
            return self::_getDirectoryContent($sPath, $bUseFileInfo, $aExclude);
        }
    }

    public static function makeDirectory($basePath, $fragmentPath) {
        $completePath = $basePath . $fragmentPath;
        // create directory if does not exists
        if (!file_exists($completePath)) {
            if (mkdir($completePath, 0755, true)) {
                // make each directory is writable
                $parts = explode('/', $fragmentPath);
                $tmpPath = $basePath;
                foreach ($parts as $dir) {
                    if ($dir) {
                        $tmpPath .= '/' . $dir;
                    }
                    chmod($tmpPath, 0755);	
                }
            }
        }
    }

    private static function _getDirectoryContent($path, $finfo = false, $aExclude = []) {
        // use finfo for files
        if ($finfo && function_exists('finfo_open')) {
            $finfo = finfo_open();
        }

        if ($handle = opendir($path)) {
            $aContent = [];
            while (($file = readdir($handle)) !== false) {
                // no file, no . in name
                $aInfo = [];
                if (!is_file($file) && strpos($file, '.') == false) {
                    if ($file != '.') {
                        if (!in_array($file, $aExclude)) {
                            $aInfo['name'] = $file;

                            $aContent['dirs'][] = $aInfo;
                        }
                    }
                } else {
                    if (!in_array($file, $aExclude)) {
                        if ($finfo && function_exists('finfo_file')) {
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
                }
                // if (is_file($file)) {
                //     $aContent['files'][] = $file;
                // } else {
                //     if ($file != '.') {
                //         $aContent['dirs'][] = $file;
                //     }
                // }
                $aContent['all'][] = array('name' => $file);
            }
            closedir($handle);

            if (isset($aContent['dirs'])) {
                asort($aContent['dirs']);    
            }
            if (isset($aContent['files'])) {
                asort($aContent['files']);    
            }

            return $aContent;
        }
        return false;
    }
}