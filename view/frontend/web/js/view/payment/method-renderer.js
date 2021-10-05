/**
* Implements the payment method renderer.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'ebizcharge_ebizcharge',
                component: 'Ebizcharge_Ebizcharge/js/view/payment/method-renderer/ebizcharge'
            }
        );
        return Component.extend({});
    }
);