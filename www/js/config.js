'use strict';

window.Config = {
    isRelease: true,
    debug: true,
    baseUrl: 'http://shiny.sunpop.cn/',

    getRememberme: function () {
        return localStorage['rememberme'] || true;
    },
    setRememberme: function (rememberme) {
        localStorage['rememberme'] = rememberme;
    },
    getUsername: function ()    {
        return localStorage['username']|| '';
    },
    setUsername: function (username)    {
        localStorage['username'] = username;
    },
    getPassword: function ()    {
        return localStorage['password']|| '';
    },
    setPassword: function (password)    {
        localStorage['password'] = password;
    },
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
