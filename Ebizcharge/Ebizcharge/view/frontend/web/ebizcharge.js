define([
    "jquery",
    'Magento_Ui/js/modal/confirm',
    "jquery/ui",
    "mage/translate"
], function($, confirm) {
    "use strict";
    
    $.widget('mage.ebizcharge', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            deleteConfirmMessage: $.mage.__('Are you sure you want to delete this credit card?')
        },

        /**
         * Bind event handlers for adding and deleting credit cards.
         * @private
         */
        _create: function() {
            var options         = this.options,
                addCreditCard      = options.addCreditCard;

            if( addCreditCard ){
                $(document).on('click', addCreditCard, this._addCreditCard.bind(this));
            }
        },

        /**
         * Add a new credit card.
         * @private
         */
        _addCreditCard: function() {
            window.location = this.options.addCreditCardLocation;
        },
		
    });
    
    return $.mage.ebizcharge;
});