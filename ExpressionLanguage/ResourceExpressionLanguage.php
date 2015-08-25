<?php

namespace DoS\ResourceBundle\ExpressionLanguage;

use Sylius\Bundle\ResourceBundle\ExpressionLanguage\ExpressionLanguage;

class ResourceExpressionLanguage extends ExpressionLanguage
{
    /**
     * {@inheritdoc}
     */
    protected function registerFunctions()
    {
        parent::registerFunctions();

        $this->register(
            'getCurrentUser',
            function ($arg) {
                return sprintf('$this->get("security.token_storage")->getToken()->getUser()', $arg);
            },
            function (array $variables) {
                return $variables['container']->get('security.token_storage')->getToken()->getUser();
            }
        );

        $this->register(
            'getQueryParam',
            function ($limitKey, $default) {
                return sprintf('$this->get("request")->get(%s, %s)', $limitKey, $default);
            },
            function (array $variables, $limitKey, $default) {
                return $variables['container']->get('request')->get($limitKey, $default);
            }
        );
    }
}
