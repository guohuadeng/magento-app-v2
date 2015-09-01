// Ionic Starter App
// 'app' is the name of this angular module (also set in a <body> attribute in index.html)
angular.module('app', [
        'ionic', 'ngCordova', 'pascalprecht.translate',
        'app.controllers', 'app.filters', 'ionicLazyLoad'
    ])

    .run(function ($ionicPlatform, $rootScope, $http) {
        $ionicPlatform.ready(function () {
            // Hide the accessory bar by default
            if (window.cordova && window.cordova.plugins.Keyboard) {
                cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
            }
            if (window.StatusBar) {
                // org.apache.cordova.statusbar required
                StatusBar.styleDefault();
            }
        });
        Service($rootScope, $http);
    })

    .config(function ($stateProvider, $urlRouterProvider, $ionicConfigProvider, $translateProvider) {
				$ionicConfigProvider.backButton.text('Back').icon('ion-chevron-left');
				$ionicConfigProvider.scrolling.jsScrolling(true);
				$ionicConfigProvider.tabs.position(top);
				$ionicConfigProvider.form.checkbox('square');
				$ionicConfigProvider.views.transition('none');  //('fade-in')
				
        $stateProvider
            .state('app', {
                url: '/app',
                abstract: true,
                templateUrl: 'templates/menu.html',
                controller: 'AppCtrl'
            })
            .state('app.home', {
                url: '/home', //首页
                views: {
                    'menuContent': {
                        templateUrl: 'templates/home.html'
                    }
                }
            })
            .state('app.lists', {
                url: '/lists/:cmd',
                views: {
                    'menuContent': {
                        templateUrl: 'templates/lists.html',
                        controller: 'ListsCtrl'
                    }
                }
            })
            .state('app.certDownload', {
                url: '/certDownload', //附近经销商
                views: {
                    'menuContent': {
                        templateUrl: 'templates/certDownload.html'
                    }
                }
            })
            .state('app.searchAgent', {
                url: '/searchAgent', //附近经销商
                views: {
                    'menuContent': {
                        templateUrl: 'templates/searchAgent.html'
                    }
                }
            })
            .state('app.survey', {
                url: '/survey', //问卷调查
                views: {
                    'menuContent': {
                        templateUrl: 'templates/survey.html'
                    }
                }
            })
            .state('app.register', {
                url: '/register',
                views: {
                    'menuContent': {
                        templateUrl: 'templates/register.html',
                        controller: 'registerCtrl'
                    }
                }
            })
            .state('app.setting', {
                url: '/setting',
                views: {
                    'menuContent': {
                        templateUrl: 'templates/setting.html',
                        controller: 'settingCtrl'
                    }
                }
            })
            .state('app.productDetail', {
                url: '/productDetail/:productid',
                views: {
                    'menuContent': {
                        templateUrl: 'templates/productDetail.html',
                        controller: 'productDetailCtrl'
                    }
                }
            })
            .state('app.search', {
                url: '/search',
                views: {
                    'menuContent': {
                        templateUrl: 'templates/search.html',
                        controller: 'SearchCtrl'
                    }
                }
            })
            .state('app.searchResult', {
                url: '/search/:text',
                views: {
                    'menuContent': {
                        templateUrl: 'templates/searchResult.html',
                        controller: 'SearchResultCtrl'
                    }
                }
            })
            .state('app.cart', {
                url: '/cart',
                views: {
                    'menuContent': {
                        templateUrl: 'templates/cart.html'
                    }
                }
            })
            .state('app.frame', {
                url: '/frame/:page',
                views: {
                    'menuContent': {
                        templateUrl: 'templates/frame.html',
                        controller: 'FrameCtrl'
                    }
                }
            });

        // if none of the above states are matched, use this as the fallback
        $urlRouterProvider.otherwise('/app/home');

        $translateProvider.translations('cn', zh_CN);
        $translateProvider.translations('en', en_US);
        $translateProvider.preferredLanguage(Config.getLocale());
    })

    .directive('onFinishRender', function ($timeout) {
        return {
            restrict: 'A',
            link: function (scope, element, attr) {
                if (scope.$last === true) {
                    $timeout(function () {
                        scope.$emit('ngRepeatFinished');
                    });
                }
            }
        }

    });
