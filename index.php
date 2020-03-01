<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1"><!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

	<title>IGB SLURM Script Generator</title><!-- Bootstrap -->

	<script src="vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="vendor/select2/select2/dist/js/select2.min.js" type="text/javascript"></script>

	<link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="vendor/select2/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css">
	<link href="vendor/intelogie/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="includes/css/main.inc.css" rel="stylesheet" type="text/css">

</head>

<body>
	<?php
		$json_data = file_get_contents('includes/config.json');
		$config = json_decode($json_data,true);
	?>
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<h1>SLURM Script Generator</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<legend>Available Queues</legend>
				<table class="table table-bordered table-condensed">
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
				<div class="form-horizontal">
					<fieldset>
						<legend>Choose a Queue</legend>
						<?php
							$checked = " checked";
							foreach($config['queues'] as $queue){
								echo '<div class="form-group"><label class="col-sm-4">'.$queue['name'].' queue</label> <div class="col-sm-8"><input type="radio" class="queue_radio" name="queue" value="'.$queue['name'].'"'.$checked.' onchange="generateScript();" /> </div></div>';
								$checked = "";
							}
						?>
					</fieldset>
					<fieldset>
						<legend>Allocate Resources</legend>
						<div class="form-group">
							<label class="col-sm-4">CPU (cores)</label>
							<div class="col-sm-8">
								<select id="cpu" class="form-control select2_dropdown" onchange="generateScript();">
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4">Memory (GB)</label>
							<div class="col-sm-8">
								<select id="memory" class="form-control select2_dropdown" onchange="generateScript();">
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4">Nodes</label>
							<div class="col-sm-8">
								<select id="nodes" class="form-control select2_dropdown" onchange="generateScript();">
								</select>
							</div>
						</div>
						<div class="form-group" id='gpu-group'>
							<label class="col-sm-4">GPUs</label>
							<div class="col-sm-8">
								<select id="gpu" class="form-control select2_dropdown" onchange="generateScript();">
								</select>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<legend>Modules/Commands</legend>
						<div class="form-group">
							<label class="col-sm-4">Modules to load</label>
							<div class="col-sm-8">
								<select id='modules' multiple class='select2_dropdown form-control' onchange="generateScript();">
									<?php
										$lines = preg_split('/ +/',file_get_contents('http://biocluster2.igb.illinois.edu/apps.txt'));
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
												echo "<option value='{$module}' >{$module}</option>";
									        }
									    }
									    ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4">Commands to run</label>
							<div class="col-sm-8">
								<textarea class="form-control" id="commands" rows="2" placeholder="formatdb -p F -i all_seqs.fasta -n customBLASTdb" onkeyup="generateScript();"></textarea>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<legend>Recommended Settings</legend>
						<div class="form-group">
							<label class="col-sm-4">Email</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="email" id="email" onkeyup="generateScript();"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4">Job Name</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="jobname" id="jobname" onkeyup="generateScript();"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4">Working directory</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="workingdir" id="workingdir" onkeyup="generateScript();" placeholder="/full/path/to/directory/"/>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-horizontal">
					<fieldset>
						<legend>SLURM Script</legend>
						<textarea id="slurm" class="form-control" rows="16" readonly></textarea>
					</fieldset>
					<fieldset>
						<legend>Optional Settings</legend>
						<div class="form-group">
							<label class="col-sm-4">Std out file</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="stdout" id="stdout" onkeyup="generateScript();"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4">Std err file</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="stderr" id="stderr" onkeyup="generateScript();"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4">Project</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="project" id="project" onkeyup="generateScript();"/>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
		</div>
	</div>
	
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<script type="text/javascript">
		$.fn.select2.defaults.set( "theme", "bootstrap" );
		$.fn.select2.defaults.set( "width", null );
		
		var config = {};
		$.ajax('includes/config.json',{
			async: false,
			success: function(data){
				config = data;
			}
		});
		function populateResourceDropdowns(){
			var queue = $('.queue_radio:checked').val();
			for(var i=0;i<config.queues.length;i++){
				if(config.queues[i].name == queue){
					var $cpu = $('#cpu');
					$cpu.empty();
					for(var j=1;j<=config.queues[i].cpu;j++){
						$cpu.append('<option value="'+j+'">'+j+'</option>');
					}
					var $memory = $('#memory');
					$memory.empty();
					for(var j=1;j<=config.queues[i].memory;j++){
						$memory.append('<option value="'+j+'">'+j+'</option>');
					}
					var $nodes = $('#nodes');
					$nodes.empty();
					for(var j=1;j<=config.queues[i].nodes;j++){
						$nodes.append('<option value="'+j+'">'+j+'</option>');
					}
					
					var $gpugroup = $('#gpu-group');
					if(config.queues[i].hasOwnProperty('gpu')){
						var $gpus = $('#gpu');
						$gpus.empty();
						for(var j=1; j<=config.queues[i].gpu; j++){
							$gpus.append('<option value="'+j+'">'+j+'</option>');
						}
						$gpugroup.css('display','block');
					} else {
						$gpugroup.css('display', 'none');
					}
				}
			}
			generateScript();
		}
		$('.queue_radio').on('change',populateResourceDropdowns);
		
		function generateScript(){
			// Grab Queue
			var queue = $('.queue_radio:checked').val();
			
			var queueStr = "#SBATCH -p "+queue+"\n";
			// Grab resources
			var cpu = $('#cpu').val();
			var memory = $('#memory').val();
			var nodes = $('#nodes').val();
			var gpu = null;
			if($('#gpu-group').css('display') == 'block'){ gpu = $('#gpu').val(); }
			
			var cpuStr = "#SBATCH -n "+cpu+"\n";
			var memStr = "#SBATCH --mem="+memory+"g\n";
			var nodesStr = "#SBATCH -N "+nodes+"\n";
			var gpuStr = "";
			if(gpu != null){ gpuStr = "#SBATCH --gres=gpu:"+gpu+"\n"; }
			
			// Grab modules
			var modules = $('#modules').select2('val');
			
			var modulesStr = "";
			if(modules != null){
				for(var i=0;i<modules.length;i++){
					modulesStr += "module load "+modules[i].replace(/\(default\)/,"")+"\n";
				}
			}
			
			// Grab commands
			var commands = $('#commands').val();
			
			var commandsStr = commands+"\n";
			
			// Recommended settings
			var email = $('#email').val();
			var jobname = $('#jobname').val();
			var workingdir = $('#workingdir').val();
			
			var emailStr = email==""?"":"#SBATCH --mail-user="+email+"\n#SBATCH --mail-type=ALL\n";
			var jobnameStr = jobname==""?"":"#SBATCH -J "+jobname+"\n";
			var workingdirStr = workingdir==""?"":"#SBATCH -D "+workingdir+"\n";
			
			// Optional settings
			var stdout = $('#stdout').val();
			var stderr = $('#stderr').val();
			var account = $('#project').val();
			
			var stdoutStr  = stdout=="" ?"":"#SBATCH -o "+stdout+"\n";
			var stderrStr  = stderr=="" ?"":"#SBATCH -e "+stderr+"\n";
			var accountStr = account==""?"":"#SBATCH -A "+account+"\n";
			
			var script = "#!/bin/bash\n"+
			"# ----------------SLURM Parameters----------------\n"+
			queueStr+
			cpuStr+
			memStr+
			gpuStr+
			nodesStr+
			emailStr+
			jobnameStr+
			workingdirStr+
			stdoutStr+
			stderrStr+
			accountStr+
			"# ----------------Load Modules--------------------\n"+
			modulesStr+
			"# ----------------Commands------------------------\n"+
			commandsStr;
			
			$('#slurm').val(script);
		}

		$('.select2_dropdown').select2();
		populateResourceDropdowns();
	</script>
</body>
</html>
