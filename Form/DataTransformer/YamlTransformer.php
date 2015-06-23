<?php

namespace DoS\ResourceBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Yaml\Yaml;

class YamlTransformer implements DataTransformerInterface
{
    /**
     * The level where you switch to inline YAML.
     *
     * @var int
     */
    protected $inlineLevel;

    /**
     * @param int $inlineLevel
     */
    public function __construct($inlineLevel = 10)
    {
        $this->inlineLevel = $inlineLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return Yaml::parse($value);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return Yaml::dump($value, $this->inlineLevel);
    }
}
