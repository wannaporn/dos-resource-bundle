<?php

namespace DoS\ResourceBundle\Uploader;

use DoS\ResourceBundle\Model\ImageInterface;

interface ImageUploaderInterface
{
    /**
     * @param ImageInterface $image
     */
    public function upload(ImageInterface $image);

    /**
     * @param $path
     */
    public function remove($path);
}
