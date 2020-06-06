/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
/*global define*/
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'mage/calendar'
    ],
    function($, ko, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'MW_Onestepcheckout/delivery-date'
            },

            isShowDelivery: ko.observable(window.checkoutConfig.delivery_time_date),

            isShowSecurityCode: ko.observable(window.checkoutConfig.is_enable_security_code),

            listTime: ko.observableArray(),

            initialize: function () {
                if(!this.listTime().length){
                    var listTimeArray = this.getListTimeConfig();
                    if(listTimeArray.length){
                        var newTimeArray = this.getListTimeAvaiable();
                        this.listTime(newTimeArray);
                    }
                    else{
                        this.listTime([]);
                    }
                }

                this._super();
            },

            getListTimeAvaiable: function(time){
                var self = this;
                var listTimeArray = this.getListTimeConfig();
                var newTimeArray = [];
                var currentDate = new Date();
                var hour = currentDate.getHours();
                var today = this.currentDate();
                $.each(listTimeArray, function (index, value) {
                    if(time && self.parseDateTime(time) != self.parseDateTime(today)){
                        newTimeArray.push(value + ':00');
                    }
                    else{
                        if(value > hour){
                            newTimeArray.push(value + ':00');
                        }
                    }
                });

                return newTimeArray;
            },

            parseDateTime: function (str) {
                var mdy = str.split('/');
                var date = new Date(mdy[2], mdy[1], mdy[0]);
                return date.getTime();
            },

            getListTimeConfig: function(){
                var listTime = window.checkoutConfig.delivery_hour;
                var listTimeArray = listTime.split(',');
                return listTimeArray;
            },

            currentDate: function () {
                var currentDate = new Date();
                var year = currentDate.getFullYear();
                var month = currentDate.getMonth() + 1;
                var date = currentDate.getDate();
                var day = currentDate.getDay();

                var disableDay = window.checkoutConfig.disable_day;
                if (disableDay) {
                    var disableDayArray = disableDay.split(',').map(Number);
                    var i;

                    for (i=0; i<=6; i++) {
                        if ($.inArray(day, disableDayArray) == -1 ) {
                            year = currentDate.getFullYear();
                            month = currentDate.getMonth() + 1;
                            date = currentDate.getDate();
                            return month + '/' + date + '/' + year;
                        }
                        currentDate.setDate(currentDate.getDate() + 1)
                        day = currentDate.getDay();
                    }
                }
                return month + '/' + date + '/' + year;
            },

            // listTime: function() {
            //     var listTime = window.checkoutConfig.delivery_hour;
            //     if(listTime != ""){
            //         var listTimeArray = listTime.split(',');
            //         var newTimeArray = [];
            //         $.each(listTimeArray, function (index, value) {
            //             newTimeArray[index] = value + ':00';
            //         });
            //         return newTimeArray;
            //     }
            //     return false;
            // },

            setTime: function(elm, event){
                var time = event.target.value;
                this.listTime(this.getListTimeAvaiable(time));
            },

            initDate: function () {
                var currentDate = new Date();
                var year = currentDate.getFullYear();
                var month = currentDate.getMonth();
                var day = currentDate.getDate();
                var self = this;
                $("#delivery_date").calendar({
                    showsTime: false,
                    controlType: 'select',
                    timeFormat: 'HH:mm TT',
                    showTime: false,
                    minDate: new Date(year, month, day, '00', '00', '00', '00'),
                    beforeShowDay: self.disableDate
                });
            },

            disableDate: function (date) {
                var day = date.getDay();
                var disableDay = window.checkoutConfig.disable_day;
                if (disableDay) {
                    var disableDayArray = disableDay.split(',').map(Number);
                    // Now check if the current date is in disabled dates array.

                    if ($.inArray(day, disableDayArray) != -1 ) {
                        return [false];
                    } else {
                        return [true];
                    }
                } else {
                    return [true];
                }

            }
        });
    }
);
