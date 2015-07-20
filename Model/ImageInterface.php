<?php

namespace DoS\ResourceBundle\Model;

interface ImageInterface extends TimestampableInterface
{
    /**
     * @return Boolean
     */
    public function hasFile();

    /**
     * @return null|\SplFileInfo
     */
    public function getFile();

    /**
     * @param null|\SplFileInfo $file
     */
    public function setFile(\SplFileInfo $file = null);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $path
     */
    public function setPath($path);
}
