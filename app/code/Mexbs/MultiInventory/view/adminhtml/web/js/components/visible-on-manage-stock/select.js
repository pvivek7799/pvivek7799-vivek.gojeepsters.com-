define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        setUseDefaultValues: function (value){
            if (value == 0){
                this.useDefaultValues = false;
            }else{
                this.useDefaultValues = true;
            }
            this.visible(!this.useDefaultValues && this.isManageStock);
        },
        setIsManageStock: function (value){
            if (value == 0){
                this.isManageStock = false;
            }else{
                this.isManageStock = true;
            }
            this.visible(!this.useDefaultValues && this.isManageStock);
        }
    });
});