<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class DoSResourceExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getBundleConfiguration()
    {
        return new Configuration();
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = parent::load($configs, $container);

        if ($config['form_factory']['enabled']) {
            $container->setParameter('dos.form.factory.class', $config['form_factory']['class']);
            $container->setParameter('dos.form.factory.override_pattern', $config['form_factory']['pattern']);
            $container->setParameter('dos.form.factory.override_replacement', $config['form_factory']['replacement']);
        }

        $container->setParameter('dos.slugify.reg_exp', $config['slugify']['reg_exp']);
        $container->setParameter('dos.slugify.lowercase', $config['slugify']['lowercase']);
    }

    /**
     * @inheritdoc
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        // use the Configuration class to generate a config array with
        $config = $this->processConfiguration(new Configuration(), $configs);
        // remove dos_resource config before append to sylius_resource.
        unset($config['form_factory']);
        unset($config['slugify']);

        $container->prependExtensionConfig('sylius_resource', $config);

        $container->setParameter('dos.locale_traditional', true);
        $container->setParameter('dos.image_holder', null);
    }
}
