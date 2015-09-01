function Service($rootScope, $http) {

    var api = {
        website: '/restconnect/store/websiteinfo',
        store: '/restconnect/store/storeinfo',
        user: '/restconnect/customer/status',
        forgotpwd: '/restconnect/customer/forgotpwd',
        menus: '/restconnect/?cmd=menu',
        products: '/restconnect/',
        login: '/restconnect/customer/login',
        logout: '/customer/account/logout',
        register: '/restconnect/customer/register',
        search: '/restconnect/search',
        searchAdvField: '/restconnect/searchadv/getfield',
        productDetail: '/restconnect/products/getproductdetail',
        productImg: '/restconnect/products/getPicLists',
        productOption: '/restconnect/products/getcustomoption',
        cartGetQty: '/restconnect/cart/getQty',	//直接post到这个接口就返回参数
        cartAdd: '/restconnect/cart/add/'	//直接post到这个接口就返回参数
    };

    $rootScope.service = {
        get: function (key, params, success, error) {
            if (typeof params === 'function') {
                error = success;
                success = params;
                params = null;
            }

            var url = Config.baseUrl + Config.getLocale() + api[key];

            $http.get(url, {
                params: params
            }).then(function (res) {
                success(res.data);
            }, error);
        },
        post: function (key, params, success, error) {
            if (typeof params === 'function') {
                callback = params;
                params = null;
            }

            var url = Config.baseUrl + Config.getLocale() + api[key];

            $.post(url, {
                params: params
            }).then(function (res) {
                success(res.data);
            }, error);
        }
    }
}
