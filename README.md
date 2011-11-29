THUMBNAILSHELPER FOR CAKEPHP 2.0
================================
Helper to generate thumbnail images dynamically by saving them to the cache.
Alternative to phpthumb.

Inspired in http://net.tutsplus.com/tutorials/php/image-resizing-made-easy-with-php/
 
usage:
-----

``` php
echo $this->Html->image($this->Thumbnail->render($myvar['Model']['picture'], array(
                        'path' => 'uploads/Model/picture',
                        'newWidth' => '250',
                        'newHeight' => '150',
                        'resizeOption' => 'portrait',
                        'quality' => '100'
                            )
                    ));
```
Resize options: exact, portrait, landscape, auto, crop;