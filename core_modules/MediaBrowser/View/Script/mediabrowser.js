$J(document).ready(function () {
    // drag and drop overlay
    $J("html, .mediaBrowserMain").bind('dragover', dragover);
    $J('html, .mediaBrowserMain').bind('dragleave', dragleave);

    var tid;

    function dragover(event) {
        clearTimeout(tid);
        event.stopPropagation();
        event.preventDefault();
        $J('.modal-content').addClass('modal-drag-overlay');
    }

    function dragleave(event) {
        tid = setTimeout(function () {
            event.stopPropagation();
            $J('.modal-content').removeClass('modal-drag-overlay');

        }, 300);
    }

    //function filesDropped() { } -> in angular controller mainctrl
});


/* MEDIABROWSER ANGULARJS */
var mediaBrowserApp = angular.module('contrexxApp', ['ngRoute', 'plupload.module']);

mediaBrowserApp.config(['$routeProvider', '$locationProvider', function ($routeProvider) {
    $routeProvider.
        when('/uploader', {templateUrl: '/core_modules/MediaBrowser/View/Template/_Uploader.html', controller: 'UploaderCtrl'}).// todo adapt path
        when('/sitestructure', {templateUrl: '/core_modules/MediaBrowser/View/Template/_Sitestructure.html', controller: 'SitestructureCtrl'}).
        when('/filebrowser', {templateUrl: '/core_modules/MediaBrowser/View/Template/_FileBrowser.html', controller: 'MediaBrowserListCtrl'}).
        otherwise({redirectTo: '/uploader'});
}]);

mediaBrowserApp.service('uploadFileService', function () {
    return {
        files: [],
        addFiles:function (newFiles) {
            var length = newFiles.length;
            for (var i = 0; i < length; i++){
                this.files.push(newFiles[i]);
            }
        },
        getFiles: function(){
            return this.files;
        }
    };
});

/* CONTROLLERS */
mediaBrowserApp.controller('MainCtrl', ['$scope', '$rootScope', '$location', '$http',
    function ($scope, $rootScope, $location, $http) {
        // configuration 
        $rootScope.configuration = {selectmultiple: false};
        $rootScope.sources = [];

        // get files by json | todo: outsource in service & get everything in one json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getFiles&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
            $J(".loadingPlatform").hide();
            $J(".filelist").show();
            $rootScope.path = [
                {name: 'Dateien', path: 'files', standard: true}
            ]
            $rootScope.dataFiles = jsonadapter.data;
            $rootScope.files = $rootScope.dataFiles;
        });

        // get sites by json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getSites&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
            $rootScope.dataSites = jsonadapter.data;
            $rootScope.sites = $rootScope.dataSites;
        });

        // get sources by json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getSources&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
            $rootScope.sources = [];

            $rootScope.dataSources = jsonadapter.data;
            $rootScope.sources = $rootScope.dataSources;

            $scope.selectedSource = $rootScope.sources[0];
        });

        $rootScope.dataTabs = [
            {link: '#/uploader', label: 'Hochladen', icon: 'icon-upload'},
            {link: '#/filebrowser', label: 'Ablage', icon: 'icon-folder'},
            {link: '#/sitestructure', label: 'Seitenstruktur', icon: 'icon-sitestructure'}
        ];

        $rootScope.tabs = $rootScope.dataTabs;

        $scope.selectedTab = $rootScope.tabs[0];
        $scope.setSelectedTab = function (tab) {
            $scope.selectedTab = tab;
            $rootScope.changeLocation(tab.link);
        }

        // return active if is selected
        $scope.tabClass = function (tab) {
            if ($scope.selectedTab === tab) {
                return "active-tab";
            } else {
                return "not-active-tab";
            }
        }

        $scope.updateSource = function () {
            $rootScope.path = [
                {name: "" + $scope.selectedSource.name, path: $scope.selectedSource.value, standard: true}
            ];
            $J(".loadingPlatform").show();
            $J(".filelist").hide();
            $http.get('index.php?cmd=jsondata&object=MediaBrowser&mediatype=' + $scope.selectedSource.value + '&act=getFiles&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
                $J(".loadingPlatform").hide();
                $J(".filelist").show();
                $rootScope.dataFiles = jsonadapter.data;
                $rootScope.files = $rootScope.dataFiles;
            });

        };

        $rootScope.changeLocation = function (url, forceReload) {
            $scope = $scope || angular.element(document).scope();
            if (forceReload || $scope.$$phase) {
                window.location = url;
            }
            else {
                $location.path(url);
                $scope.$apply();
            }
        };

        $rootScope.go = function (path) {
            $rootScope.changeLocation(path, true);

            $rootScope.tabs.forEach(function (tab) {
                if (tab.link === path) {
                    $scope.selectedTab = tab;
                }
            });
        };


        $rootScope.getPathAsString = function () {
            var pathstring = '';
            //console.log($rootScope.path);
            $rootScope.path.forEach(function (path) {
                pathstring += path.path + '/';
            });
            return pathstring;
        }

        $rootScope.createFolder = function () {

            bootbox.prompt("Name of folder:", function (result) {
                if (result === null) {

                } else {
                    $http.get('index.php?cmd=jsondata&object=Uploader&act=createDir&path=' + $rootScope.getPathAsString() + '&dir=' + result + '&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
                        $scope.updateSource();
                    });
                }
            });
            //$rootScope.tabs.forEach(function(tab) {


            // client side:
        };


    }]);


