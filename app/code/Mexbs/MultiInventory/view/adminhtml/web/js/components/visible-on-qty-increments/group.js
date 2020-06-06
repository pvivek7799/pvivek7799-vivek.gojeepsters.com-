define([
    'Magento_Ui/js/form/components/group'
], function (Group) {
    'use strict';

    return Group.extend({
        setUseDefaultValues: function (value){
            if (value == 0){
                this.useDefaultValues = false;
            }else{
                this.useDefaultValues = true;
            }
            this.visible(!this.useDefaultValues && this.isQtyIncrements);
        },
        setIsQtyIncrements: function (value){
            if (value == 0){
                this.isQtyIncrements = false;
            }else{
                this.isQtyIncrements = true;
            }
            this.visible(!this.useDefaultValues && this.isQtyIncrements);
        }
    });
});
