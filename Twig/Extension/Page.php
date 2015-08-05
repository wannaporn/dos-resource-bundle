<?php

namespace DoS\ResourceBundle\Twig\Extension;

use DoS\ResourceBundle\Templating\PageBuilder;
use Sonata\SeoBundle\Twig\Extension\SeoExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class Page extends \Twig_Extension
{
    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var SeoExtension
     */
    private $seoExtension;

    public function __construct(PageBuilder $pageBuilder, SeoExtension $seoExtension = null)
    {
        $this->pageBuilder = $pageBuilder;
        $this->seoExtension = $seoExtension;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->router = $container->get('router');
        $this->request = $container->get('request_stack');
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array_merge(array(
            new \Twig_SimpleFunction('ui_page', array($this, 'getUiFile')),
            new \Twig_SimpleFunction('ui_page_is', array($this, 'isPageId')),
            new \Twig_SimpleFunction('ui_page_get', array($this, 'getPageOption')),
            new \Twig_SimpleFunction('ui_page_options', array($this, 'setPageOptions')),
            new \Twig_SimpleFunction('ui_page_actions', array($this, 'getUiPageActions')),
            new \Twig_SimpleFunction('ui_page_self', array($this, 'getUiPageSelf')),
            new \Twig_SimpleFunction('ui_css_if', array($this, 'getCssIfMatchRoute')),
        ), $this->seoExtension ? array(
            new \Twig_SimpleFunction('ui_seo_title', array($this->seoExtension, 'getTitle'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ui_seo_metadatas', array($this->seoExtension, 'getMetadatas'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ui_seo_html_attributes', array($this->seoExtension, 'getHtmlAttributes'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ui_seo_head_attributes', array($this->seoExtension, 'getHeadAttributes'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ui_seo_link_canonical', array($this->seoExtension, 'getLinkCanonical'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ui_seo_lang_alternates', array($this->seoExtension, 'getLangAlternates'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ui_seo_oembed_links', array($this->seoExtension, 'getOembedLinks'), array('is_safe' => array('html'))),
        ) : array());
    }

    /**
     * @param $file
     * @param \Twig_Template|null $scope
     *
     * @return string
     */
    public function getUiFile($file, \Twig_Template $scope = null)
    {
        if ($scope) {
            return $this->getUiPageSelf($scope, $file);
        }

        return sprintf('ui:%s.html.twig', $file);
    }

    /**
     * @param \Twig_Template $scope
     * @param null|string    $name
     *
     * @return mixed|string
     */
    public function getUiPageActions(\Twig_Template $scope, $name = null)
    {
        $file = $scope->getTemplateName();
        $pattern = '/ui\:(.*):(.*)\.html\.twig/';

        if ($name) {
            $file = preg_replace($pattern, sprintf('ui:$1/Actions:%s.html.twig', $name), $file);
        } else {
            $file = preg_replace($pattern, 'ui:$1/Actions:${2}.html.twig', $file);
        }

        return $file;
    }

    /**
     * @param \Twig_Template $scope
     * @param string         $name
     *
     * @return mixed
     */
    public function getUiPageSelf(\Twig_Template $scope, $name)
    {
        $file = $scope->getTemplateName();
        $pattern = '/ui\:(.*):(.*)\.html\.twig/';

        return preg_replace($pattern, sprintf('ui:$1:%s.html.twig', $name), $file);
    }

    /**
     * @param array $options
     */
    public function setPageOptions(array $options = array())
    {
        $this->pageBuilder->setOptions($options);
    }

    /**
     * @param string $option
     * @param string $printOut
     * @param null   $default
     *
     * @return string
     */
    public function getPageOption($option, $printOut = '%s', $default = null)
    {
        return $this->pageBuilder->get($option, $printOut, $default);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function isPageId($id)
    {
        return $this->pageBuilder->getOption('id') === $id;
    }

    /**
     * @param string|bool $check
     * @param string      $css
     *
     * @return null|string
     */
    public function getCssIfMatchRoute($check, $css = 'active')
    {
        if (is_string($check)) {
            $matcher = $this->router->match($this->request->getCurrentRequest()->getPathInfo());
            $check = $matcher['_route'] === $check;
        }

        return $check ? $css : null;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ui_page';
    }
}
