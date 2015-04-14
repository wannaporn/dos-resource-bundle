<?php

namespace DoS\ResourceBundle\Form\Type;

use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType as BaseMoneyType;

class MoneyType extends BaseMoneyType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dos_money';
    }
}
