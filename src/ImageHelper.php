<?php

use Intervention\Image\ImageManagerStatic as Image;

class ImageHelper{
    private $image;
    private $quality;

    private $originalPath;
    private $path;
    private $extension;

    private $handler;
    private $operations = [];

    /** @return ImageHelper */
    public static function fromCMS($handler){
        return new ImageHelper(str_replace('/admin/', 'admin/', $handler->src()));
    }

    /** @return ImageHelper */
    public static function fromPath($path){
        return new ImageHelper($path);
    }

    public function __construct($path, $quality = 65){
        $this->quality = $quality;
        $this->originalPath = $path;
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
        $pathPieces = explode('/', rtrim($path, '/'));
        $this->path = substr(array_pop($pathPieces), 0, -strlen($this->extension)-1) . 'q' . $quality;
    }

    /** @return ImageHelper */
    public function resize($width, $height, $preventUpsize = true){
        if(!$width && !$height) throw new Exception("Either width or height has to be defined");

        $this->path .= '_resize' . ($width?$width:'a') . 'x' . ($height?$height:'a');
        $this->operations[] = function($im) use ($width, $height, $preventUpsize){
            return $im->resize($width, $height,  function ($constraint) use($preventUpsize) {
                $constraint->aspectRatio();
                if($preventUpsize) $constraint->upsize();
            });
        };
        return $this;
    }

    /** @return ImageHelper */
    public function grayscale() {
        $this->path .= '_grayscale';
        $this->operations[] = function($im) {
            return $im->greyscale();
        };
        return $this;
    }

    /** @return ImageHelper */
    public function multiply($r,$g,$b){
        $this->path .= '_multiply'.$r.'_'.$g.'_'.$b;
        $this->operations[] = function($im) use($r,$g,$b) {
            $srcPath = 'cache/tmp/'.uniqid().'.'.$this->extension;
            $im->save($this->pathToDir($srcPath));

            switch($this->extension) {
                case 'gif':
                    $src = imagecreatefromgif($this->pathToDir($srcPath));
                    break;
                case 'png':
                    $src = imagecreatefrompng($this->pathToDir($srcPath));
                    break;
                case 'jpg':
                case 'jpeg':
                    $src = imagecreatefromjpeg($this->pathToDir($srcPath));
                    break;
                default:
                    throw new Exception("Uknown extension type: " . $this->extension);
            }

            $filter_r=$r;
            $filter_g=$g;
            $filter_b=$b;
            $imagex = imagesx($src);
            $imagey = imagesy($src);
            for ($x = 0; $x <$imagex; ++$x) {
                for ($y = 0; $y <$imagey; ++$y) {
                    $rgb = imagecolorat($src, $x, $y);
                    $TabColors = imagecolorsforindex ( $src , $rgb );
                    $color_r = floor($TabColors['red']*$filter_r/255);
                    $color_g = floor($TabColors['green']*$filter_g/255);
                    $color_b = floor($TabColors['blue']*$filter_b/255);
                    $newcol = imagecolorallocate($src, $color_r,$color_g,$color_b);
                    imagesetpixel($src, $x, $y, $newcol);
                }
            }
            return Image::make($src);
        };
        return $this;
    }

    public function size(){
        return null;
    }

    public function src(){
        $finalPath = 'cache/images/' . $this->path . '.' . $this->extension;
        $filePath = $this->pathToDir($finalPath);


        if(!file_exists($filePath)){
            //If not in cache -> apply operations
            $this->image = Image::make($this->pathToDir($this->originalPath));
            foreach($this->operations as $operation){
                $this->image = $operation($this->image);
            }
            $this->image->save($filePath);
        }

        return '/' . $finalPath;
    }

    private function pathToDir($path){
        return getcwd() . '/' . $path;
    }

    public function __toString(){
        return $this->src();
    }
}