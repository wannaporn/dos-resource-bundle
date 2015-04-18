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
    }
}
