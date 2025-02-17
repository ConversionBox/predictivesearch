define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (DynamicRows) {
    'use strict';

    return DynamicRows.extend({
        /**
         * Override deleteRecord to ensure a new row is added only when all records are deleted.
         */
        deleteRecord: function (index) {
            this._super(index);

            // Ensure UI updates before checking elems() length
            setTimeout(() => {
                if (this.elems().length === 0 && !this._isAddingNewRow) {
                    this._isAddingNewRow = true;  // Prevent multiple row additions
                    this.addChild();
                    this._isAddingNewRow = false;
                }
            }, 50); // Reduced timeout for better responsiveness

            return this;
        },

        /**
         * Override addChild to set default values.
         */
        addChild: function (data, index) {
            data = data || {}; // Ensure data object exists
          //  data.attribute = []; // Set default empty array for the attribute field

            return this._super(data, index);
        }
    });
});
