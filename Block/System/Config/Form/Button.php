<?php
/**
 * Renders action button.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */
namespace Ebizcharge\Ebizcharge\Block\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\Config\ScopeConfigInterface;
//use Ebizcharge\Ebizcharge\Model\Data;

class Button extends Field
{
    private $_key;
    private $_url;
    private $_label;
    protected $_logger;

    /**
     * @var string
     */
    protected $_template = 'system/config/button.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
		\Magento\Backend\Helper\Data $HelperBackend,
		Context $context,
		\Ebizcharge\Ebizcharge\Model\Config $config,
		array $data = []
	)
    {
        $this->HelperBackend = $HelperBackend;
		$this->_logger = $context->getLogger();
		$this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $data = $element->getOriginalData();

        $this->_key = $data['id'];
        $this->_label = $data['label'];

        return $this->_toHtml();
    }
	
	public function getAdminUrl()
    {
        echo $this->HelperBackend->getHomePageUrl();
    }
	
	public function getAdminPaymentUrl()
    {
        echo $this->HelperBackend->getHomePageUrl().'system_config/edit/section/payment/';
    }
	

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('ebizcharge_ebizcharge/system_config/' . $this->_key);
    }
	
	/**
     * Get Econnect Yes/No
     *
     * @return bolean
     */
    public function getEconnect()
    {
		return $this->config->getEconnect();
    }

    /**
     * Return button function name
     *
     * @return string
     */
    public function getButtonFunction()
    {
        return $this->_key;
    }

    /**
     * Generate collect button html
     * 'id' => $this->_key . '_button',
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
            [
				'id' => $this->_key,
                'label' => $this->_label,
                'onclick'   => 'javascript:' . $this->_key . '_click(); return false;'
            ]
        );

        return $button->toHtml();
		
    }
}
