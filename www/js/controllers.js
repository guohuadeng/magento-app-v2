angular.module('app.controllers', [])

    // 菜单
    .controller('AppCtrl', function ($scope, $rootScope,
                                     $ionicModal, $ionicSlideBoxDelegate,
                                     $ionicTabsDelegate, $ionicLoading,
                                     $ionicPopup, $timeout, $state,
                                     $ionicSideMenuDelegate, $translate) {

        $translate(Object.keys(zh_CN)).then(function (translations) {
            $scope.translations = translations;
        });

        // Loading
        $scope.showLoading = function () {
            $ionicLoading.show({
                template: ''
            });
        };
        $scope.hideLoading = function () {
            $ionicLoading.hide();
        };

        // Alert dialog
        $scope.showAlert = function (_title, _content) {
            $ionicPopup.alert({
                title: _title,
                template: _content,
                okType: 'button-assertive'
            });
        };

        $scope.menuClose = function () {
            $ionicSideMenuDelegate.toggleLeft(false);
        };

        //首次欢迎页
        $scope.welcome = function () {
            $ionicPopup.show({
                templateUrl: 'templates/welcome.html',
                title: '',
                cssClass: 'popupWelcome',
                scope: $scope,
                buttons: [
                    {
                        text: $scope.translations.enter_app,
                        type: 'button-light'
                    }
                ]
            });
        };
        if (!localStorage['first-use']) {
            localStorage['first-use'] = true;
            $timeout(function () {
                $scope.welcome();
            }, 50);
        }

        // 获取用户信息
        $scope.getUser = function () {
            $rootScope.service.get('user', function (user) {
                $scope.user = typeof user === 'object' ? user : null;
            });
        };
        $scope.getUser();

        // 登录
        $scope.showLogin = function () {
            $scope.loginData = {};

            var popup = $ionicPopup.show({
                templateUrl: 'templates/login.html',
                title: $scope.translations.login_title,
                cssClass: 'login-container',
                scope: $scope,
                buttons: [
                    {text: $scope.translations.cancel},
                    {
                        text: $scope.translations.login,
                        type: 'button-assertive',
                        onTap: function (e) {
                            e.preventDefault();
                            if (!$scope.loginData.username || !$scope.loginData.password) {
                                return;
                            }

                            $scope.showLoading();
                            $rootScope.service.get('login', $scope.loginData, function (res) {
                                $scope.hideLoading();

                                if (res.code || res.message) {
                                    $scope.showAlert('Alert!', res.message || res.code);
                                    return;
                                }
                                $scope.user = res;
                                popup.close();
                            });
                        }
                    }
                ]
            });
            $scope.hideLogin = function () {
                popup.close();
            };
        };

        // 忘记密码
        $scope.showPopupForgotPwd = function () {
            $scope.pwdData = {};

            var popup = $ionicPopup.show({
                templateUrl: 'templates/forgotPwd.html',
                title: $scope.translations.enter_email,
                cssClass: 'forgot-pwd-container',
                scope: $scope,
                buttons: [
                    {text: $scope.translations.cancel},
                    {
                        text: $scope.translations.submit,
                        type: 'button-assertive',
                        onTap: function (e) {
                            e.preventDefault();
                            if (!$scope.pwdData.email) {
                                return;
                            }

                            $scope.showLoading();
                            $rootScope.service.get('forgotpwd', $scope.pwdData, function (res) {
                                $scope.hideLoading();
                                if (res.code == '0x0000') {
                                    $scope.showAlert($scope.translations.success, res.message);
                                    popup.close();
                                    return;
                                }
                                $scope.showAlert($scope.translations.alert, $scope.translations.error_code +
                                    res.code + '</br>' + res.message);
                            });
                        }
                    }
                ]
            });
        };

        // 退出登录
        $scope.doLogout = function () {
            $scope.showLoading();
            $rootScope.service.get('logout', $scope.getUser);
            $timeout($scope.hideLoading(), 1000);
        };

        // 退出应用
        $scope.showExit = function () {
            $ionicPopup.confirm({
                title: $scope.translations.confirm,
                template: $scope.translations.exit_tip,
                okType: 'button-assertive',
                buttons: [
                    {text: $scope.translations.cancel},
                    {
                        text: $scope.translations.ok,
                        onTap: function (e) {
                            e.preventDefault();
                            navigator.app.exitApp();
                        }
                    }
                ]
            });
        };

        // 取搜索选项
        //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        $rootScope.service.get('searchAdvField', {}, function (results) {
            var fields = [];

            for (var key in results) {
                fields.push(results[key]);
            }
            $scope.searchFields = fields;
        });
    })

    // 注册
    .controller('registerCtrl', function ($scope, $rootScope, $timeout, $state) {
        $scope.registerData = {};

        $scope.validationCodeDisabled = false;
        $scope.getValidationCode = function () {
            $scope.validationCodeDisabled = true;
            $scope.validationCode = ~~(Math.random() * 8999) + 1000;
            $scope.validateSeconds = 30;
            var update = function () {
                if ($scope.validateSeconds-- > 0) {
                    $timeout(update, 1000);
                } else {
                    $scope.validationCodeDisabled = false;
                }
            };
            update();
        };

        $scope.doRegister = function () {
            if ($scope.registerData.password !== $scope.registerData.confirmation) {
                $scope.showAlert('Alert!', 'Please confirm you password!');
                return;
            }

            $scope.showLoading();
            $rootScope.service.get('register', $scope.registerData, function (res) {
                $scope.hideLoading();

                if (res[0]) {
                    $scope.showAlert('Success!', 'Welcome! User register success.');
                    $scope.getUser();
                    $state.go('app.register');
                    return;
                }
                $scope.showAlert('Alert!', res[2]);
            });
        };
    })

    // 设置
    .controller('settingCtrl', function ($scope, $rootScope, $translate, $ionicHistory) {
        // 网站列表信息
        $scope.getWebsite = function () {
            $rootScope.service.get('website', function (website) {
                $scope.languages = [];
                for (var l in website['1'].webside['1'].view) {
                    $scope.languages.push(website['1'].webside['1'].view[l]);
                }
            });
        };
        $scope.getWebsite();

        $scope.locale = Config.getLocale();

        $scope.changeLocale = function () {
            $scope.locale = this.language.store_code;
            $translate.use($scope.locale);
            Config.setLocale($scope.locale);
            $ionicHistory.clearCache();
        };
    })

    // 列表
    .controller('ListsCtrl', function ($scope, $rootScope, $stateParams, $translate) {
        $scope.listTitle = {
            daily_sale: 'latest_promotions',
            'new': 'common_products',
            cert_download: 'cert_download'
        }[$stateParams.cmd];
        $scope.listPge = 1;
        $scope.hasInit = false;
        $scope.loadOver = false;
        if ($stateParams.cmd === 'daily_sale') {
            $scope.lineClass = 'one-line';
        }

        var getList = function (func, callback) {
            if (func === 'load') {
                $scope.listPge++;
            } else {
                $scope.listPge = 1;
            }

            var params = {
                limit: 20,
                page: $scope.listPge,
                cmd: $stateParams.cmd || 'catalog'
            };

            $scope.showLoading();
            $rootScope.service.get('products', params, function (lists) {
                if (func === 'load') {
                    if (Array.isArray(lists) && lists.length) {
                        $scope.lists = $scope.lists.concat(lists);
                    } else {
                        $scope.loadOver = true;
                    }
                } else {
                    $scope.hasInit = true;
                    $scope.lists = lists;
                    if (!localStorage['symbol']) {
                        localStorage['symbol'] = lists[0].symbol;
                    }
                }
                if (typeof callback === 'function') {
                    callback();
                }
            });

            $scope.hideLoading();
        };

        $scope.doRefresh = function () {
            getList('refresh', function () {
                $scope.$broadcast('scroll.refreshComplete');
            });
        };
        $scope.loadMore = function () {
            if (!$scope.hasInit || $scope.loadOver) {
                $scope.$broadcast('scroll.infiniteScrollComplete');
                return;
            }
            getList('load', function () {
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };

        getList('refresh');
    })

    // 产品详情
    .controller('productDetailCtrl', function ($scope, $rootScope, $timeout,
                                               $stateParams, $ionicPopup, $translate,
                                               $ionicSlideBoxDelegate, $ionicScrollDelegate,
                                               $cordovaSocialSharing, $ionicSideMenuDelegate) {
        $scope.showLoading();
        $scope.qty = 1;
        $scope.totalPrice = 0;

        $scope.updateSlider = function () {
            $ionicSlideBoxDelegate.$getByHandle('image-viewer').update();
        };

        // 物车商品数量
        $rootScope.service.get('cartGetQty', {
            product: $stateParams.productid
        }, function (res) {
            $scope.items_qty = res.items_qty;
        });

        // 商品详情
        $rootScope.service.get('productDetail', {
            productid: $stateParams.productid
        }, function (results) {
            $scope.product = results;
            $scope.totalPrice = +$scope.product.final_price_with_tax;
            $scope.oldPrice = +$scope.product.regular_price_with_tax;

            //取商品选项
            if (results.has_custom_options) {
                $rootScope.service.get('productOption', {
                    productid: $stateParams.productid
                }, function (option) {
                    $scope.productOption = option;
                    $timeout($scope.updatePrice, 0);
                });
            }
            $scope.hideLoading();
        });

        // 商品图片
        $rootScope.service.get('productImg', {
            product: $stateParams.productid
        }, function (lists) {
            $scope.productImg = lists;
        });

        // 分享
        $scope.onShare = function () {
            $cordovaSocialSharing.share($scope.product.name, $scope.product.name, '', $scope.product.url_key);
        };

        // 全屏幕图片
        $scope.imageFullscreen = function () {
            var toggle = 1;

            $scope.getCurrentSlideIndex = function () {
                return $ionicSlideBoxDelegate.currentIndex();
            };
            $scope.updateFullscreenSlider = function () {
                $ionicSlideBoxDelegate.$getByHandle('image-fullscreen-viewer').update();
            };
            $scope.zoomProductImg = function () {
                if (toggle === 1) {
                    toggle = 2;
                    $ionicScrollDelegate.$getByHandle('image-scroll').zoomTo(toggle);
                } else {
                    toggle = 1;
                    $ionicScrollDelegate.$getByHandle('image-scroll').zoomTo(toggle);
                }
            };
            $scope.noZoom = function () {
                $ionicScrollDelegate.$getByHandle('image-scroll').zoomTo(1);
            };

            //直接用template，会出现图片无法垂直居中的问题
            var myt = '<ion-content overflow-scroll="true">'
                +'<ion-slide-box delegate-handle="image-fullscreen-viewer" on-slide-changed="noZoom()" show-pager="true" active-slide="'
                + $ionicSlideBoxDelegate.currentIndex()
                + '"><ion-slide ng-repeat="img in productImg" ng-init="updateFullscreenSlider()">'
                +'<ion-scroll overflow-scroll="true" delegate-handle="image-scroll" zooming="true" direction="xy" locking="false" scrollbar-x="false" scrollbar-y="false" min-zoom="1" id="scrolly"  style="width: 100%; height: 100%;">'
                +'<img id="zoomImg" class="fullwidth" ng-src="{{img.url}}"  on-double-tap="zoomProductImg()">'
                +'<span></span>'
                +'</ion-scroll>'
                +'</ion-slide></ion-slide-box>';
            +'</ion-content>';
            // An elaborate, custom popup
            var myPopup = $ionicPopup.show({
                template: myt,
                cssClass: 'popupFullscreen',
                scope: $scope,
                buttons: [
                    { text: 'X' ,
                        type: 'button-dark',},
                ]
            });
            /*
            $ionicPopup.show({
                templateUrl: 'templates/productImg.html',
                cssClass: 'popupFullscreen',
                scope: $scope,
                buttons: [
                    {
                        text: 'X',
                        type: 'button-dark'
                    }
                ]
            });
            */
        };

        // 增减数量操作
        $scope.qtyAdd = function () {
            $scope.qty++;
        };
        $scope.qtyMinus = function () {
            if ($scope.qty > 1) {
                $scope.qty--;
            }
        };
        $scope.$watch('qty', function () {
            $timeout($scope.updatePrice, 0);
        });

        // 选择列表
        $scope.selectOptions = {};
        $scope.selectOption = function (name) {
            $scope.selectOptions[name + this.$parent.option.option_id] = this.item.option_type_id;
            $timeout($scope.updatePrice, 0);
        };

        $scope.updatePrice = function () {
            if (!$scope.product) {
                return;
            }
            $scope.totalPrice = +$scope.product.final_price_with_tax;
            $scope.oldPrice = +$scope.product.regular_price_with_tax;
            // field
            $('[ng-switch-when="field"]').find('[data-price]').each(function () {
                $scope.totalPrice += +$(this).data('price');
                $scope.oldPrice += +$(this).data('price');
            });
            //drop_down
            $('[ng-switch-when="drop_down"] select').each(function () {
                $scope.totalPrice += +$(this).find(':selected').data('price') || 0;
                $scope.oldPrice += +$(this).find(':selected').data('price') || 0;
            });
            // check
            $('[ng-switch-when="checkbox"] input:checked').each(function () {
                $scope.totalPrice += +$(this).data('price') || 0;
                $scope.oldPrice += +$(this).data('price') || 0;
            });
            // radio
            $('[ng-switch-when="radio"] span.selected').each(function () {
                $scope.totalPrice += +$(this).data('price') || 0;
                $scope.oldPrice += +$(this).data('price') || 0;
            });
            // qty
            $scope.totalPrice *= $scope.qty;
            $scope.oldPrice *= $scope.qty;
        };

        // 增加到购物车
        $scope.doCartAdd = function () {
            var queryString = $('#product_addtocart_form').formParams();
            if (!($scope.qty > 1)) {
                $scope.qty = 1;
            }
            $rootScope.service.get('cartAdd', queryString, function (res) {
                if (res.result == 'error') {
                    $scope.showAlert('Alert!', res.message);
                    return;
                }
                if (res.result == 'success') {
                    $scope.showAlert('Success', res.items_qty + '&nbsp;'+ $scope.translations['items_in_cart']);
                    $scope.items_qty = res.items_qty;
                    return;
                }
            });
        };
    })

    // home中，取banner，快速搜索
    .controller('HomeCtrl', function ($scope, $rootScope, $state, $ionicSlideBoxDelegate) {
        $scope.searchData = {};

        $('.homebanner-swiper-container').height($(window).width() * 4 / 9);
        $scope.updateSlider = function () {
            $ionicSlideBoxDelegate.$getByHandle('image-viewer').update();
        };
        // 取首页第一个banner
        $rootScope.service.get('getBannerBlock', {block:'app_home_banner1'}, function (results) {
            $scope.banner1 = results;

            var banner1 = [];
            for (var key in results) {
                banner1.push(results[key]);
            }
            $scope.banner1 = banner1;
        });
        //快速搜索
        $scope.onSearch = function () {
            if (!$scope.searchData.text) {
                return;
            }
            $rootScope.search = {
                type: 'search',
                params: {
                    q: $scope.searchData.text
                }
            };
            $state.go('app.searchResult');
        };
    })

    // 高级搜索
    .controller('SearchAdvCtrl', function ($scope, $rootScope, $state) {
        $scope.searAdvData = {};
        // 取目录选项
        $rootScope.service.get('menus', {}, function (results) {
            var cat_field = [];

            for (var key in results) {
                cat_field.push(results[key]);
            }
            $scope.cat_field = cat_field;
        });

        $scope.optionChange = function () {
            if (this.field.code === 'a_xingzhuang') {
                var $shape = $('select[name="' + this.field.code + '"]'),
                    shape = $.trim($shape.find('option:selected').text());

                $('select[name="a_guige"]').val('').find('option').show();
                if ($shape.val()) {
                    $('select[name="a_guige"]').find('option:not(:contains((' + shape + '))').hide();
                    $('select[name="a_guige"]').find('option[value=""]').show();
                }
            }
        };

        $scope.onSearch = function () {
            $rootScope.search = {
                type: 'searchAdv',
                params: $('#searAdv').formParams()
            };
            $state.go('app.searchResult');
        };
    })

    // 搜索结果
    .controller('SearchResultCtrl', function ($scope, $rootScope) {
        if (!$rootScope.search) {
            return;
        }
        if ($rootScope.search.type === 'search') {
            $scope.searchTitle = $scope.translations.quick_search +
                $scope.translations.colon + $rootScope.search.params.q;
        } else {
            $scope.searchTitle = $scope.translations.product_searchadv;
        }

        $scope.page = 1;
        var getList = function (func, callback) {
            if (func === 'load') {
                $scope.page++;
            } else {
                $scope.page = 1;
            }
            $rootScope.search.params.page = $scope.page;
            $rootScope.service.get($rootScope.search.type, $rootScope.search.params, function (results) {
                if (func === 'load') {
                    if (Array.isArray(results.productlist) && results.productlist.length) {
                        $scope.results = $scope.results.concat(results.productlist);
                    } else {
                        $scope.loadOver = true;
                    }
                } else {
                    $scope.hasInit = true;
                    if (Array.isArray(results.productlist) && results.productlist.length) {
                        $scope.results = results.productlist;
                    } else {
                        $scope.noProductFound = true;
                    }
                }
                if (typeof callback === 'function') {
                    callback();
                }
            });
        };

        $scope.doRefresh = function () {
            getList('refresh', function () {
                $scope.$broadcast('scroll.refreshComplete');
            });
        };
        $scope.loadMore = function () {
            if (!$scope.hasInit || $scope.loadOver) {
                $scope.$broadcast('scroll.infiniteScrollComplete');
                return;
            }
            getList('load', function () {
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };
        getList('refresh');
    })

    // 证书下载
    .controller('certCtrl', function ($scope, $rootScope) {
        // 取证书列表选项
        $rootScope.service.get('certGet', {}, function (results) {
            var certList = [];

            for (var key in results.articlelist) {
                certList.push(results.articlelist[key]);
            }
            $scope.certList = certList;
        });
    })
    // 购物车
    .controller('cartCtrl', function ($scope, $rootScope) {
        // 取证书列表选项
        $rootScope.service.get('cart', {}, function (results) {
            var cartList = [];

            for (var key in results.cart_items) {
                cartList.push(results.cart_items[key]);
            }
            $scope.cartList = cartList;
            $scope.symbol = localStorage['symbol'];
        });
    })
    // 附近经销商
    .controller('SearchAgentCtrl', function ($scope, $rootScope, $state, $cordovaGeolocation) {
        $scope.searchData = {
            //address: $scope.translations.current_position,
            address: '广州',
            radius: 200
        };

        $scope.onSearch = function () {
            if (!$scope.searchData.address) {
                return;
            }
            if ($scope.searchData.address === $scope.translations.current_position) {
                $cordovaGeolocation.getCurrentPosition({timeout: 10000, enableHighAccuracy: false})
                    .then(function (position) {
                        $rootScope.agent = {
                            title: $scope.searchData.address,
                            params: $.extend({}, {
                                radius: $scope.searchData.radius
                            }, {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            })
                        };
                        $state.go('app.agents');
                    }, function(err) {
                        alert($scope.translations.position_not_found);
                    });
                return;
            }
            var myGeo = new BMap.Geocoder();
            myGeo.getPoint($scope.searchData.address, function(point){
                if (point) {
                    $rootScope.agent = {
                        title: $scope.searchData.address,
                        params: $.extend({}, {
                            radius: $scope.searchData.radius
                        }, point)
                    };
                    $state.go('app.agents');
                } else {
                    alert($scope.translations.position_not_found);
                }
            });
        };
    })

    .controller('AgentsCtrl', function ($scope, $rootScope, $ionicPopup, $timeout) {
        if (!$rootScope.agent) {
            return;
        }
        $scope.titleText = $rootScope.agent.title;
        $rootScope.service.get('searchAgent', $rootScope.agent.params, function (res) {
            $scope.agentList = res;
        });

        $scope.showAgent = function () {
            $scope.agent = this.agent;
            $ionicPopup.show({
                templateUrl: 'templates/agent.html',
                title: this.agent.store_name,
                cssClass: 'agent-container',
                scope: $scope,
                buttons: [{
                    text: $scope.translations.ok,
                    type: 'button-assertive',
                }]
            });
        };

        $scope.showMap = function () {
            if (!$('#map').length) {
                setTimeout($scope.showMap, 100);
                return;
            }
            $('#map').parent().html('<div id="map"></div>');

            setTimeout(function () {
                var map = new BMap.Map('map'),
                    point = new BMap.Point($rootScope.agent.params.lng, $rootScope.agent.params.lat);
                if ($rootScope.agent.params['radius']>0) {
                    $scope.zoomLevel =  13;
                }
                if ($rootScope.agent.params['radius']>10)    {
                    $scope.zoomLevel =  11;
                }
                if ($rootScope.agent.params['radius']>20)    {
                    $scope.zoomLevel =  9;
                }
                if ($rootScope.agent.params['radius']>50)    {
                    $scope.zoomLevel =  8;
                }
                if ($rootScope.agent.params['radius']>200)    {
                    $scope.zoomLevel =  6;
                }
                if ($rootScope.agent.params['radius']>500)    {
                    $scope.zoomLevel =  5;
                }

                map.centerAndZoom(point, $scope.zoomLevel); //1000公里用5，500公里用5，200的用6，100公里用8，50公里用8，20公里用9，10公里用11，5公里内用13，
                $scope.agentList.forEach(function (item) {
                    var point = new BMap.Point(item.lng, item.lat),
                        marker = new BMap.Marker(point);

                    map.addOverlay(marker);
                });
            }, 100);
        };
    })

    .controller('FrameCtrl', function ($scope, $sce, $stateParams) {
        $scope.trustSrc = function (src) {
            return $sce.trustAsResourceUrl(src);
        };

        var frame = Config.frames[$stateParams.page];
        $scope.title = $scope.translations[$stateParams.page];
        $scope.src = Config.baseUrl + Config.getLocale() + frame.src;
    });
