<?php

namespace DoS\ResourceBundle\Rest;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler as BaseViewHandler;

class ViewHandler extends BaseViewHandler
{
    protected $serializerEnableMaxDepthChecks = true;

    /**
     * @return bool
     */
    public function getSerializerEnableMaxDepthChecks()
    {
        $this->serializerEnableMaxDepthChecks;
    }

    /**
     * @param bool $flag
     */
    public function setSerializerEnableMaxDepthChecks($flag)
    {
        $this->serializerEnableMaxDepthChecks = boolval($flag);
    }

    /**
     * @param View $view
     *
     * @return \JMS\Serializer\SerializationContext
     */
    protected function getSerializationContext(View $view)
    {
        $context = parent::getSerializationContext($view);

        if ($this->serializerEnableMaxDepthChecks) {
            $context->enableMaxDepthChecks();
        }

        return $context;
    }
}
