<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class DoSResourceExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->configure($configs, new Configuration(), $container,
            self::CONFIGURE_LOADER
        );

        if ($config['form_factory']['enabled']) {
            $container->setParameter('dos.form.factory.class', $config['form_factory']['class']);
            $container->setParameter('dos.form.factory.override_pattern', $config['form_factory']['pattern']);
            $container->setParameter('dos.form.factory.override_replacement', $config['form_factory']['replacement']);
        }
    }

    /**
     * @inheritdoc
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        // use the Configuration class to generate a config array with
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->prependExtensionConfig('sylius_resource', $config);

        $container->setParameter('dos.locale_traditional', true);
        $container->setParameter('dos.image_holder', null);
    }
}
