<?php

namespace DoS\ResourceBundle\Originator;

use DoS\ResourceBundle\Model\OriginContextInterface;
use Sylius\Component\Originator\Model\OriginAwareInterface;
use Sylius\Component\Originator\Originator\Originator as BaseOriginator;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;

class Originator extends BaseOriginator
{
    /**
     * {@inheritdoc}
     */
    public function setOrigin(OriginAwareInterface $originAware, $origin)
    {
        if (!is_object($origin)) {
            throw new UnexpectedTypeException($origin, 'object');
        }

        /** @var \DoS\ResourceBundle\Model\OriginAwareInterface $originAware */
        if ($origin instanceof OriginContextInterface) {
            $originAware->setOriginAlias($origin->getOriginalAlias());
            $originAware->setOriginType($origin->getOriginalType());
            $originAware->setOriginId($origin->getId());

            return;
        }

        if (null === $id = $this->accessor->getValue($origin, $this->identifier)) {
            throw new \InvalidArgumentException(sprintf(
                'Origin %s is not set.',
                $this->identifier
            ));
        }

        $originAware->setOriginId($id);
        $originAware->setOriginType(get_class($origin));
    }
}
