<?php

namespace Aya\Helper;

class ImageManipulator {
    
    private $_rImage;
    
    private $_name;
    
    private $_sType;
    
    private $_sOrientation;
    
    private $_iWidth;
    
    private $_iHeight;
    
    private $_iWidthRatio;
    
    private $_iHeightRatio;
    
    public function __construct() {
        
    }
    
    public function loadImage($sImage) {
        $this->_name = $sImage;
        
        // checking image type
        if ($this->imgType($sImage) == "IMAGETYPE_JPEG") {
            $rImage = imagecreatefromjpeg($sImage);
        } elseif ($this->imgType($sImage) == "IMAGETYPE_GIF") {
            $rImage = imagecreatefromgif($sImage);
        } elseif ($this->imgType($sImage) == "IMAGETYPE_PNG") {
            $rImage = imagecreatefrompng($sImage);
        } else {
            die('Wrong filetype! Accepted images: JPG/JPEG, GIF, PNG');
        }
        
        $this->_rImage = $rImage;
        
        $this->_iWidth = imagesx($this->_rImage);
        $this->_iHeight = imagesy($this->_rImage);
        
        if ($this->_iWidth > $this->_iHeight) {
            $this->_sOrientation = 'landscape';
        } elseif ($this->_iWidth < $this->_iHeight) {
            $this->_sOrientation = 'portrait';
        } else {
            $this->_sOrientation = 'square';
        }
    }
    
    public function resize($iWidth = 0, $iHeight = 0, $bMargin = true, $sHorCrop = 'center', $sVerCrop = 'center') {
        // check image orientation
        // echo $this->_sOrientation;

        // images ratios
        $dSourceRatio = $this->_iWidth / $this->_iHeight;
        if ($iWidth && $iHeight) {
            $dDestRatio = $iWidth / $iHeight;
        } else {
            // known only width of expected image
            if ($iWidth) {
                $iHeight = (int)$iWidth * $this->_iHeight / $this->_iWidth;
            }
            // known only height of expected image
            if ($iHeight) {
                $iWidth = $iHeight * $this->_iWidth / $this->_iHeight;
            }
            // should know both sizes
            if ($iWidth && $iHeight) {
                $dDestRatio = $iWidth / $iHeight;
            } else {
                $dDestRatio = $this->_iWidth / $this->_iHeight;
            }
        }
        // echo 'dSourceRatio: '.$dSourceRatio.'<br>';
        // echo '$dDestRatio:  '.$dDestRatio.'<br>';
            
        if ($this->_sOrientation == 'landscape') {
            if ($dSourceRatio > $dDestRatio) {
                $bMargin = 1;
            }

            if ($bMargin) {
                $this->_iHeightRatio = ($this->_iHeight > $iHeight) ? $iHeight / $this->_iHeight : 1;
                $this->_iWidthRatio = $this->_iHeightRatio;
                $sMove = 'x';
            } else {
                $this->_iWidthRatio = ($this->_iWidth > $iWidth) ? $iWidth / $this->_iWidth : 1;
                $this->_iHeightRatio = $this->_iWidthRatio;
                $sMove = 'y';
            }
        } elseif ($this->_sOrientation == 'portrait') {
            if ($bMargin) {
                $this->_iHeightRatio = ($this->_iHeight > $iHeight) ? $iHeight / $this->_iHeight : 1;
                $this->_iWidthRatio = $this->_iHeightRatio;
                $sMove = 'x';
            } else {
                $this->_iWidthRatio = ($this->_iWidth > $iWidth) ? $iWidth / $this->_iWidth : 1;
                $this->_iHeightRatio = $this->_iWidthRatio;
                $sMove = 'y';
            }
        } else {
            // TODO check is it correct ?
            if ($iWidth > $iHeight) {
                if ($bMargin) {
                    $this->_iWidthRatio = $this->_iHeightRatio = $iHeight / $this->_iHeight;
                } else {
                    $this->_iWidthRatio = $this->_iHeightRatio = $iWidth / $this->_iWidth;
                }
                $sMove = 'x';
            } else {
                if ($bMargin) {
                    $this->_iWidthRatio = $this->_iHeightRatio = $iWidth / $this->_iWidth;
                } else {
                    $this->_iWidthRatio = $this->_iHeightRatio = $iHeight / $this->_iHeight;
                }
                $sMove = 'y';
            }
        }
        
        $iNewWidth = $this->_iWidth * $this->_iWidthRatio;
        $iNewHeight = $this->_iHeight * $this->_iHeightRatio;
        
//        echo '$iNewWidth'. $iNewWidth.'<br />';
//        echo '$iNewHeight'. $iNewHeight.'<br />';
        
        if ($sHorCrop == 'left') {
            $sMoveWidth = 0;
        } elseif ($sHorCrop == 'center') {
            $sMoveWidth = ($sMove == "x") ? ($iWidth - $iNewWidth) / 2 : 0;
        } elseif ($sHorCrop == 'right') {
            $sMoveWidth = ($sMove == "x") ? ($iWidth - $iNewWidth) : 0;
        } elseif (strpos($sHorCrop, '%') !== false) {
            $sMoveWidth = ($sMove == "x") ? ($iWidth - $iNewWidth) * ((int)str_replace('%', '', $sHorCrop) / 100) : 0;
        }

        if ($sVerCrop == 'top') {
            $sMoveHeight = 0;
        } elseif ($sVerCrop == 'center') {
            $sMoveHeight = ($sMove == "y") ? ($iHeight - $iNewHeight) / 2 : 0;
        } elseif ($sVerCrop == 'bottom') {
            $sMoveHeight = ($sMove == "y") ? ($iHeight - $iNewHeight) : 0;
        } elseif (strpos($sVerCrop, '%') !== false) {
            $sMoveHeight = ($sMove == "y") ? ($iHeight - $iNewHeight) * ((int)str_replace('%', '', $sVerCrop) / 100) : 0;
        }
                
        $rImage = imagecreatetruecolor($iWidth, $iHeight);
        $rBackground = imagecolorallocate($rImage, 255, 255, 255);
        
        imagefill($rImage, 0, 0, $rBackground);
        imagecopyresampled($rImage, $this->_rImage, $sMoveWidth, $sMoveHeight, 0, 0, $iNewWidth, $iNewHeight, $this->_iWidth, $this->_iHeight);
        
        $this->_name = dirname(__FILE__).'/image-'.$iWidth.'-'.$iHeight.'.jpg';
        $this->_rImage = $rImage;
        
        //imagejpeg($rImage, $this->_name);
    }
    
