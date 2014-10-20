'use strict';

orbisApp.controller( 'OrbisTasksCtrl', function( $scope, $http ) {
	$scope.tasks = [];

	$http.get( '/wp-admin/admin-ajax.php', {
		params: {
			action: 'orbis_get_tasks',
		}
	} ).success( function( data ) {
		$scope.tasks = data;
	} );
  
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
			project_id: $scope.formTaskProjectId,
			due_at: $scope.formTaskDueAt,
			time: time,
			done: false
		};

		$http.post( '/wp-admin/admin-ajax.php', task, {
			params: {
				action: 'orbis_add_task',
			}
		} ).success( function( data ) {
			$scope.tasks.push( data );

			$scope.formTaskText = '';
		} );
	};
  
	$scope.clearCompleted = function() {
		$scope.tasks = $scope.tasks.filter( function( task ) {
			return ! task.done;
        } );
    };
} );
