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
class ThumbnailHelper extends AppHelper {

    private $absoluteCachePath = '';
    private $cachePath = '';
    private $newWidth = 150;
    private $newHeight = 225;
    private $srcWidth;
    private $srcHeight;
    private $quality = 80;
    private $path = 'uploads/images/';
    private $srcImage = '';
    private $resizeOption = 'auto';
    private $openedImage = '';
    private $imageResized = '';

    public function render($image, $params) {
        $this->setup($image, $params);

        if (file_exists($this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage)) {
            return $this->openCachedImage();
        } else if ($this->openSrcImage()) {
            $this->resizeImage();
            $this->saveImgCache();
            return $this->cachePath . DS . $this->srcImage;
        }
    }

    private function setup($image, $params) {
        if (isset($params['path'])) {
            $this->path = $params['path'] . DS;
        }

        if (isset($params['newWidth'])) {
            $this->newWidth = $params['newWidth'];
        }

        if (isset($params['newHeight'])) {
            $this->newHeight = $params['newHeight'];
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
        if (file_exists($this->absoluteCachePath . DS . $this->path . DS . $this->srcImage)) {
            list($width, $heigth) = getimagesize($this->absoluteCachePath . DS . $this->path . DS . $this->srcImage);

            $this->srcWidth = $width;
            $this->srcHeight = $heigth;

            $this->openedImage = $this->openImage($this->absoluteCachePath . DS . $this->path . DS . $this->srcImage);
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
                $scaleQuality = round(($imageQuality / 100) * 9);

                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                    imagepng($this->imageResized, $savePath, $invertScaleQuality);
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

        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);

        imagecopyresampled($this->imageResized, $this->openedImage, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->srcWidth, $this->srcHeight);

        if ($this->resizeOption == 'crop') {
            $this->crop($optimalWidth, $optimalHeight);
        }
    }

    private function crop($optimalWidth, $optimalHeight) {

        $cropStartX = ( $optimalWidth / 2) - ( $this->newWidth / 2 );
        $cropStartY = ( $optimalHeight / 2) - ( $this->newHeight / 2 );

        $crop = $this->imageResized;
        $this->imageResized = imagecreatetruecolor($this->newWidth, $this->newHeight);
        imagecopyresampled($this->imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $this->newWidth, $this->newHeight, $this->newWidth, $this->newHeight);
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
