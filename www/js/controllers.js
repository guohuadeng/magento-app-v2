angular.module('app.controllers', [])

    // 菜单
    .controller('AppCtrl', function ($scope, $rootScope,
                                     $ionicModal, $ionicSlideBoxDelegate,
                                     $ionicTabsDelegate, $ionicLoading,
                                     $ionicPopup, $timeout, $location,
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
                cssClass: 'popupFullscreen',
                scope: $scope,
                buttons: [
                    {
                        text: $scope.translations.enter_app,
                        type: 'button-energized'
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
                    {text: $scope.translations.ok}
                ]
            }).then(function (res) {
                if (res) {
                    navigator.app.exitApp();
                }
            });
        };
    })

    // 注册
    .controller('registerCtrl', function ($scope, $rootScope, $timeout, $location) {
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
                    $location.path('#app/register');
                    return;
                }
                $scope.showAlert('Alert!', res[2]);
            });
        };
    })

    // 设置
    .controller('settingCtrl', function ($scope, $rootScope, $translate) {
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
        };
    })

    // 列表
    .controller('ListsCtrl', function ($scope, $rootScope, $stateParams, $translate) {
        $scope.listTitle = {
            daily_sale: 'latest_promotions',
            new: 'common_products',
            cert_download: 'cert_download'
        }[$stateParams.cmd];
        $scope.listPge = 1;
        $scope.hasInit = false;
        $scope.loadOver = false;

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
                                               $stateParams, $ionicPopup,
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
            $ionicPopup.show({
                templateUrl: 'templates/productImg.html',
                cssClass: 'popupFullscreen',
                scope: $scope,
                buttons: [
                    {
                        text: '< ',
                        type: 'button-light',
                        onTap: function (e) {
                            e.preventDefault();
                            $ionicSlideBoxDelegate.previous();
                        }
                    },
                    {
                        text: 'Close',
                        type: 'button-light'
                    },
                    {
                        text: '>',
                        type: 'button-light',
                        onTap: function (e) {
                            e.preventDefault();
                            $ionicSlideBoxDelegate.next();
                        }
                    }
                ]
            });
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
            var queryString = $('#product_addtocart_form').formSerialize();
            if (!($scope.qty > 1)) {
                $scope.qty = 1;
            }
            $rootScope.service.get('cartAdd', queryString, function (res) {
                if (res.result == 'error') {
                    $scope.showAlert('Alert!', res.message);
                    return;
                }
                if (res.result == 'success') {
                    $scope.showAlert('Success', res.items_qty + '&nbsp;items already in your cart.');
                    $scope.items_qty = res.items_qty;
                    return;
                }
            });
        };
    })

    // 快速搜索Home
    .controller('HomeCtrl', function ($scope, $location) {
        $scope.searchData = {};
        $scope.onSearch = function () {
            if (!$scope.searchData.text) {
                return;
            }
            $location.path('app/search/' + $scope.searchData.text);
        };
    })
    
    // 快速搜索
    .controller('SearchCtrl', function ($scope, $location) {
        $scope.searchData = {};
        $scope.onSearch = function () {
            if (!$scope.searchData.text) {
                return;
            }
            $location.path('app/search/' + $scope.searchData.text);
        };
    })

    // 搜索结果
    .controller('SearchResultCtrl', function ($scope, $rootScope, $stateParams) {
        $scope.text = $stateParams.text;
        $scope.showLoading();
        $rootScope.service.get('search', {
            q: $stateParams.text
        }, function (results) {
            $scope.hideLoading();
            $scope.results = results.productlist;
        });
    })

    // 高级搜索
    .controller('SearchAdvCtrl', function ($scope, $rootScope, $location) {
        $scope.searchData = {};
        // 取目录选项
        $rootScope.service.get('menus', {}, function (results) {
            var cat_field = [];

            for (var key in results) {
                cat_field.push(results[key]);
            }
            $scope.cat_field = cat_field;
        });

        // 取搜索选项
        //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        $rootScope.service.get('searchAdvField', {}, function (results) {
            var fields = [];

            for (var key in results) {
                fields.push(results[key]);
            }
            $scope.fields = fields;
        });

        $scope.onSearch = function () {
            if (!$scope.searchData.text) {
                return;
            }
            $location.path('app/searchadv/' + $scope.searchData.text);
        };
    })
    .controller('FrameCtrl', function ($scope, $sce, $stateParams) {
        $scope.trustSrc = function (src) {
            return $sce.trustAsResourceUrl(src);
        };

        var frame = Config.frames[$stateParams.page];
        $scope.title = frame.title;
        $scope.src = Config.baseSite + frame.src;
    });
