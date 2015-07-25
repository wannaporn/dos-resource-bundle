<?php

namespace DoS\ResourceBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ReplaceSyliusTrans
{
    protected $controller = 'bazinga.jstranslation.controller:getTranslationsAction';
    protected $pattern;
    protected $replacement;

    public function __construct($controller = null, $pattern = '/"sylius\./', $replacement = '"ui.t.')
    {
        $this->controller = $controller ?: $this->controller;
        $this->pattern = $pattern;
        $this->replacement = $replacement;
    }

    /**
     * Replace Sylius translation keys.
     *
     * @param FilterResponseEvent $event
     */
    public function replaceKeys(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->controller !== $event->getRequest()->attributes->get('_controller')) {
            return;
        }

        $response = $event->getResponse();
        $content = preg_replace($this->pattern, $this->replacement, $response->getContent());
        $response->setContent($content);
    }
}
