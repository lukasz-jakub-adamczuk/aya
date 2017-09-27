<?php

namespace Aya\Helper;

use Aya\Core\Debug;

class FigureManager {
    
    public static function image($params) {
        if (isset($params['file'])) {
            $fileName = $params['file'];
        } else {
            return false;
        }
        if (substr($fileName, 0, 7) == '/assets') {
            $fileName = WEB_DIR . $fileName;
        }

        if (file_exists($fileName)) {
            $hashName = md5($fileName);
        
            // $sOriginImage = $fileName;

            $fileExt = isset($params['ext']) ? $params['ext'] : 'jpg';

            $width = isset($params['width']) ? $params['width'] : 320;
            $height = isset($params['height']) ? $params['height'] : 180;
            $margins = isset($params['margin']) ? $params['margin'] : true;
            $cropX = isset($params['x']) ? $params['x'] : 'center';
            $cropY = isset($params['y']) ? $params['y'] : 'center';

            $output = isset($params['output']) ? $params['output'] : 'image';
            $margins = false; //really

            // old way
            if (isset($params['size'])) {
                $media = ['1920px'];
                $sizes[] = $params['size'];
                $aParts = explode('x', $params['size']);
                $width = (int)$aParts[0] ? $aParts[0] : 0;
                $height = (int)$aParts[1] ? $aParts[1] : 0;
            }

            if ($width === $height) {
                $className = 'ratio-1-1';
            }

            if (isset($params['ratio']) && !is_null($params['ratio'])) {
                if (strpos($params['ratio'], ':') !== false) {
                    $ratio = $params['ratio'];
                    $className = 'ratio-' . str_replace(':', '-', $params['ratio']);
                } else {
                    $className = $params['ratio'];
                }
            }

            if (!isset($ratio)) {
                $ratio = '16:9';
            }

            $className = isset($className) ? ' class="'.$className.'"' : '';

            // new way
            $media = ['360px', '768px', '1280px', '1920px'];
            if (isset($params['sizes'])) {
                $sizes = explode(',', $params['sizes']);
            } else {
                $ratioValues = explode(':', $ratio);

                foreach ($media as $width) {
                    $sizes[] = (int)$width . 'x' . round((int)$width * $ratioValues[1] / $ratioValues[0]);
                }
                // $sizes = explode(',', $params['sizes']);
            }
            
            

            $filePath = [];
            $fileDest = [];
            $haveToGenerate = false;
            foreach ($media as $mk => $dev) {
                $size = explode('x', $sizes[$mk]);
                $fileDest[$dev]['96dpi'] = $hashName.'-'.$size[0].'-'.$size[1].'.'.$fileExt;
                // $fileDest[$dev]['192dpi'] = $fileName.'-'.($size[0]*2).'-'.($size[1]*2).'.'.$fileExt;
                if (isset($params['asset'])) {
                    $filePath[$dev]['96dpi'] = WEB_DIR.'/tmp/'.$params['asset'].'/'.$fileDest[$dev]['96dpi'];
                    // $filePath[$dev]['192dpi'] = WEB_DIR.'/tmp/'.$params['asset'].'/'.$fileDest[$dev]['192dpi'];
                } else {
                    $filePath[$dev]['96dpi'] = WEB_DIR.'/tmp/'.$fileDest[$dev]['96dpi'];
                    // $filePath[$dev]['192dpi'] = WEB_DIR.'/tmp/'.$fileDest[$dev]['192dpi'];
                }
                if (!file_exists($filePath[$dev]['96dpi'])) {
                    $haveToGenerate |= true;
                }
                // if (!file_exists($filePath[$dev]['192dpi'])) {
                //     $haveToGenerate |= true;
                // }
            }

            // var_dump($haveToGenerate);

            if ($haveToGenerate) {
                // image generation
                $imageManipulator = new ImageManipulator();

                foreach ($media as $mk => $dev) {
                    $size = explode('x', $sizes[$mk]);
                    
                    $imageManipulator->loadImage($fileName);
                    $imageManipulator->resize($size[0], $size[1], $margins, $cropX, $cropY);
                    $imageManipulator->save($filePath[$dev]['96dpi']);

                    // $imageManipulator->loadImage($fileName);
                    // $imageManipulator->resize($size[0]*2, $size[1]*2, $margins, $cropX, $cropY);
                    // $imageManipulator->save($filePath[$dev]['192dpi']);
                }
            }

            $source = '';
            foreach (array_reverse($media) as $mk => $dev) {
                $source .= '<source class="lazyload" data-srcset="'.BASE_URL.'/tmp/'.$fileDest[$dev]['96dpi'].'" media="(min-width: '.$dev.')">';
            }
            // foreach (array_reverse($media) as $mk => $dev) {
            //     $source .= '<source srcset="'.BASE_URL.'/tmp/'.$fileDest[$dev]['192dpi'].'" media="(min-width: '.$dev.') and (min-resolution: 192dpi)">';
            // }

            $imageUrl = BASE_URL.'/tmp/'.$fileDest['1920px']['96dpi'];

            $figure = ''.
            // '<figure'.$className.'>'.
                '<picture>'.
                    $source.
                    '<img class="lazyload" data-src="'.$imageUrl.'" alt="">'.
                '</picture>'.
            // '</figure>'.
            '';

            if ($output === 'url') {
                return $imageUrl;
            } else {
                return $figure;
            }

            // $srcset = '';
            // foreach (array_reverse($media) as $mk => $dev) {
            //     $size = explode('x', $sizes[$mk]);

            //     $srcset[] = ''.BASE_URL.'/tmp/'.$fileDest[$dev]['96dpi'].' '.$size[0].'w';
            // }
            // $srcset = implode(', ', $srcset);


            // $figure = ''.
            //     '<img srcset="'.$srcset.'"'.
            //     // 'sizes="'..'"'.
            //     'src="'.BASE_URL.'/tmp/'.$fileDest['1080px']['96dpi'].'" alt="...">'.
            //     '';

            // $figure = ''.
            //     '<img srcset="'..'"'.
            //     // 'sizes="'..'"'.
            //     'src="'.BASE_URL.'/tmp/'.$fileDest['1080px']['96dpi'].'" alt="...">'
            //     '';

            // return $figure;
        }
    }
}
