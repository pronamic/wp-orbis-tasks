'use strict';

orbisApp.controller( 'OrbisTasksCtrl', function( $scope ) {
	$scope.tasks = [
		{
			text: 'Learn AngularJS',
			date: '2014-01-01',
			time: 1800,
			done: false
		},         
		{
			text: 'Build an app',
			date: '2014-02-02',
			time: 3600,
			done: false
		}
	];
  
	$scope.getTotalTasks = function() {
		return $scope.tasks.length;
	};

	$scope.addTask = function() {
		var time = 0;
		if ( angular.isNumber( $scope.formTaskHours ) ) {
			time += $scope.formTaskHours * 3600;
		}
		if ( angular.isNumber( $scope.formTaskMinutes ) ) {
			time += $scope.formTaskMinutes * 60;
		}

		var task = {
			text: $scope.formTaskText,
			date: $scope.formTaskDate,
			time: time,
			done: false
		};

		$scope.tasks.push( task );

		$scope.formTaskText = '';
	};
  
	$scope.clearCompleted = function() {
		$scope.tasks = $scope.tasks.filter( function( task ) {
			return ! task.done;
        } );
    };
} );
