'use strict';

window.Config = {
    isRelease: true,
    baseUrl: 'http://w.sunpop.cn/',
    baseSite: 'http://w.sunpop.cn/m',
    getLocale: function () {
        return localStorage['locale'] || 'cn';
    },
    setLocale: function (locale) {
        localStorage['locale'] = locale;
    },
    frames: {
        personal: {
            title: 'Persional Infomation',
            src: '/customer/account/edit'
        },
        account: {
            title: 'My Account',
            src: '/customer/account'
        },
        order: {
            title: 'My Orders',
            src: '/sales/order/history/'
        },
        address: {
            title: 'My Address Book',
            src: '/customer/address'
        },
        wishlist: {
            title: 'My Wishlist',
            src: '/wishlist'
        },
        cart: {
            title: 'My Shopping Cart',
            src: '/checkout/cart/'
        }
    }
};

if (!Config.isRelease)	{
    Config.baseUrl = '/';
}
