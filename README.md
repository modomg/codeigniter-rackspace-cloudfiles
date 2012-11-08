Codeigniter Rackspace Cloudfiles
================================
An easy to use library that utilizes the Rackspace Cloudfiles API. 

Mercurial: https://bitbucket.org/modomg/codeigniter-rackspace-cloudfiles
Git: https://github.com/modomg/codeigniter-rackspace-cloudfiles

Welcome
=======

I made this as an extension to the Rackspace Cloudfiles API along with some changes to it. You can read the original blog post here: http://www.syracusecs.com/rackspace-cloud-files-api-with-codeigniter/.

Right now this is just some basic functions, but I will be actively supporting this and adding things as I go along. If you have any requests or changes, please feel free to leave me a request in the "issues" tab for me.

I've made an easy to follow controller with a number of examples. If you need any other ones, please let me know.

-Chris

Installation
============
1. Drop everything into your application folder
2. Edit the config file
3. Go to the controller and start playing around.

If you run into any problems with running it on your localhost or server, please make sure you check out the original API documentation here: https://github.com/rackspace/php-cloudfiles.

Versions
========
* **1.2** - Added exception handling to cfiles.php library and updated the demo controller with new uses. You will still be able to use the functions as they were previously. Also un-commented some sections in the base API library - this means that you will be able to upgrade the base without making any additional changes.
* **1.1** - Upgraded the base cloud files API along with the demo controller up to CodeIgniter 2.0
* **1.0** - Initial Commit, no comments