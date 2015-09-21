'use strict';

window.Config = {
    isRelease: true,
    baseUrl: 'http://w.sunpop.cn/',
    getLocale: function () {
        return localStorage['locale'] || 'cn';
    },
    setLocale: function (locale) {
        localStorage['locale'] = locale;
    },
    frames: {
        survey: {
            src: '/customersurvey'
        },
        personal: {
            src: '/customer/account/edit'
        },
        account: {
            src: '/customer/account'
        },
        order: {
            src: '/sales/order/history/'
        },
        address: {
            src: '/customer/address'
        },
        wishlist: {
            src: '/wishlist'
        },
        cart: {
            src: '/checkout/cart/'
        }
    }
};

if (!Config.isRelease)	{
    Config.baseUrl = '/';
}
