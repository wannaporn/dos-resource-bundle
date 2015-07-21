<?php

namespace DoS\ResourceBundle\Form\DataTransformer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform object collection to array.
 *
 * @author Liverbool <nukboon@gmail.com>
 */
class ObjectCollectionToArrayTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value instanceof Collection) {
            return $value->toArray();
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
