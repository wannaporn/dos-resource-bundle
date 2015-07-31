<?php

namespace DoS\ResourceBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class AjaxRedirect
{
    /**
     * Set 204 status and return location redirect for ajax redirect.
     *
     * @param FilterResponseEvent $event
     */
    public function setJsonRedirection(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        // no handle with ajax (except turbolinks)
        if ($event->getResponse() instanceof RedirectResponse && $request->isXmlHttpRequest()) {
            $uri = rtrim(explode('?', $request->getUri())[0], '/');
            $target = rtrim(explode('?', $targetUrl = $event->getResponse()->getTargetUrl())[0], '/');

            // when just sf route `/` matching redirect
            // most of index route end with `/` but we just enter `resources`
            // eg. `/users` may redirect to `/users/`
            if ($uri === $target) {
                return;
            }

            /** @var FlashBagInterface $flashes */
            $flashes = $event->getRequest()->getSession()->getFlashBag();
            $headers = array(
                'x-location' => $targetUrl,
                'x-flashes' => $flashes ? json_encode($flashes->all()) : null
            );

            $event->setResponse(new Response(null, 204, $headers));
        }
    }
}
