#######Setup Process#########

1) Launch the script docker.ecr.app.imageBase.build to create the centos7 base image from which we will later create our app image.
2) Launch the script dockerup.sh like this
	./dockerup.sh dev
This will set up the whole stack
3) go to the local_frontend folder and execute docker-compose up -d to start our local frontend logic

####Develop process

I've developed this from Windows using a Fedora Workstation virtual machine, sharing the app folder with Samba and using PHPStorm as my IDE


#App

Create Recipe: I've created a sample function to create a recipe, which can be executed with the route base_url/add
Modify Recipe: when you go to each recipe page, you will find a small icon at the bottom right side representing a pork's ham. When pressed, this will make our recipe "less vegan" by adding
pork to the ingredients and a new tag. This was made to illustrate the update functionality.


###git flow
So, I've two main branches, develop and master. I'm the only person working on this project, and as I can focus just in one task at a time (bug,feature or whatever) I can be working straight into develop. However, 
if more people were to join the project, the idea would be to have the branch develop as the core, and sub branches for all the issues. For example, I have created a new Issue-XXXX from develop to 
solve a bug. After the fix was tested and I was happy with the solution I merge it into develop. Once we have a bunch of tickets to be released, we can create a new branch from develop, called release_vx.x.x for example, change the version of the application in there, deploy to LIVE and then merge into master and into develop that release branch, so that master gets the new code and the new version and develop gets the new version as well.


####API Usage####

get '/'
post '/update'
post '/delete'
get '/add' -> should be post, but for this example we will allow the user to create a sample hardcoded recipe from the url
get '/search/[{name}]' 
     name can follow the below format	
     * title:query
     * description:query
     * ingredients:query
     * directions:query
     * prep_time_min:query
     * cook_time_min:query
     * servinges:query
     * tags:query
     * author:query
     * query (without filter) try to match in any field


###TODO
- When we first visit the application, an error will show up saying that no handler is available for type text in author. Once we reload everything is fine, this need to be corrected.
- Apply swagger input codes in the classes to generate documentation


