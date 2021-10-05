define([
	"jquery",
], function($) {
    "use strict";
    $.widget('mage.ebizcharge', {
		options: {
            clientKey: false
        },
        prepare : function(event, method) {}
    });

    return $.mage.ebizcharge;
});
