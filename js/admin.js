var conf = {
	base:'/any-rest'
};

var admin = angular.module('admin', ['ngResource'])
	.config(function($routeProvider, $locationProvider, $compileProvider){
		  $compileProvider.urlSanitizationWhitelist(/^\s*(https?|ftp|mailto|file|tel):/);
		$locationProvider.html5Mode(true);
		$routeProvider
		.otherwise({controller:ListCtrl, templateUrl: conf.base +'/view/listClients.html'});
	}).directive('markdown', function() {
    return {
        restrict: 'A',
        replace: true,
        link: function(scope, element, attrs) {

            scope.$watch('cont', function(n, p) {
            	if (n !== undefined) {
	            	var converter = new Showdown.converter();
	            	var htmlText = converter.makeHtml(n);
		           	var tmp = element.html(htmlText);
		           	if (typeof Prism == 'object'){
		           		Prism.highlightAll();	
		           	}
	           	} else {
	           		element.html(htmlText);
	           	}
            });
        },
        scope: {
        	cont:'='
        }
    }
});


function ListCtrl($scope, $resource, $timeout) {
	var Client = $resource(conf.base +'/api/client/:id');

	$scope.clients = Client.query();

	$scope.new = {};
	$scope.log = function() {
		console.log($scope);
	}
	$scope.submit = function() {
	 	var client = new Client({name: $scope.new.name});
	 	client.$save(function(u){
	 		$scope.clients.push(u);
	 	});
	}
	$scope.remove = function(u){
			if (u.n !== 0) {
				var index = $scope.clients.indexOf(u);
				$scope.clients.splice(index, 1);
			} 
		}
}