mediaBrowserApp.controller('UploaderCtrl', ['$scope', '$rootScope', '$http', 'uploadFileService',
    function ($scope, $rootScope, $http, uploadFileService) {
        $scope.fileList = uploadFileService;

        $scope.$watch(uploadFileService, function() {
            alert('hey, myVar has changed!');
        });
        $rootScope.uploader.destroy();
        $rootScope.uploader = new plupload.Uploader({
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: 'selectFileFromComputer',
                container: 'mediaBrowserMain',
                drop_element: "mediaBrowserMain",
                url: '?csrf=' + cx.variables.get('csrf') + '&cmd=jsondata&object=Uploader&act=upload',
                flash_swf_url: '/lib/plupload/js/Moxie.swf',
                silverlight_xap_url: '/lib/plupload/js/Moxie.xap',
                filters: {
                    max_file_size: '50cxMb',
                    mime_types: [
                        {title: "Image files", extensions: "jpg,gif,png"},
                        {title: "Zip files", extensions: "zip"}
                    ]
                },
                multipart_params: {
                    "path": ''
                },
                init: {
                    PostInit: function () {
                        /*document.getElementById('uploadPlatform').innerHTML = '';

                         document.getElementById('uploadfiles').onclick = function() {
                         uploader.start();
                         return false;
                         };*/
                    },
                    FilesAdded: function (up, files) {

                        $rootScope.uploader.settings.multipart_params.path = $rootScope.getPathAsString();
                        //alert(JSON.stringify($rootScope.path));
//                        setTimeout(function () {
//                            up.start();
//                        }, 100);
                       // $J('.uploadStart').hide();
//                        $J('.uploadFilesAdded').show();
                        $J('.modal-content').removeClass('modal-drag-overlay');
                        uploadFileService.addFiles(files);

                        $scope.fileList = uploadFileService.files;
                        //console.log(uploadFileFactory.files);

//                        plupload.each(files, function (file) {
//                            $J(".uploadFiles").append('<li id="' + file.id + '">' + file.name + ' <b></b></li>');
//                        });
                    },
                    UploadProgress: function (up, file) {
                        $J(".uploadFiles b").html(file.percent + '%');
                        console.log(file.percent);
                        var $bar = $('.progress-bar');
                        $bar.width(file.percent + '%');
                        $bar.text(file.percent + "%");
                    },
                    Error: function (up, err) {
                        console.log("nError #" + err.code + ": " + err.message)
                    },
                    UploadComplete: function () {
                        //$rootScope.go('/filebrowser');
                    }
                }
            });

            $rootScope.uploader.init();

    }]);

mediaBrowserApp.controller('MediaBrowserListCtrl', ['$scope', '$rootScope', '$http',
    function ($scope, $rootScope, $http) {

        // tmp but necessary
        $scope.lastActiveFile = {};
        // __construct

        $scope.extendPath = function (dirName) {
            if (Array.isArray($rootScope.path)) {
                $rootScope.path.push({name: dirName, path: dirName, standard: false});
            }

            $scope.refreshBrowser();
        };
        $scope.shrinkPath = function (countDirs) {
            if (Array.isArray($rootScope.path)) {

                for (var i = 0; i < countDirs; i++) {
                    if ($rootScope.path[$rootScope.path.length - 1].standard === false)
                        $rootScope.path = $rootScope.path.slice(0, -1);
                }
            }
            $scope.refreshBrowser();
        };
        $scope.getPathString = function () {
            var returnValue = '/';
            $rootScope.path.forEach(function (pathpart) {
                returnValue += pathpart.path + '/';
            });
            return returnValue;
        };


        $scope.refreshBrowser = function () {
            $rootScope.files = $rootScope.dataFiles;
            $rootScope.path.forEach(function (pathpart) {
                if (!pathpart.standard) {
                    $rootScope.files = $rootScope.files[pathpart.path];
                }
            });
        };
        /* CLICK EVENTS */
        $scope.clickFile = function (thisDir, index) {
            if (thisDir.datainfo.extension === 'Dir') {
                $scope.extendPath(thisDir.datainfo.name);
            }
            else {
                if (thisDir.datainfo.active === true) {
                    thisDir.datainfo.active = false;
                }
                else {
                    if (!$scope.configuration.selectmultiple) {
                        console.log('ok');
                        if (!$J.isEmptyObject($scope.lastActiveFile))
                            $scope.lastActiveFile.datainfo.active = false;
                        $scope.lastActiveFile = thisDir;
                    }
                    thisDir.datainfo.active = true;
                }
            }
        };

        /* CLICK EVENTS */
        $scope.removeFile = function (file, index) {
            var message = '';
            if (file.datainfo.extension === 'Dir') {
                message = 'Möchten sie wirklich das Verzeichnis %s mit allen Dateien entfernen ?';
            }
            else {

                message = 'Möchten sie wirklich die Datei %s entfernen?';
            }
            bootbox.confirm(message.replace('%s', file.datainfo.name), function (result) {
                if (result === null) {

                } else {

                }
            });
        };

        $scope.clickPath = function (index) {
            shrinkby = $rootScope.path.length - index - 1;
            if (shrinkby > 0)
                $scope.shrinkPath(shrinkby);
        };
    }]);