    public function show() {
        //return '<img src="'.basename($this->_name).'" />';
        echo '<img src="'.basename($this->_name).'" style="border: 1px solid #aaa; margin: 5px;" />';
    }
    
    public function debug() {
        echo '<pre>';
        echo 'img width :      '.$this->_iWidth."\n"
            .'img height:      '.$this->_iHeight."\n"
            .'img ratio width: '.$this->_iWidthRatio."\n"
            .'img ratio height:'.$this->_iHeightRatio."\n"
            .'</pre>';
    }
    
    public function save($sFile) {
        $sFile = isset($sFile) ? $sFile : $this->_name;

        if (!file_exists(dirname($sFile))) {
            mkdir(dirname($sFile), 0777, true);
        }
        // TODO save to another file also
        imagejpeg($this->_rImage, $sFile);
    }
    
    protected function _saveToFile($rImage, $sImageName) {
        // TODO better checking file type
        if ($this->imgType($sImageName) == "IMAGETYPE_JPEG") {
            imagejpeg($rImage, $sImageName, 80);
        } elseif ($this->imgType($sImageName) == "IMAGETYPE_GIF") {
            imagegif($rImage, $sImageName);
        } elseif ($this->imgType($sImageName) == "IMAGETYPE_PNG") {
            imagepng($rImage, $sImageName);
        }
    }
    
    public function imgType($sImageName)    {
        if (substr($sImageName, -4, 4) == '.jpg' || substr($sImageName, -4, 4) == 'jpeg') {
            return "IMAGETYPE_JPEG";
           } elseif (substr($sImageName, -4, 4) == '.gif') {
               return "IMAGETYPE_GIF";
           } elseif (substr($sImageName, -4, 4) == '.png') {
               return "IMAGETYPE_PNG";
           }
    }
}
