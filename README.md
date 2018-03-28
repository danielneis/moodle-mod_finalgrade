Final Grade Activity Plugin for Moodle
--------------------------------------

This activity imports it's grades from another course final grade.

With this you can have a course with multiple activities pointing to multiple courses and use the gradebook to aggregate them.

Install
-------

* Put these files at moodle/mod/finalgrade/
 * You may use composer
 * or git clone
 * or download the latest version from https://github.com/danielneis/moodle-mod_finalgrade/archive/master.zip
* Log in your Moodle as Admin and go to "Notifications" page
* Follow the instructions to install the plugin

Usage
-----

This plugin will appear as an activity called "finalgrade".

When you add the activity, you must provide the name for the activity, an optional description, and select the course you want to import final grades from. 

With the activity added to course, it will appear on the grade book, bringing the final grades from the selected course.

Everytime a grade is changed on the selected course, the activity will be updated with the new grades.


Dev Info
--------

Please, report issues at: https://github.com/danielneis/moodle-mod_finalgrade/issues

Feel free to send pull requests at: https://github.com/danielneis/moodle-mod_finalgrade/pulls

[![Travis-CI Build Status](https://travis-ci.org/danielneis/moodle-mod_finalgrade.svg?branch=master)](https://travis-ci.org/danielneis/moodle-mod_finalgrade)
