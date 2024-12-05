<?php
	require_once 'includes/app.inc.php';
	$json_data = file_get_contents('includes/config.json');
	$config = json_decode($json_data,true);

	$title = __DEFAULT_TITLE__;
	if (isset($config['config']['title'])) {
		$title = $config['config']['title'];
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?php echo $title; ?></title>

	<script src="vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="vendor/select2/select2/dist/js/select2.min.js" type="text/javascript"></script>
	<script src="includes/js/slurm.js" type="text/javascript"></script>

	<link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="vendor/select2/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css">
	<link href="includes/css/main.inc.css" rel="stylesheet" type="text/css">

</head>

<body>
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<h1>SLURM Script Generator</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<legend>Available Queues</legend>
				<table class="table table-bordered table-sm">
					<tr><th>Queue Name</th><th>CPUs</th><th>Memory</th><th>Nodes</th><th>GPUs</th></tr>
				<?php
					foreach($config['queues'] as $queue){
						echo '<tr>';
						echo '<td>'.$queue['name'].'</td>';
						echo '<td>'.$queue['cpu'].'</td>';
						echo '<td>'.$queue['memory'].'</td>';
						echo '<td>'.$queue['nodes'].'</td>';
						echo '<td>'; if(isset($queue['gpu'])){ echo $queue['gpu']; } echo '</td>';
						echo '</tr>';
					}
				?>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
					<fieldset>
						<legend>Choose a Partition</legend>
						<?php
							$checked = " checked";
							foreach($config['queues'] as $queue){
								echo "<div class='form-check'>";
								echo "<input type='radio' class='queue_radio form-check-input' name='queue' value='" . $queue['name'] . "'" . $checked . " onchange='generateScript();' />";
								echo "&nbsp;<label class='form-check-label'>" . $queue['name'] . "</label>";
								echo "</div>";
								$checked = "";
							}
						?>
					</fieldset>
					<fieldset>
						<legend>Allocate Resources</legend>
						<div class="row">
							<label class="col-sm-4">CPU (cores)</label>
							<div class="col-sm-8">
								<select id="cpu" class="form-select select2_dropdown" onchange="generateScript();">
								</select>
							</div>
						</div>
						&nbsp;
						<div class="row">
							<label class="col-sm-4">Memory (GB)</label>
							<div class="col-sm-8">
								<select id="memory" class="form-select select2_dropdown" onchange="generateScript();">
								</select>
							</div>
						</div>
						&nbsp;
						<div class="row">
							<label class="col-sm-4">Nodes</label>
							<div class="col-sm-8">
								<select id="nodes" class="form-select select2_dropdown" onchange="generateScript();">
								</select>
							</div>
						</div>
						&nbsp;
						<div id='gpu-group'>
						<div class="row">
							<label class="col-sm-4">GPUs</label>
							<div class="col-sm-8">
								<select id="gpu" class="form-select select2_dropdown" onchange="generateScript();">
								</select>
							</div>
						</div>
						</div>
					</fieldset>
					&nbsp;
					<fieldset>
						<legend>Modules/Commands</legend>
						<div class="row">
							<label class="col-sm-4">Modules to load</label>
							<div class="col-sm-8">
								<select id='modules' multiple class='select2_dropdown form-select' onchange="generateScript();">
									<?php
										$lines = preg_split('/ +/',file_get_contents($config['config']['apps_url']));
										$software = array();
									    foreach($lines as $line){
									        $line = trim($line);
									        $matches = preg_split("/\s+/",$line);
									        foreach($matches as $match){
									              array_push($software,($match));
									        }
									    }
									    sort($software);
									    
									    foreach( $software as $module){
									        if(! (preg_match('/modules|^-|^ -|\/$/',$module)) ){
												echo "<option value='{$module}' >{$module}</option>\n";
									        }
									    }
									    ?>
								</select>
							</div>
						</div>
						&nbsp;
						<div class="row">
							<label class="col-sm-4">Commands to run</label>
							<div class="col-sm-8">
								<textarea class="form-control" id="commands" rows="2" placeholder="formatdb -p F -i all_seqs.fasta -n customBLASTdb" onkeyup="generateScript();"></textarea>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<legend>Recommended Settings</legend>
						<div class="row">
							<label class="col-sm-4">Email</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="email" id="email" onkeyup="generateScript();"/>
							</div>
						</div>
						&nbsp;
						<div class="row">
							<label class="col-sm-4">Job Name</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="jobname" id="jobname" onkeyup="generateScript();"/>
							</div>
						</div>
						&nbsp;
						<div class="row">
							<label class="col-sm-4">Working directory</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="workingdir" id="workingdir" onkeyup="generateScript();" placeholder="/full/path/to/directory/"/>
							</div>
						</div>
					</fieldset>
			</div>
			<div class="col-sm-6">
					<fieldset>
						<legend>SLURM Script</legend>
						<textarea id="slurm" class="form-control" rows="16" readonly></textarea>
					</fieldset>
					<fieldset>
						<legend>Optional Settings</legend>
						<div class="row">
							<label class="col-sm-4">Std out file</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="stdout" id="stdout" onkeyup="generateScript();"/>
							</div>
						</div>
						&nbsp;
						<div class="row">
							<label class="col-sm-4">Std err file</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="stderr" id="stderr" onkeyup="generateScript();"/>
							</div>
						</div>
						&nbsp;
						<div class="row">
							<label class="col-sm-4">Project</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="project" id="project" onkeyup="generateScript();"/>
							</div>
						</div>
					</fieldset>
			</div>
		</div>
	</div>
	&nbsp;	
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<script type="text/javascript">
		$.fn.select2.defaults.set( "theme", "bootstrap-5" );
		$.fn.select2.defaults.set( "width", "resolve" );
		
		$('.queue_radio').on('change',populateResourceDropdowns);
		

		$('.select2_dropdown').select2();
		populateResourceDropdowns();
	</script>
</body>
</html>
