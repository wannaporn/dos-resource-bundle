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
        return null !== $this->file;
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
    public function setFile(\SplFileInfo $file)
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPath()
    {
        return null !== $this->path;
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
