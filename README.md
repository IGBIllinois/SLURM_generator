SLURM Generator
===============

This tool helps users generate SLURM batch scripts for the Carl R. Woese Institute for Genomic Biology's Biocluster [https://biocluster.igb.illinois.edu](https://biocluster.igb.illinois.edu).  Allows them to interactively build their SLURM script. As job parameters are entered into the form, the SLURM script is generated in real time.

## Installation
* Copy includes/config.json.dist to includes/config.json
```
cp includes/config.json.dist includes/config.json
```
* Edit includes/config.json for your cluster specifications.  
```
		{
                        "name": "normal",
                        "cpu": 24,
                        "memory": 384,
                        "nodes": 10
                },
```
