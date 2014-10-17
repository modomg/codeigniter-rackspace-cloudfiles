Codeigniter Rackspace Cloudfiles
================================
An easy to use library (wrapper) that utilizes the Rackspace Open Cloud API (previously cloud files).

Installation
============
1. Install composer (https://getcomposer.org)
2. Run via terminal in project directory: composer install
3. Add the following to the top of your index.php: include_once './vendor/autoload.php';
4. Add 'cloudfiles' to your config array in application/config/autoload.php
5. Drop everything into your application folder
6. Edit the config file
7. Go to the controller and start playing around.

If you run into any problems with running it on your localhost or server, please make sure you check out the original Rackspace repo and documentation here: https://github.com/rackspace/php-opencloud

Versions
========
* **2.1** - Updated php-opencloud to 1.10.0, made initialize method more efficient when connecting to containers
* **2.0** - Full rewrite requiring the new Open Cloud API
* **1.2** - Added exception handling to cfiles.php library and updated the demo controller with new uses. You will still be able to use the functions as they were previously. Also un-commented some sections in the base API library - this means that you will be able to upgrade the base without making any additional changes.
* **1.1** - Upgraded the base cloud files API along with the demo controller up to CodeIgniter 2.0
* **1.0** - Initial Commit, no comments