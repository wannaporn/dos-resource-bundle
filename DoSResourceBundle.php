<?php

namespace DoS\ResourceBundle;

use DoS\ResourceBundle\DependencyInjection\AbstractResourceBundle;
use DoS\ResourceBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoSResourceBundle extends AbstractResourceBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\ExpressionLanguagePass());
        $container->addCompilerPass(new Compiler\FormFactoryPass());
        $container->addCompilerPass(new Compiler\SlugifyPass());
    }
}
