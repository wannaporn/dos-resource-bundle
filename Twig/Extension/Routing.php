<?php

namespace DoS\ResourceBundle\Twig\Extension;

use Cocur\Slugify\SlugifyInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author liverbool <nukboon@gmail.com>
 */
class Routing extends \Twig_Extension
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var SlugifyInterface
     */
    protected $slugify;

    public function __construct(RouterInterface $router, SlugifyInterface $slugify)
    {
        $this->router = $router;
        $this->slugify = $slugify;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ui_resource_route', array($this, 'generateObjectUrl')),
        );
    }

    /**
     * @param mixed $object
     * @param string $routeName #Route
     * @param string $separator
     * @param array $query
     *
     * @return string
     */
    public function generateObjectUrl($object, $routeName, $separator = '_', array $query = array())
    {
        $parameters = array();
        $accessor = PropertyAccess::createPropertyAccessor();
        $pattern = $this->router->getRouteCollection()->get($routeName)->getPath();

        if (preg_match_all('/\{([a-z]+)\}/', $pattern, $matches)) {
            foreach($matches[1] as $holder) {
                $value = $accessor->getValue($object, $holder);

                if (!is_numeric($value)) {
                    $value = $this->slugify->slugify($value, $separator);
                }

                $parameters[$holder] = $value;
            }
        }

        return $this->router->generate($routeName, $parameters, $query);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ui_routing';
    }
}
