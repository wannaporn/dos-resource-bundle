<?php

namespace DoS\ResourceBundle\Uploader;

use Gaufrette\Filesystem;
use DoS\ResourceBundle\Model\ImageInterface;

class ImageUploader implements ImageUploaderInterface
{
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param ImageInterface $image
     */
    public function upload(ImageInterface $image)
    {
        if (!$image->hasFile()) {
            return;
        }

        // remove older path
        if (null !== $image->getPath() && $this->filesystem->has($image->getPath())) {
            $this->remove($image->getPath());
        }

        do {
            $hash = md5(uniqid(mt_rand(), true));
            $path = $this->expandPath($hash.'.'.$image->getFile()->getExtension());
        } while ($this->filesystem->has($path));

        $image->setPath($path);

        $this->filesystem->write(
            $image->getPath(),
            file_get_contents($image->getFile()->getPathname())
        );
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function remove($path)
    {
        return $this->filesystem->delete($path);
    }

    /**
     * @param $path
     *
     * @return string
     */
    private function expandPath($path)
    {
        return sprintf(
            '%s/%s/%s',
            substr($path, 0, 2),
            substr($path, 2, 2),
            substr($path, 4)
        );
    }
}
