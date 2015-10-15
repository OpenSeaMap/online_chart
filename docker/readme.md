# Docker helpers

This scripts allow to use Docker [1] to provide the nginx server.

You will need to have a working docker installation to proceed.

## Startup
To start the container run one of the following scripts. They will run docker
and save the container id to a file "container.id" for later use.

### Load datafiles from the online chart github repo [2]
    ./runContainer_git.sh
After the download of the image the container will pull the data from github.

### Use local files
In case you want to use the local files for development use this command:

    ./runContainer_localFiles.sh

It will bind the root of this repository (../) to the container. So nginx will
serve these files.

## Usage
After startup you can browse http://localhost:8080 to see your map.

## Update the data files
In case you want to update the git version (git pull) run this script:

    ./refreshGit.sh

In case you use our local files there is no need to execute anything.
The server will automatically serve your local files.

## Shutdown the container
To stop the server run

    ./stopContainer.sh

This script will delete the container.id file.



[1] https://www.docker.com/

[2] https://github.com/OpenSeaMap/online_chart
