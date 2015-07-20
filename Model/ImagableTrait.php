<?php

namespace DoS\ResourceBundle\Model;

trait ImagableTrait
{
    /**
     * @var \SplFileInfo
     */
    protected $file;

    /**
     * @var string
     */
    protected $path;

    /**
     * @return Boolean
     */
    public function hasFile()
    {
        return $this->file !== null;
    }

    /**
     * @return null|\SplFileInfo
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param null|\SplFileInfo $file
     */
    public function setFile(\SplFileInfo $file = null)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}
