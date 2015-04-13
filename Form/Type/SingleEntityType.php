<?php

namespace DoS\ResourceBundle\Form;

use Doctrine\Common\Persistence\ManagerRegistry;
use DoS\ResourceBundle\Form\DataTransformer\IdentifierToObjectTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SingleEntityType extends AbstractType
{
    /**
     * Manager registry.
     *
     * @var ManagerRegistry
     */
    protected $manager;

    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['class']) {
            throw new LogicException('Option "class" must be set.');
        }

        $modelTransformer = new IdentifierToObjectTransformer(
            $this->manager->getRepository($options['class']),
            $options['identifier']
        );

        $builder
            ->addModelTransformer($modelTransformer)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['model'] = $form->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'identifier' => 'id',
                'empty_data' => null,
                'class' => null,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'single_entity';
    }
}
