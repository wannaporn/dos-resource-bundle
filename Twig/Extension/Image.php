<?php

namespace DoS\ResourceBundle\Twig\Extension;

use DoS\ResourceBundle\Model\ImageInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class Image extends \Twig_Extension
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var FilterConfiguration
     */
    private $filter;

    private $defaultFilterDimension = '32x32';

    /**
     * @var string
     */
    private $imageHolderPath = 'https://placehold.it';

    public function __construct(
        CacheManager $cacheManager,
        FilterConfiguration $filter,
        $imageHolderPath = null,
        $defaultFilterDimension = null
    ) {
        $this->cacheManager = $cacheManager;
        $this->filter = $filter;

        if ($imageHolderPath) {
            $this->imageHolderPath = $imageHolderPath;
        }

        if ($defaultFilterDimension) {
            $this->defaultFilterDimension = $defaultFilterDimension;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            'ui_image_filter' => new \Twig_Filter_Method($this, 'filter'), // deprecated
            'ui_image' => new \Twig_Filter_Method($this, 'filter'),
        );
    }

    /**
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $filter
     * @param array  $runtimeConfig
     *
     * @return \Twig_Markup
     */
    public function filter($path, $filter, array $runtimeConfig = array())
    {
        if ($path instanceof ImageInterface) {
            $path = $path->getPath();
        }

        if (empty($path)) {
            return $this->imageHolderPath.'/'.$this->getFilterDimension($filter);
        }

        return new \Twig_Markup(
            $this->cacheManager->getBrowserPath($path, $filter, $runtimeConfig),
            'utf8'
        );
    }

    private function getFilterDimension($name)
    {
        $filter = $this->filter->get($name);

        if (empty($filter)) {
            return $this->defaultFilterDimension;
        }

        $size = $filter['filters']['thumbnail']['size'];

        return vsprintf('%sx%s', $size);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ui_image';
    }
}
