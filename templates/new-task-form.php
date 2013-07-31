<form action="" method="post" class="form-horizontal">
	<?php wp_nonce_field( 'orbis_tasks_add_new_task', 'orbis_tasks_new_task_nonce' ); ?>

	<div class="panel">
		<div class="content">

			<legend>Taak toevoegen</legend>

			<div class="control-group">
				<label class="col-lg-2 control-label">Task</label>

				<div class="controls form-line clearfix">
					<div class="col pull-left">
						<label>Task</label>
						<input type="text" class="input-xxlarge important task-description" placeholder="Task description" name="orbis_task_description" />
					</div>

					<div class="col pull-left">
						<label>Tijd</label>
						<input type="text" class="input-mini important" placeholder="00:00" name="time" />
					</div>
				</div>
			</div>

			<div class="control-group">
				<label class="col-lg-2 control-label" for="task-project-id-field">Project</label>

				<div class="col-lg-10 controls">
					<input type="text" placeholder="Select project" id="task-project-id-field" name="project_id" class="input-xlarge orbis-id-control orbis-project-id-control" />
				</div>
			</div>

			<div class="control-group">
				<label class="col-lg-2 control-label" for="task-person-id-field">Persoon</label>

				<div class="col-lg-10 controls">
					<input type="text" placeholder="Select person" id="task-person-id-field" name="person_id" class="input-large orbis-id-control orbis-person-id-control" />
				</div>
			</div>

			<div class="control-group">
				<label class="col-lg-2 control-label">Datum</label>

				<div class="col-lg-10 controls">
					<input type="text" placeholder="dd-mm-yyyy" name="date" class="form-control" />
				</div>
			</div>

			<div class="form-actions">
				<button class="btn btn-primary save" type="submit">Taak toevoegen</button>
			</div>

			<div id="result"></div><div id="output"></div>
		</div>
	</div>
</form>