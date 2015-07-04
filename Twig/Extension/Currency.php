<?php

namespace DoS\ResourceBundle\Twig\Extension;

use Sylius\Bundle\MoneyBundle\Templating\Helper\MoneyHelper;
use Sylius\Bundle\SettingsBundle\Templating\Helper\SettingsHelper;
use Symfony\Component\Intl\Intl as SymfonyIntl; #fix small bug in console with Intl naming

class Currency extends \Twig_Extension
{
    /**
     * @var MoneyHelper
     */
    protected $moneyHelper;

    /**
     * @var SettingsHelper
     */
    protected $helper;

    protected $generalCurrencyKey;

    protected $currencySymbols = array(
        'THB' => '฿',
    );

    public function __construct(
        MoneyHelper $moneyHelper,
        SettingsHelper $helper,
        $generalCurrencyKey = 'general.currency',
        array $currencySymbols = array()
    ) {
        $this->moneyHelper = $moneyHelper;
        $this->helper = $helper;
        $this->generalCurrencyKey = $generalCurrencyKey;
        $this->currencySymbols = array_merge($this->currencySymbols, $currencySymbols);
    }

    public function getFunctions()
    {
        $self = array('is_safe' => array('all'));

        return array(
            new \Twig_SimpleFunction(
                'ui_currency_symbol', array($this, 'getCurrencySymbol'), $self
            ),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ui_money', array($this, 'formatAmount')),
        );
    }

    public function getCurrencySymbol($currency = null)
    {
        $currency = $currency ?: $this->helper->getSettingsParameter($this->generalCurrencyKey);

        if (array_key_exists($currency, $this->currencySymbols)) {
            return $this->currencySymbols[$currency];
        }

        return SymfonyIntl::getCurrencyBundle()->getCurrencySymbol($currency);
    }

    /**
     * @param      $amount
     * @param null $currency
     *
     * @return string
     */
    public function formatAmount($amount, $currency = null)
    {
        return str_replace('.00', '', $this->moneyHelper->formatAmount($amount, $currency));
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'ui_currency';
    }
}
