<?php
/**
 * Helper to generate thumbnail images dynamically by saving them to the cache.
 * Alternative to phpthumb.
 * 
 * Inspired in http://net.tutsplus.com/tutorials/php/image-resizing-made-easy-with-php/
 * 
 * @author Emerson Soares (dev.emerson@gmail.com)
 * @filesource https://github.com/emersonsoares/ThumbnailsHelper-for-CakePHP
 */
class ThumbnailHelper extends HtmlHelper {

    private $absoluteCachePath = '';
    private $cachePath = '';
    private $newWidth = 150;
    private $newHeight = 225;
    private $srcWidth;
    private $srcHeight;
    private $quality = 80;
    private $path = '';
    private $srcImage = '';
    private $resizeOption = 'auto';
    private $openedImage = '';
    private $imageResized = '';

    /**
     *
     * @param string $image Caminho da imagem no servidor
     * @param array $params Parametros de configuração do Thumbnail
     * @param array $options Parametros de configuração da tag <img/>
     * @return string Retorna uma tag imagem, configurada de acordo com os parametros recebidos. 
     */
    public function render($image, $params, $options = null) {
        $this->setup($image, $params);

        if (file_exists($this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage)) {
            return $this->image($this->openCachedImage(), $options);
        } else if ($this->openSrcImage()) {
            $this->resizeImage();
            $this->saveImgCache();
            return $this->image($this->cachePath . DS . $this->srcImage, $options);
        }
    }

    private function setup($image, $params) {
        if (isset($params['path'])) {
            $this->path = $params['path'] . DS;
        }

        if (isset($params['width'])) {
            $this->newWidth = $params['width'];
        }

        if (isset($params['height'])) {
            $this->newHeight = $params['height'];
        }

        if (isset($params['quality'])) {
            $this->quality = $params['quality'];
        }

        if (isset($params['absoluteCachePath'])) {
            $this->absoluteCachePath = $params['absoluteCachePath'];
        } else {
            $this->absoluteCachePath = WWW_ROOT . 'img';
        }

        if (isset($params['resizeOption'])) {
            $this->resizeOption = strtolower($params['resizeOption']);
        }

        if (isset($params['cachePath'])) {
            $this->cachePath = $params['cachePath'] . DS . $this->newWidth . 'x' . $this->newHeight . DS . $this->quality . DS . $this->resizeOption;
        } else {
            $this->cachePath = 'cache' . DS . $this->newWidth . 'x' . $this->newHeight . DS . $this->quality . DS . $this->resizeOption;
        }

        $this->srcImage = $image;
    }

    private function openCachedImage() {
        return $this->cachePath . DS . $this->srcImage;
    }

    private function openSrcImage() {
      $image_path = $this->absoluteCachePath . DS . $this->path . DS . $this->srcImage;
      if (file_exists($image_path)) {

          list($width, $heigth) = getimagesize($image_path);

          $this->srcWidth = $width;
          $this->srcHeight = $heigth;

          $this->openedImage = $this->openImage($image_path);

          return true;
      } else {
          return false;
      }
    }

