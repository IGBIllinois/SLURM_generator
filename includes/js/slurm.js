//$.fn.select2.defaults.set( "theme", "bootstrap" );
//$.fn.select2.defaults.set( "width", null );
		
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
//$('.queue_radio').on('change',populateResourceDropdowns);
		
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

//$('.select2_dropdown').select2();
//populateResourceDropdowns();
