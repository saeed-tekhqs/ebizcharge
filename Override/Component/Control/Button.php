<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/29/21
 * Time: 3:56 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Override\Component\Control;

use Magento\Ui\Component\Control\Button as CoreButton;
use Magento\Framework\View\Element\Html\Date;

/**
 * Add the calendar type input on admin grid top
 *
 * Class Button
 */
class Button extends CoreButton
{
    /**
     * Custom Template path
     */
    const CUSTOM_TEMPLATE = 'Ebizcharge_Ebizcharge::uicomponent/control/button/default.phtml';

    /**
     * Retrieve template path
     *
     * @return string
     */
    protected function getTemplatePath()
    {
        return static::CUSTOM_TEMPLATE;
    }

    /**
     * Check if ebiz custom calendar input
     *
     * @return bool
     */
    public function ifEbizOrderGrid()
    {
        return $this->getData('type') == 'ebiz-date-picker';
    }

    /**
     * Retrieve name of the calendar
     *
     * @return false|mixed
     */
    public function getName()
    {
        if ($this->ifEbizOrderGrid()) {
            return $this->getData('type');
        }
        return false;
    }

    /**
     * Get calendar date picker
     *
     * @return false|string
     */
    public function getDateInput() {
        $datePickerInput = false;
        try {
            $datePickerInput = $this->getLayout()->createBlock(Date::class)
                ->setData([
                        'name' => $this->getName(),
                        'id' => $this->getName(),
                        'value' => '',
                        'extra_params' => ' placeholder = "   Select date"',
                        'date_format' => 'dd-MM-y',
                        'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                        'years_range' => '-120y:c+nn',
                        'max_date' => '-1d',
                        'change_month' => 'true',
                        'change_year' => 'true',
                        'show_on' => 'both',
                        'autocomplete' => 'off',
                        'first_day' => 1
                ])
                ->toHtml();
        } catch (\Exception $e) {
            return $datePickerInput;
        }
        return $datePickerInput;
    }

    /**
     * Get calendar css
     *
     * @return string
     */
    public function getCalendarCss(): string
    {
        return sprintf("<style> #%s + button { margin-right: 10%%; margin-top: -0.3%%; height: 5%%;}</style>", $this->getName());
    }

    /**
     * @return string|null
     */
    public function getOnClick(): ?string
    {
        if ($this->getData('type') == 'download-orders') {
            return null;
        }
        return parent::getOnClick();
    }
}
