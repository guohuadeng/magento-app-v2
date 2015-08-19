angular.module('app.controllers', [])

    // 菜单
    .controller('AppCtrl', function ($scope, $rootScope,
                                     $ionicModal, $ionicSlideBoxDelegate,
                                     $ionicTabsDelegate, $ionicLoading,
                                     $ionicPopup, $timeout) {
        // Loading
        $scope.showLoading = function () {
            $ionicLoading.show({
                template: 'Loading...'
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

        //首次欢迎页
        $scope.welcome = function () {
            $ionicPopup.show({
                templateUrl: 'templates/welcome.html',
                title: '',
                cssClass: 'popupFullscreen',
                scope: $scope,
                buttons: [
                    {
                        text: 'Enter Kikuu',
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

        // 网站列表信息
        $scope.getWebsite = function () {
            $rootScope.service.get('website', function (website) {
                $scope.website = website;
            });
        };

        // 获取用户信息
        $scope.getUser = function () {
            $rootScope.service.get('user', function (user) {
                $scope.user = typeof user === 'object' ? user : null;
            });
        };
        $scope.getUser();

        // 菜单处理
        $rootScope.service.get('menus', function (menus) {
            $scope.menus = [
                {
                    cmd: 'daily_sale',
                    name: 'Daily Sale',
                    class_name: 'one-line',
                    item_height: '338'
                }
                /* 客户要求，去掉New Arrival
                 {
                 cmd: 'best_seller',
                 name: 'New Arrival',
                 class_name: 'one-line'
                 }
                 */
            ].concat(menus);
            $scope.$broadcast('menusData', $scope.menus);
        });
        $scope.setCatalog = function (index) {
            $scope.$broadcast('setCatalog', index);
        };
        $scope.$on('selectedIndex', function (e, index) {
            $scope.selectedIndex = index;
        });
        $scope.getActiveClass = function (index) {
            return $scope.selectedIndex === index;
        };

        // 登录
        $scope.showLogin = function () {
            $scope.loginData = {};

            var popup = $ionicPopup.show({
                templateUrl: 'templates/login.html',
                title: 'Login - Registered User',
                cssClass: 'login-container',
                scope: $scope,
                buttons: [
                    {text: 'Cancel'},
                    {
                        text: 'Login',
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
                title: 'Enter your email',
                cssClass: 'forgot-pwd-container',
                scope: $scope,
                buttons: [
                    {text: 'Cancel'},
                    {
                        text: '<b>Submit</b>',
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
                                    $scope.showAlert('Success', res.message);
                                    popup.close();
                                    return;
                                }
                                $scope.showAlert('Alert!', 'Error code:' + res.code + '</br>' + res.message);
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
                title: 'Confirm',
                template: 'Are you sure to exit the Kikuu App?',
                okType: 'button-assertive'
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

    // 列表
    .controller('ListsCtrl', function ($scope, $rootScope) {
        var getList = function (tab, type, callback) {
            if (type === 'load') {
                tab.page++;
            } else {
                tab.page = 1;
            }

            var params = {
                limit: 20,
                page: tab.page,
                cmd: tab.cmd || 'catalog'
            };
            if (tab.category_id) {
                params.categoryid = +tab.category_id;
            }
            $scope.showLoading();
            $rootScope.service.get('products', params, function (lists) {
                if (type === 'load') {
                    if (lists) {
                        tab.lists = tab.lists.concat(lists);
                    } else {
                        tab.loadOver = true;
                    }
                } else {
                    tab.lists = lists;
                }
                tab.hasInit = true;
                if (typeof callback === 'function') {
                    callback();
                }
            });

            $scope.hideLoading();
        };

        // 根据菜单生成 tabs
        $scope.$on('menusData', function (e, menus) {
            $scope.menus = menus;
            $scope.tabs = menus.slice(0);
            $scope.selectedIndex = 0;
        });
        $scope.$on('setCatalog', function (e, index) {
            $scope.selectedIndex = index;
        });
        $scope.$watch('selectedIndex', function () {
            if (!$scope.tabs) {
                return;
            }
            var tab = $scope.tabs[$scope.selectedIndex];

            $scope.$emit('selectedIndex', $scope.selectedIndex);

            if (tab.hasInit) {
                return;
            }
            getList(tab, 'refresh');
        });

        $scope.doRefresh = function (index) {
            getList($scope.tabs[index], 'refresh', function () {
                $scope.$broadcast('scroll.refreshComplete');
            });
        };
        $scope.loadMore = function (index) {
            getList($scope.tabs[index], 'load', function () {
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };
    })

    // 产品详情
    .controller('productDetailCtrl', function ($scope, $rootScope,
                                               $stateParams, $ionicPopup,
                                               $ionicSlideBoxDelegate, $ionicScrollDelegate,
                                               $cordovaSocialSharing) {
        $scope.showLoading();
        $scope.qty = 1;

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

            //取商品选项
            if (results.has_custom_options) {
                $rootScope.service.get('productOption', {
                    productid: $stateParams.productid
                }, function (option) {
                    $scope.productOption = option;
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

        // 选择列表
        $scope.selectOptions = {};
        $scope.selectOption = function (name) {
            $scope.selectOptions[name + this.$parent.option.option_id] = this.item.option_type_id;
        };

        // 增加到购物车
        $scope.doCartAdd = function () {
            var queryString = $('#product_addtocart_form').formSerialize();
            if (!($scope.qty > 1)) {
                $scope.qty = 1;
                $scope.$apply();
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

    //产品选项
    .controller('ProductOptionCtrl', function ($scope, $stateParams) {

    })

    .controller('SearchCtrl', function ($scope, $location) {
        $scope.model = {};
        //angular.element('#search-input').trigger('focus');
        $scope.onSearch = function () {
            $location.path('app/search/' + $scope.model.text);
        };
    })

    .controller('SearchResultCtrl', function ($scope, $rootScope, $stateParams) {
        $scope.text = $stateParams.text;
        $rootScope.service.get('search', {q: $stateParams.text}, function (results) {
            $scope.results = results;
        });
    })

    .controller('FrameCtrl', function ($scope, $sce, $stateParams, Config) {
        $scope.trustSrc = function (src) {
            return $sce.trustAsResourceUrl(src);
        };

        var frame = Config.frames[$stateParams.page];
        $scope.title = frame.title;
        $scope.src = frame.src;
    });
