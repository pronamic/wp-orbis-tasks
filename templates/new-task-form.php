<form action="" method="post">
	<?php wp_nonce_field( 'orbis_tasks_add_new_task', 'orbis_tasks_new_task_nonce' ); ?>

	<div class="panel">
		<div class="content">

			<legend>Taak toevoegen</legend>

			<div class="form-line clearfix">
				<div class="col pull-left">
					<label>Task</label>
					<input type="text" class="input-xxlarge important task-description" placeholder="Task description" name="orbis_task_description" />
				</div>

				<div class="col pull-left">
					<label>Tijd</label>
					<input type="text" class="input-mini important" placeholder="00:00" name="time">
				</div>
			</div>

			<label>Project</label>
			<input type="text" placeholder="Select project" name="project_id" class="orbis_company_id_field" />

			<label>Persoon</label>
			<input type="text" placeholder="Select person" name="person_id">

			<label>Datum</label>
			<input type="text" placeholder="dd-mm-yyyy" name="date">

			<div class="form-actions">
				<button class="btn btn-primary save" type="submit">Taak opslaan</button>
				<button data-target="#add-task" data-toggle="collapse" class="btn" type="button">Close</button>
			</div>

			<div id="result"></div><div id="output"></div>
		</div>
	</div>

	<p>
		<input type="text" placeholder="Select project" name="project_id" class="input-default orbis_company_id_field" />
	</p>
</form>