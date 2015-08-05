<?php

namespace DoS\ResourceBundle\Form\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ChoiceResizeListener
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $configs
     *
     * @return ChoiceResizeListener
     */
    public function create(FormBuilderInterface $builder, array $configs)
    {
        return new self($builder, $configs);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $configs
     */
    public function __construct(FormBuilderInterface $builder, array $configs)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use ($configs) {
                $this->build($event, $configs);
            })
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($configs) {
                $this->build($event, $configs);
            })
            ->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) use ($configs) {
                $form = $event->getForm();

                foreach($configs as $property => $config) {
                    $depended = $config['depended'];

                    if (!is_array($depended)) {
                        $depended = array('partner' => $depended);
                    }

                    if ($form->get($partnerName = $depended['partner'])->getData()) {
                        return;
                    }

                    if ($data = $form->get($property)->getData()) {
                        $form->get($partnerName)
                            ->setData(call_user_func_array(array($data, 'get' . $partnerName), array()))
                        ;
                    }
                }
            })
        ;
    }

    /**
     * @param FormEvent $event
     * @param array $configs
     */
    private function build(FormEvent $event, array $configs)
    {
        foreach($configs as $property => $config) {
            $this->resize($event, $property, $config);
        }
    }

    /**
     * @param FormEvent $event
     * @param string $property
     * @param array $config
     */
    private function resize(FormEvent $event, $property, array $config)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $depended = $config['depended'];

        if (!is_array($depended)) {
            $depended = array('partner' => $depended);
        }

        $repository = isset($depended['repository']) ? $depended['repository'] : null;
        $partner = $form->get($partnerName = $depended['partner']);

        // pre-submit
        if (is_array($data)) {
            if (!$partnerData = $data[$partnerName]) {
                return;
            }

            $partnerOptions = $partner->getConfig()->getOptions();
            /** @var ObjectManager $em */
            $em = $partnerOptions['em'];
            $partnerData = $em->getRepository($partnerOptions['class'])->find($partnerData);
        } else {
            if (!$partnerData = $partner->getData()) {
                if($fieldData = call_user_func_array(array($data, 'get' . $property), array())) {
                    $partnerData = call_user_func_array(array($fieldData, 'get' . $partnerName), array());
                }
            }

            if (!$partnerData) {
                return;
            }
        }

        unset($config['options']['choices']);

        $form->remove($property);
        $form->add($property, $config['type'], array_merge($config['options'], array(
            'query_builder' => function(EntityRepository $er) use ($partnerName, $partnerData, $repository) {
                if ($repository) {
                    return call_user_func_array(array($er, $repository), array($partnerData));
                }

                return $er
                    ->createQueryBuilder('o')
                    ->where(sprintf('o.%s = :partnerData', $partnerName))
                    ->setParameter('partnerData', $partnerData)
                ;
            }
        )));
    }
}
