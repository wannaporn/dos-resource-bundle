<?php

namespace DoS\ResourceBundle\Settings;

use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;

interface SettingsAwareInterface
{
    /**
     * @param SettingsManagerInterface $manager
     * @param string $alias
     */
    public function setSettings(SettingsManagerInterface $manager, $alias);
}
