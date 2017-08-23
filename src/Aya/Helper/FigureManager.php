<?php

namespace Aya\Helper;

class FigureManager {
    
    public static function image($params) {
        if (isset($params['file'])) {
            $sFileName = $params['file'];
        } else {
            return false;
        }
        if (substr($sFileName, 0, 7) == '/assets') {
            $sFileName = ASSETS_DIR . $sFileName;
        }

        // echo 'aaaa',$sFileName;

        if (file_exists($sFileName)) {
            $sHashName = md5($sFileName);
        
            $sOriginImage = $sFileName;

            $sFileExt = isset($params['ext']) ? $params['ext'] : 'jpg';

            $iWidth = isset($params['width']) ? $params['width'] : 320;
            $iHeight = isset($params['height']) ? $params['height'] : 180;
            $bMargin = isset($params['margin']) ? $params['margin'] : true;
            $sCropX = isset($params['x']) ? $params['x'] : 'center';
            $sCropY = isset($params['y']) ? $params['y'] : 'center';

            if (isset($params['size'])) {
                $aParts = explode('x', $params['size']);
                $iWidth = (int)$aParts[0] ? $aParts[0] : 0;
                $iHeight = (int)$aParts[1] ? $aParts[1] : 0;
            }

            if ($iWidth === $iHeight) {
                $sRatio = 'ratio-1-1';
            }

            if (isset($params['ratio'])) {
                $sRatio = $params['ratio'];
            }

            $sClass = isset($sRatio) ? ' class="'.$sRatio.'"' : '';
            
            $bMargin = false;

            $sFileHash = $sHashName;
            $sFileDest = $sFileHash.'-'.$iWidth.'-'.$iHeight.'.'.$sFileExt;

            $sFileRetinaDest = $sFileHash.'-'.($iWidth*2).'-'.($iHeight*2).'.'.$sFileExt;

            if (isset($params['asset'])) {
                $sFilePath = WEB_DIR.'/tmp/'.$params['asset'].'/'.$sFileDest;
                $sFileRetinaPath = WEB_DIR.'/tmp/'.$params['asset'].'/'.$sFileRetinaDest;
            } else {
                $sFilePath = WEB_DIR.'/tmp/'.$sFileDest;
                $sFileRetinaPath = WEB_DIR.'/tmp/'.$sFileRetinaDest;
            }

            if (!file_exists($sFilePath)) {
                // require_once dirname(ROOT_DIR).'/aya/src/Aya/Helper/ImageManipulator.php';
                
                // $oImageManipulator = new Aya\Helper\ImageManipulator();
                $oImageManipulator = new ImageManipulator();

                $oImageManipulator->loadImage($sFileName);
                $oImageManipulator->resize($iWidth, $iHeight, $bMargin, $sCropX, $sCropY);
                $oImageManipulator->save($sFilePath);

                // retina
                $oImageManipulator->resize($iWidth*2, $iHeight*2, $bMargin, $sCropX, $sCropY);
                $oImageManipulator->save($sFileRetinaPath);
            }

            return ''.
            '<figure'.$sClass.'>'.
                '<picture>'.
                    // '<source media="(min-resolution: 192dpi)" srcset="image.php?img='.$sFileName.'&size='.($iWidth*2).'x'.($iHeight*2).'&margin=&x=center&y=center 2x" class="spinner">'.
                    // '<img src="image.php?img='.$sFileName.'&size='.($iWidth*2).'x'.($iHeight*2).'&margin='.$bMargin.'&x='.$sCropX.'&y='.$sCropY.'" width="100%" class="spinner">'.
                    '<img src="./tmp/'.$sFileDest.'" width="100%" class="spinner">'.
                '</picture>'.
            '</figure>';
        }
    }
}