    private function saveImgCache() {
        $extension = strtolower(strrchr($this->absoluteCachePath . DS . $this->path . DS . $this->srcImage, '.'));

        if (!file_exists($this->absoluteCachePath . DS . $this->cachePath))
            mkdir($this->absoluteCachePath . DS . $this->cachePath, 0777, true);

        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->imageResized, $this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage, $this->quality);
                }
                break;

            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->imageResized, $this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage);
                }
                break;
            case '.png':
                $scaleQuality = round(($this->quality / 100) * 9);

                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                    imagepng($this->imageResized, $this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage, $invertScaleQuality);
                }
                
                break;
            default:
                break;
        }
        imagedestroy($this->imageResized);
    }

    private function resizeImage() {
        $options = $this->getDimensions();

        $optimalWidth = $options['optimalWidth'];
        $optimalHeight = $options['optimalHeight'];

        if($optimalWidth > $this->srcWidth)
        {
            $optimalWidth = $this->srcWidth;
        }

        if($optimalHeight > $this->srcHeight)
        {
            $optimalHeight = $this->srcHeight;
        }

        // generate new w/h if not provided
        if($optimalWidth && !$optimalHeight)
        {
            $optimalHeight = $this->srcHeight * ($optimalHeight / $this->srcWidth);
        }
        elseif($optimalHeight && !$optimalWidth)
        {
            $optimalWidth = $this->srcWidth * ($optimalHeight / $this->srcHeight);
        }
        elseif(!$optimalWidth && !$optimalHeight)
        {
            $optimalWidth = $this->srcWidth;
            $optimalHeight = $this->srcHeight;
        }

        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
       
        $info = getimagesize($this->absoluteCachePath . DS . $this->path . DS . $this->srcImage);
        
        if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
          $trnprt_indx = imagecolortransparent($this->openedImage);

          // If we have a specific transparent color
          if ($trnprt_indx >= 0) {

            // Get the original image's transparent color's RGB values
            $trnprt_color    = imagecolorsforindex($this->openedImage, $trnprt_indx);

            // Allocate the same color in the new image resource
            $trnprt_indx    = imagecolorallocate($this->imageResized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

            // Completely fill the background of the new image with allocated color.
            imagefill($this->imageResized, 0, 0, $trnprt_indx);

            // Set the background color for new image to transparent
            imagecolortransparent($this->imageResized, $trnprt_indx);


          }
          // Always make a transparent background color for PNGs that don't have one allocated already
          elseif ($info[2] == IMAGETYPE_PNG) {

            // Turn off transparency blending (temporarily)
            imagealphablending($this->imageResized, false);

            // Create a new transparent color for image
            $color = imagecolorallocatealpha($this->imageResized, 0, 0, 0, 127);

            // Completely fill the background of the new image with allocated color.
            imagefill($this->imageResized, 0, 0, $color);

            // Restore transparency blending
            imagesavealpha($this->imageResized, true);
          }
        }
            
        imagecopyresampled($this->imageResized, $this->openedImage, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->srcWidth, $this->srcHeight);

        if ($this->resizeOption == 'crop') {
            $this->crop($optimalWidth, $optimalHeight);
        }
    }

    private function crop($optimalWidth, $optimalHeight) {

        $cropStartX = ( $optimalWidth / 2) - ( $this->newWidth / 2 );
        $cropStartY = ( $optimalHeight / 2) - ( $this->newHeight / 2 );

        $crop = $this->imageResized;
        $this->imageResized = @imagecreatetruecolor($this->newWidth, $this->newHeight);
        @imagecopyresampled($this->imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $this->newWidth, $this->newHeight, $this->newWidth, $this->newHeight);
    }

    private function openImage($file) {
        $extension = strtolower(strrchr($file, '.'));

        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                $img = imagecreatefromjpeg($file);
                break;
            case '.gif':
                $img = imagecreatefromgif($file);
                $transparent_index = imagecolortransparent($img);
                break;
            case '.png':
                $img = imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }
        return $img;
    }

    private function getDimensions() {

        switch ($this->resizeOption) {
            case 'exact':
                $optimalWidth = $this->newWidth;
                $optimalHeight = $this->newHeight;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($this->newHeight);
                $optimalHeight = $this->newHeight;
                break;
            case 'landscape':
                $optimalWidth = $this->newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($this->newWidth);
                break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($this->newWidth, $this->newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($this->newWidth, $this->newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    private function getSizeByFixedHeight($newHeight) {
        $ratio = $this->srcWidth / $this->srcHeight;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    private function getSizeByFixedWidth($newWidth) {
        $ratio = $this->srcHeight / $this->srcWidth;
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    private function getSizeByAuto($newWidth, $newHeight) {
        if ($this->srcHeight < $this->srcWidth) {
            $optimalWidth = $newWidth;
            $optimalHeight = $this->getSizeByFixedWidth($newWidth);
        } elseif ($this->srcHeight > $this->srcWidth) {
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight = $newHeight;
        } else {
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            } else {
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            }
        }

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    private function getOptimalCrop($newWidth, $newHeight) {

        $heightRatio = $this->srcHeight / $newHeight;
        $widthRatio = $this->srcWidth / $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->srcHeight / $optimalRatio;
        $optimalWidth = $this->srcWidth / $optimalRatio;

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

}
?>
