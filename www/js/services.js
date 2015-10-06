function Service($rootScope, $http) {

    var api = {
        website: '/restconnect/store/websiteinfo',
        store: '/restconnect/store/storeinfo',
        getStaticBlock: '/restconnect/index/getstaticblock',
        getBannerBlock: '/restconnect/index/getbannerblock',
        user: '/restconnect/customer/status',
        forgotpwd: '/restconnect/customer/forgotpwd',
        menus: '/restconnect/?cmd=menu',
        products: '/restconnect/',
        login: '/restconnect/customer/login',
        logout: '/restconnect/customer/logout',
        register: '/restconnect/customer/register',
        search: '/restconnect/search',
        certGet: '/clnews/api/article',
        searchAdvField: '/restconnect/searchadv/getfield',
        searchAdv: '/restconnect/searchadv/index',
        searchAgent: '/storelocator/index/city',
        productDetail: '/restconnect/products/getproductdetail',
        productImg: '/restconnect/products/getPicLists',
        productOption: '/restconnect/products/getcustomoption',
        cart: '/restconnect/cart/getCartInfo',	//获取购物车内容
        cartGetQty: '/restconnect/cart/getQty',	//
        cartGetTotal: '/restconnect/cart/getTotal',	//
        cartAdd: '/restconnect/cart/add'	//直接post到这个接口就返回参数
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
        },
        sendSms : function (params, success, error) {
            if (typeof params === 'function') {
                error = success;
                success = params;
                params = null;
            }

            var url = Config.baseUrl + 'smsapi/SendTemplateSMS.php';
            $http.get(url, {
                params: params
            }).then(function (res) {
                success(res.data);
            }, error);
        }
    }
}