mediaBrowserApp.controller('SitestructureCtrl', ['$scope', '$rootScope', '$http',
    function ($scope, $rootScope, $http) {
        // todo
    }]);


/* DIRECTIVES */
/* preview function */
mediaBrowserApp.directive('previewImage', function () {
    return {
        restrict: 'A',
        link: function (scope, el, attrs) {
            if (attrs.previewImage !== 'none') {
                $J(el).popover({
                    trigger: 'hover',
                    html: true,
                    content: '<img src="' + attrs.previewImage + '" />',
                    placement: 'right'
                });
            }
        }
    };
});

/* button to modal */
mediaBrowserApp.directive('cxMb', function () {
    return {
        restrict: 'A', // only work with elements including the attribute cxMb
        link: function (scope, el, attrs) {
            $J(el).click(function () {
                // sitestructure / filebrowser / uploader

                // cx-mb-views="sitestructure,uploader"
                // cx-mb-startmediatype="gallery"
                // cx-mb-startmediatype="gallery" cx-mb-mediatypes="files, gallery"
                // cx-mb-multipleselect="true" cx-mb-views="filebrowser"

                if (!attrs.cxMbStartview) {
                    attrs.$set('cxMbStartview', 'filebrowser');
                }

                if (!attrs.cxMbViews) {
                    attrs.$set('cxMbViews', 'all');
                }

                if (!attrs.cxMbStartmediatype) {
                    attrs.$set('cxMbStartmediatype', 'files');
                }

                if (!attrs.cxMbMediatypes) {
                    attrs.$set('cxMbMediatypes', 'all');
                }

                if (!attrs.cxMbMultipleselect) {
                    attrs.$set('cxMbMultipleselect', false);
                }

                // callbacks
                if (!attrs.cxMbCbJsModalopened) {
                    attrs.$set('cxMbCbJsModalopened', false);
                }
                if (!attrs.cxMbCbJsModalclosed) {
                    attrs.$set('cxMbCbJsModalclosed', false);
                }


                // cx-mb-mulipleselect
                scope.configuration.selectmultiple = attrs.cxMbMultipleselect;


                // cx-mb-startview | need to be placed before cx-mb-views!!
                scope.$apply("go('#/" + attrs.cxMbStartview + "')");

                // cx-mb-views
                if (attrs.cxMbViews !== 'all') {
                    var isStartviewInViews = false;
                    var views = attrs.cxMbViews;
                    views = views.trim();
                    var newTabNames = views.split(",");

                    newTabs = [];

                    newTabNames.forEach(function (newTabName) {
                        scope.dataTabs.forEach(function (tab) {
                            if (tab.link === '#/' + newTabName) {
                                if (newTabName === attrs.cxMbStartview)
                                    isStartviewInViews = true;
                                newTabs.push(tab);
                            }
                        });
                    });
                    scope.tabs = newTabs;
                    if (!isStartviewInViews)
                        scope.$apply("go('" + scope.tabs[0].link + "')");

                    if (newTabs.length === 1) {
                        $J(".mediaBrowserMain").addClass('no-nav');
                    }
                } else {
                    $J(".mediaBrowserMain").removeClass('no-nav');
                    scope.tabs = scope.dataTabs;
                }


                $J(".media-browser-modal").modal("show");

                if (attrs.cxMbCbJsModalopened !== false) {
                    var fn = window[attrs.cxMbCbJsModalopened];
                    var data = 'modalopened';
                    if (typeof fn === 'function')
                        fn(data);
                }


                $J('.media-browser-modal').on('hidden.bs.modal', function (e) {
                    scope.tabs = scope.dataTabs;
                    scope.configuration.selectmultiple = false;

                    if (attrs.cxMbCbJsModalclosed !== false) {
                        var fn = window[attrs.cxMbCbJsModalclosed];
                        var data = 'modalclosed';
                        if (typeof fn === 'function')
                            fn(data);
                    }
                });
            });
        }
    };
});
