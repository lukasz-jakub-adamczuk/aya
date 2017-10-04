<?php

namespace Aya\Helper;

class ImageManipulator {
    
    private $image;
    
    private $name;
    
    private $_sType;
    
    private $orientation;
    
    private $width;
    
    private $height;
    
    private $widthRatio;
    
    private $heightRatio;
    
    public function __construct() {
        
    }
    
    public function loadImage($sImage) {
        $this->name = $sImage;
        
        // checking image type
        if ($this->imgType($sImage) == "IMAGETYPE_JPEG") {
            $this->image = imagecreatefromjpeg($sImage);
        } elseif ($this->imgType($sImage) == "IMAGETYPE_GIF") {
            $this->image = imagecreatefromgif($sImage);
        } elseif ($this->imgType($sImage) == "IMAGETYPE_PNG") {
            $this->image = imagecreatefrompng($sImage);
        } else {
            die('Wrong filetype! Accepted images: JPG/JPEG, GIF, PNG');
        }
        
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        
        if ($this->width > $this->height) {
            $this->orientation = 'landscape';
        } elseif ($this->width < $this->height) {
            $this->orientation = 'portrait';
        } else {
            $this->orientation = 'square';
        }
    }

    public function getImageWidth() {
        return $this->width;
    }

    public function getImageHeight() {
        return $this->height;
    }
    
    public function resize($iWidth = 0, $iHeight = 0, $bMargin = true, $sHorCrop = 'center', $sVerCrop = 'center') {
        // check image orientation
        // echo $this->orientation;

        // images ratios
        $dSourceRatio = $this->width / $this->height;
        if ($iWidth && $iHeight) {
            $dDestRatio = $iWidth / $iHeight;
        } else {
            // known only width of expected image
            if ($iWidth) {
                $iHeight = (int)$iWidth * $this->height / $this->width;
            }
            // known only height of expected image
            if ($iHeight) {
                $iWidth = $iHeight * $this->width / $this->height;
            }
            // should know both sizes
            if ($iWidth && $iHeight) {
                $dDestRatio = $iWidth / $iHeight;
            } else {
                $dDestRatio = $this->width / $this->height;
            }
        }
        // echo 'dSourceRatio: '.$dSourceRatio.'<br>';
        // echo '$dDestRatio:  '.$dDestRatio.'<br>';
            
        if ($this->orientation == 'landscape') {
            if ($dSourceRatio > $dDestRatio) {
                $bMargin = 1;
            }

            if ($bMargin) {
                $this->heightRatio = ($this->height > $iHeight) ? $iHeight / $this->height : 1;
                $this->widthRatio = $this->heightRatio;
                $sMove = 'x';
            } else {
                $this->widthRatio = ($this->width > $iWidth) ? $iWidth / $this->width : 1;
                $this->heightRatio = $this->widthRatio;
                $sMove = 'y';
            }
        } elseif ($this->orientation == 'portrait') {
            if ($bMargin) {
                $this->heightRatio = ($this->height > $iHeight) ? $iHeight / $this->height : 1;
                $this->widthRatio = $this->heightRatio;
                $sMove = 'x';
            } else {
                $this->widthRatio = ($this->width > $iWidth) ? $iWidth / $this->width : 1;
                $this->heightRatio = $this->widthRatio;
                $sMove = 'y';
            }
        } else {
            // TODO check is it correct ?
            if ($iWidth > $iHeight) {
                if ($bMargin) {
                    $this->widthRatio = $this->heightRatio = $iHeight / $this->height;
                } else {
                    $this->widthRatio = $this->heightRatio = $iWidth / $this->width;
                }
                $sMove = 'x';
            } else {
                if ($bMargin) {
                    $this->widthRatio = $this->heightRatio = $iWidth / $this->width;
                } else {
                    $this->widthRatio = $this->heightRatio = $iHeight / $this->height;
                }
                $sMove = 'y';
            }
        }
        
        $iNewWidth = $this->width * $this->widthRatio;
        $iNewHeight = $this->height * $this->heightRatio;
        
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
        imagecopyresampled($rImage, $this->image, $sMoveWidth, $sMoveHeight, 0, 0, $iNewWidth, $iNewHeight, $this->width, $this->height);
        
        $this->name = dirname(__FILE__).'/image-'.$iWidth.'-'.$iHeight.'.jpg';
        $this->image = $rImage;
        
        //imagejpeg($rImage, $this->name);
    }
    
    public function show() {
        //return '<img src="'.basename($this->name).'" />';
        echo '<img src="'.basename($this->name).'" style="border: 1px solid #aaa; margin: 5px;" />';
    }
    
    public function debug() {
        echo '<pre>';
        echo 'img width :      '.$this->width."\n"
            .'img height:      '.$this->height."\n"
            .'img ratio width: '.$this->widthRatio."\n"
            .'img ratio height:'.$this->heightRatio."\n"
            .'</pre>';
    }
    
    public function save($sFile) {
        $sFile = isset($sFile) ? $sFile : $this->name;

        if (!file_exists(dirname($sFile))) {
            mkdir(dirname($sFile), 0777, true);
        }
        // TODO save to another file also
        imagejpeg($this->image, $sFile);
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
