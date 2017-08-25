<?php // arrayobject.spec.php

use Codeurs\ImageHelper;
use Peridot\Leo\Interfaces\Assert;

ImageHelper::setCacheFolder('tests/cache/');
array_map('unlink', glob('tests/cache/*.*'));

describe('resize thor 100x100 png', function() {
    beforeEach(function() {
        $this->helper = createHelper('thor100.png');
    });

    describe('->resize(50,50)', function() {
        it('should create a new 50x50 png', function() {
            $src = $this->helper->resize(50,50)->src();
            assertImage($src, 50, 50);
        });
    });

    describe('->resize(150,150)', function() {
        it('should create a new 100x100 png', function() {
            $src = $this->helper->resize(150,150)->src();
            assertImage($src, 100, 100);
        });
    });

    describe('->resize(150,150, false) - not preventing upscale', function() {
        it('should create a new 150x150 png', function() {
            $src = $this->helper->resize(150,150,false)->src();
            assertImage($src, 150, 150);
        });
    });

    describe('->resize(75,50)', function() {
        it('should create a new 50x50 png', function() {
            $src = $this->helper->resize(75,50)->src();
            assertImage($src, 50, 50);
        });
    });
});


function assertImage($src, $width, $height){
    $assert = new Assert();
    $assert->isTrue(file_exists($src), $src . ' should exist');
    $size = getimagesize($src);
    $assert->equal($size[0], $width);
    $assert->equal($size[1], $height);
}

function createHelper($img){
    return ImageHelper::fromPath('tests/assets/thor100.png');
}