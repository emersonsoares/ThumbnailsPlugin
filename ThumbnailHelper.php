<?php

/**
 * 
 *
 * @author Emerson Soares (dev.emerson@gmail.com)
 */
class ThumbnailHelper extends AppHelper {

    private $absoluteCachePath = '';
    private $cachePath = '';
    private $newWidth = 150;
    private $newHeight = 225;
    private $quality = 80;
    private $path = '';

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

        if (file_exists($this->absoluteCachePath . DS . $this->cachePath . DS . $image)) {
            return $this->cachePath . DS . $image;
        } else {
            return $this->createThumb($image);
        }
    }

    private function createThumb($image) {
        $filename = $this->absoluteCachePath . DS . $this->path . DS . $image;

        if (!file_exists($this->absoluteCachePath . DS . $this->cachePath))
            mkdir($this->absoluteCachePath . DS . $this->cachePath, 0777, true);

        list($width, $heigth) = getimagesize($filename);

        $imageTmp = imagecreatetruecolor($this->newWidth, $this->newHeight);

        $extImg = strtolower(substr(strrchr($filename, '.'), 1));

        switch ($extImg) {
            case 'jpg':
                $srcImage = imagecreatefromjpeg($filename) or notfound();

                imagecopyresampled($imageTmp, $srcImage, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $width, $heigth);

                if (imagejpeg($imageTmp, $this->absoluteCachePath . DS . $this->cachePath . DS . $image, $this->quality)) {
                    imagedestroy($imageTmp);
                    return $this->cachePath . DS . $image;
                }
                break;
            case 'png':
                $srcImage = imagecreatefrompng($filename) or notfound();

                imagecopyresampled($imageTmp, $srcImage, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $width, $heigth);

                if (imagepng($imageTmp, $this->absoluteCachePath . DS . $this->cachePath . DS . $image, $this->quality)) {
                    imagedestroy($imageTmp);
                    return $this->cachePath . DS . $image;
                }
                break;
            case 'gif':
                $srcImage = imagecreatefromgif($filename) or notfound();

                imagecopyresampled($imageTmp, $srcImage, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $width, $heigth);

                if (imagegif($imageTmp, $this->absoluteCachePath . DS . $this->cachePath . DS . $image, $this->quality)) {
                    imagedestroy($imageTmp);
                    return $this->cachePath . DS . $image;
                }
                break;
            default:
                notfound();
                break;
        }
    }

}

?>
