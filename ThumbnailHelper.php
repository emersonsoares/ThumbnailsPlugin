<?php

/**
 * @author Emerson Soares (dev.emerson@gmail.com)
 * @filesource https://github.com/emersonsoares/ThumbnailsHelper-for-CakePHP
 */
class ThumbnailHelper extends AppHelper {

    private $absoluteCachePath = '';
    private $cachePath = '';
    private $newWidth = 150;
    private $newHeight = 225;
    private $quality = 80;
    private $path = '';
    private $srcImage = '';

    public function render($image, $params) {
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

        if (isset($params['cachePath'])) {
            $this->cachePath = $params['cachePath'] . DS . $this->newWidth . 'x' . $this->newHeight . DS . $this->quality;
        } else {
            $this->cachePath = 'cache' . DS . $this->newWidth . 'x' . $this->newHeight . DS . $this->quality;
        }
        
        $this->srcImage = $image;

        if (file_exists($this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage)) {
            return $this->cachePath . DS . $this->srcImage;
        } else {
            return $this->cropedImage();
        }
    }

    private function cropedImage() {
        return $this->saveImgCache();
    }

    private function saveImgCache() {
        $filename = $this->absoluteCachePath . DS . $this->path . DS . $this->srcImage;

        if (!file_exists($this->absoluteCachePath . DS . $this->cachePath))
            mkdir($this->absoluteCachePath . DS . $this->cachePath, 0777, true);

        list($width, $heigth) = getimagesize($filename);

        $imageTmp = imagecreatetruecolor($this->newWidth, $this->newHeight);

        $extImg = strtolower(substr(strrchr($filename, '.'), 1));

        switch ($extImg) {
            case 'jpg':
                $srcImage = imagecreatefromjpeg($filename) or notfound();

                imagecopyresampled($imageTmp, $srcImage, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $width, $heigth);

                if (imagejpeg($imageTmp, $this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage, $this->quality)) {
                    imagedestroy($imageTmp);
                    return $this->cachePath . DS . $this->srcImage;
                }
                break;
            case 'png':
                $srcImage = imagecreatefrompng($filename) or notfound();

                imagecopyresampled($imageTmp, $srcImage, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $width, $heigth);

                if (imagepng($imageTmp, $this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage, $this->quality)) {
                    imagedestroy($imageTmp);
                    return $this->cachePath . DS . $this->srcImage;
                }
                break;
            case 'gif':
                $srcImage = imagecreatefromgif($filename) or notfound();

                imagecopyresampled($imageTmp, $srcImage, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $width, $heigth);

                if (imagegif($imageTmp, $this->absoluteCachePath . DS . $this->cachePath . DS . $this->srcImage, $this->quality)) {
                    imagedestroy($imageTmp);
                    return $this->cachePath . DS . $this->srcImage;
                }
                break;
            default:
                notfound();
                break;
        }
    }

}

?>
