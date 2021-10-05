/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent'
    ],
    function (Component) {
        "use strict";
        var quoteItemData = window.checkoutConfig.quoteItemData;
        return Component.extend({
			defaults: {
                template: 'Ebizcharge_Ebizcharge/summary/item/details'
            },
            quoteItemData: quoteItemData,
            getValue: function(quoteItem) {
                return quoteItem.name;
            },
            getSubscribed: function(quoteItem){
                var item = this.getItem(quoteItem.item_id);
                return item.subscribed;
            },
			getFrequency: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                return item.frequency;
            },
			getQtySubscribed: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                return item.qty_subscribed;
            },
			getSdate: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                return item.sdate;
            },
			getEdate: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                return item.edate;
            },			
            getItem: function(item_id)
			{
                var itemElement = null;
                _.each(this.quoteItemData, function(element, index)
                //this.each(this.quoteItemData, function(element, index)
				{
                    if (element.item_id == item_id)
					{
                        itemElement = element;
                    }
                });
                return itemElement;
            }
        });
    }
);