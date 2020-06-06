/*
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['ko', 'jquery'],
    function(ko, $) {
        return {
            baseUrl: ko.observable(''),
            method: "rest",
            version: 'V1',
            serviceUrl: ':method/:version/',
            createUrl: function(url, params, isApi) {
                if(url && url.match("^/")){
                    url = url.substr(1);
                }
                params = (params)?params:{};
                isApi = (isApi === false)?false:true;
                var baseUrl = this.baseUrl();
                if(baseUrl && !baseUrl.match("/$")){
                    baseUrl = baseUrl+'/';
                }
                if (((url.indexOf("rest/") != -1) && (url.indexOf("V1/") != -1)) || !isApi) {
                    var completeUrl = url;
                }else{
                    var completeUrl = this.serviceUrl + url;
                }
                completeUrl = this.bindParams(completeUrl, params);
                if (completeUrl.indexOf(baseUrl) !== -1) {
                    return completeUrl;
                }
                return baseUrl + completeUrl;
            },
            bindParams: function(url, params) {
                var self = this;
                params.method = this.method;
                params.version = this.version;

                var urlParts = url.split("/");
                urlParts = urlParts.filter(Boolean);

                var addedParams = [];
                var urlParams = {};
                $.each(urlParts, function(key, part) {
                    part = part.replace(':', '');
                    if (params[part] != undefined) {
                        urlParts[key] = params[part];
                        addedParams.push(part);
                    }
                });
                var completeUrl = urlParts.join('/');

                $.each(params, function(key, value) {
                    if ($.inArray(key, addedParams) < 0) {
                        urlParams[key] = value;
                    }
                });

                if(urlParams != {}){
                    completeUrl = self.addParamsToUrl(completeUrl, urlParams);
                }
                return completeUrl;
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
            }
        };
    }
);
