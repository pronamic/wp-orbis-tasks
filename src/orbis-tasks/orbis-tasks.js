'use strict';

orbisApp.controller( 'OrbisTasksCtrl', function( $scope, $http ) {
	$scope.tasks = null;

	$http.get( '/wp-admin/admin-ajax.php', {
		params: {
			action: 'orbis_get_tasks',
		}
	} ).success( function( data ) {
		if ( data ) {
			angular.forEach( data, function( task ) {
				if ( task.due_at_string ) {
					task.due_at = new Date( task.due_at_string );
				}
			} );

			$scope.tasks = data;
		}
	} );
  
	$scope.getTotalTasks = function() {
		return $scope.tasks.length;
	};

	$scope.toggleTask = function( task ) {
		$http.post( '/wp-admin/admin-ajax.php', task, {
			params: {
				action: 'orbis_set_task_completed'
			}
		} ).success( function( data ) {
			
		} );
	}

	$scope.updateTask = function( task ) {console.log( task );
		$http.post( '/wp-admin/admin-ajax.php', task, {
			params: {
				action: 'orbis_set_task_due_at'
			}
		} ).success( function( data ) {
			
		} );
	}

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
			assignee_id: $scope.formTaskAssigneeId,
			due_at: $scope.formTaskDueAt,
			time: time,
			done: false
		};

		$http.post( '/wp-admin/admin-ajax.php', task, {
			params: {
				action: 'orbis_add_task',
			}
		} ).success( function( data ) {
			if ( data ) {
				task = data;

				if ( task.due_at_string ) {
					task.due_at = new Date( task.due_at_string );
				}

				$scope.tasks.push( task );

				$scope.formTaskText = '';
			}
		} );
	};
  
	$scope.clearCompleted = function() {
		$scope.tasks = $scope.tasks.filter( function( task ) {
			return ! task.done;
        } );
    };
} );
