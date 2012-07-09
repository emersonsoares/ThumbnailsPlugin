##Thumbnails helper for cakephp 2.x

Helper to generate thumbnail images dynamically by saving them to the cache.
Alternative to phpthumb.

Inspired in http://net.tutsplus.com/tutorials/php/image-resizing-made-easy-with-php/
 
usage:
-----

``` PHP
echo $this->Thumbnail->render('teste.jpg', array(
        'path' => '',
        'width' => '100',
        'height' => '100',
        'resizeOption' => 'portrait',
        'quality' => '100'
            ), array('id' => 'img-test', 'alt' => 'thumbnail test'));
```
Resize options: exact, portrait, landscape, auto, crop;
