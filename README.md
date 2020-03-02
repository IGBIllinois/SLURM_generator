SLURM Generator
===============

This tool helps users generate SLURM batch scripts for the Carl R. Woese Institute for Genomic Biology's Biocluster [https://biocluster.igb.illinois.edu](https://biocluster.igb.illinois.edu).  Allows them to interactively build their SLURM script. As job parameters are entered into the form, the SLURM script is generated in real time.

## Installation
* Git Clone the repository or download a tag release.  Place in a web accessable folder
```
git clone https://github.com/IGBIllinois/SLURM_generator.git
```
* Copy includes/config.json.dist to includes/config.json
```
cp includes/config.json.dist includes/config.json
```
## Configuration
* Edit includes/config.json for the page title and location of apps.txt
```
"config": {
	"title": "Slurm Script Generator",
	"apps_url": "http://localhost/apps.txt"
},
```
* The apps.txt can be generated for Lmod with the following command
```
module -t avail > /var/www/html/apps.txt
```
* Edit the queueus for your cluster specifications
 
```
{
	"name": "normal",
	"cpu": 24,
	"memory": 384,
	"nodes": 10
},
```
