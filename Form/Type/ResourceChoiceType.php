<?php

namespace DoS\ResourceBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceChoiceType as BaseResourceChoiceType;

class ResourceChoiceType extends BaseResourceChoiceType
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param ObjectManager|null $manager
     */
    public function setObjectManager(ObjectManager $manager = null)
    {
        $this->manager = $manager;
    }
}
