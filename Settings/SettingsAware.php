<?php

namespace DoS\ResourceBundle\Settings;

use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Sylius\Bundle\SettingsBundle\Model\SettingsInterface;

abstract class SettingsAware implements SettingsAwareInterface
{
    /**
     * @var SettingsInterface
     */
    protected $settings;

    /**
     * {@inheritdoc}
     */
    public function setSettings(SettingsManagerInterface $manager, $alias)
    {
        $this->settings = $manager->load($alias);
    }
}
