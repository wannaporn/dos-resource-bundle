<?php

namespace DoS\ResourceBundle\Form\EventListener;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ChoiceResizeListener
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $configs
     *
     * @return ChoiceResizeListener
     */
    public static function create(FormBuilderInterface $builder, array $configs)
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
                    if (true === $depended = $this->prepare($config, 'self')) {
                        $this->resizeBySelf($event, $property, $config);
                        continue;
                    }

                    if ($form->get($partnerName = $depended['partner'])->getData()) {
                        continue;
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
     * @param array $config
     * @param $selfCheck
     *
     * @return array|bool
     */
    private function prepare(array $config, $selfCheck)
    {
        $depended = $config['depended'];

        if (!is_array($depended)) {
            $depended = array('partner' => $depended);
        }

        if ($depended['partner'] === $selfCheck) {
            return true;
        }

        return $depended;
    }

    /**
     * @param FormEvent $event
     * @param array $configs
     */
    private function build(FormEvent $event, array $configs)
    {
        foreach($configs as $property => $config) {
            $this->resize($event, $property, $config);
            $this->resizeBySelf($event, $property, $config);
        }
    }

    /**
     * @param FormEvent $event
     * @param string $property
     * @param array $config
     */
    private function resize(FormEvent $event, $property, array $config)
    {
        if (true === $depended = $this->prepare($config, 'self')) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();
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
                $accessor = PropertyAccess::createPropertyAccessor();
                if($fieldData = $accessor->getValue($data, $property)) {
                    $partnerData = $accessor->getValue($fieldData, $partnerName);
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

                $partnerNames = explode('.', $partnerName);
                $partnerName = array_pop($partnerNames);

                return $er
                    ->createQueryBuilder('o')
                    ->where(sprintf('o.%s = :partnerData', $partnerName))
                    ->setParameter('partnerData', $partnerData)
                ;
            }
        )));
    }

    /**
     * @param FormEvent $event
     * @param $property
     * @param array $config
     */
    private function resizeBySelf(FormEvent $event, $property, array $config)
    {
        if (true !== $depended = $this->prepare($config, 'self')) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();

        if (is_array($data) && empty($data)) {
            return;
        }

        if (is_array($data)) {
            $ids = (array) $data[$property];
        } else {
            $ids = call_user_func_array(array($data, 'get'.$property), array());
            if ($ids instanceof Collection) {
                $ids = $ids->toArray();
            }
        }

        if (empty($ids)) {
            return;
        }

        $repository = isset($depended['repository']) ? $depended['repository'] : null;
        unset($config['options']['choices']);

        $form->remove($property);
        $form->add($property, $config['type'], array_merge($config['options'], array(
            'query_builder' => function(EntityRepository $er) use ($ids, $repository) {
                if ($repository) {
                    return call_user_func_array(array($er, $repository), array($ids));
                }

                $qb = $er->createQueryBuilder('o');

                return $qb
                    ->where($qb->expr()->in('o.id', ':ids'))
                    ->setParameter('ids', $ids)
                ;
            }
        )));
    }
}
