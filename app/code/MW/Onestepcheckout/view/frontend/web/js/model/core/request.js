/*
 * Copyright © 2017 MW. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'ko',
        'jquery',
        'MW_Onestepcheckout/js/model/core/storage',
        'MW_Onestepcheckout/js/model/core/url-builder',
        'MW_Onestepcheckout/js/action/showLoader'
    ],
    function (ko, $, storage, UrlBuilder, ShowLoader) {
        "use strict";
        var Request = {
            initialize: function () {
                var self = this;
                return self;
            },
            send: function (url, method, payload, deferred, contentType, requestHeaders, crossDomain) {
                var self = this;
                if (!deferred) {
                    deferred = $.Deferred();
                }
                if($.isArray(requestHeaders)){
                    requestHeaders = ko.utils.arrayFilter(requestHeaders, function (header) {
                        return (header.key);
                    });
                }
                if(!self.isUrlValid(url)){
                    url = UrlBuilder.createUrl(url, {});
                }
                switch (method) {
                    case 'post':
                        ShowLoader.all(true);
                        storage.post(
                            url, JSON.stringify(payload), true, contentType, requestHeaders, crossDomain
                        ).done(
                            function (response, textStatus, xhr) {
                                deferred.resolve(response, textStatus, xhr);
                            }
                        ).fail(
                            function (xhr, textStatus, errorThrown) {
                                deferred.reject(xhr, textStatus, errorThrown);
                            }
                        ).always(function(){
                            ShowLoader.all(false);
                        });
                        break;
                    case 'get':
                        ShowLoader.all(true);
                        storage.get(
                            url, JSON.stringify(payload), contentType, requestHeaders, crossDomain
                        ).done(
                            function (response, textStatus, xhr) {
                                deferred.resolve(response, textStatus, xhr);
                            }
                        ).fail(
                            function (xhr, textStatus, errorThrown) {
                                deferred.reject(xhr, textStatus, errorThrown);
                            }
                        ).always(function(){
                            ShowLoader.all(false);
                        });
                        break;
                    case 'put':
                        ShowLoader.all(true);
                        storage.put(url, payload, false, contentType, requestHeaders, crossDomain).done(
                            function (response, textStatus, xhr) {
                                deferred.resolve(response, textStatus, xhr);
                            }
                        ).fail(
                            function (xhr, textStatus, errorThrown) {
                                deferred.reject(xhr, textStatus, errorThrown);
                            }
                        ).always(function(){
                            ShowLoader.all(false);
                        });
                        break;
                    case 'delete':
                        ShowLoader.all(true);
                        url = self.addParamsToUrl(url, payload);
                        storage.delete(
                            url, false, contentType, requestHeaders, crossDomain
                        ).done(
                            function (response, textStatus, xhr) {
                                deferred.resolve(response, textStatus, xhr);
                            }
                        ).fail(
                            function (xhr, textStatus, errorThrown) {
                                deferred.reject(xhr, textStatus, errorThrown);
                            }
                        ).always(function(){
                            ShowLoader.all(false);
                        });
                        break;
                    default:
                        break;
                }
                return deferred;
            },
            addParamsToUrl: function(url, params){
                $.each(params, function(key, value){
                    if(key){
                        if (url.indexOf("?") != -1) {
                            url = url + '&'+key+'=' + value;
                        }
                        else {
                            url = url + '?'+key+'=' + value;
                        }
                    }
                });
                return url;
            },
            isUrlValid: function(url) {
                return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
            },
        };
        return Request.initialize();
    }
);
