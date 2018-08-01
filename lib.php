<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for module finalgrade
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the finalgrade specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_finalgrade
 * @copyright  2018 Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/grade/querylib.php');

// Moodle core API.

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function finalgrade_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
          return true;
        case FEATURE_GRADE_HAS_GRADE:
          return true;
        default:
          return null;
    }
}

/**
 * Saves a new instance of the finalgrade into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $finalgrade An object from the form in mod_form.php
 * @param mod_finalgrade_mod_form $mform
 * @return int The id of the newly inserted finalgrade record
 */
function finalgrade_add_instance(stdClass $finalgrade, mod_finalgrade_mod_form $mform = null) {
    global $DB;

    $finalgrade->timecreated = time();

    $finalgrade->id = $DB->insert_record('finalgrade', $finalgrade);
    finalgrade_grade_item_update($finalgrade);

    return $finalgrade->id;
}

/**
 * Updates an instance of the finalgrade in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $finalgrade An object from the form in mod_form.php
 * @param mod_finalgrade_mod_form $mform
 * @return boolean Success/Fail
 */
function finalgrade_update_instance(stdClass $finalgrade, mod_finalgrade_mod_form $mform = null) {
    global $DB;

    $finalgrade->timemodified = time();
    $finalgrade->id = $finalgrade->instance;

    $DB->update_record('finalgrade', $finalgrade);

    finalgrade_update_grades($finalgrade);
    finalgrade_grade_item_update($finalgrade);

    return true;
}

/**
 * Removes an instance of the finalgrade from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function finalgrade_delete_instance($id) {
    global $DB;

    if (!$finalgrade = $DB->get_record('finalgrade', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('finalgrade', array('id' => $finalgrade->id));

    finalgrade_grade_item_delete($finalgrade);

    return true;
}

/**
 * Delete grade item for given finalgrade
 *
 * @category grade
 * @param stdClass $finalgrade Finalgrade object
 * @return grade_item
 */
function finalgrade_grade_item_delete($finalgrade) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/finalgrade', $finalgrade->course, 'mod', 'finalgrade',
                        $finalgrade->id, 0, null, array('deleted' => 1));
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function finalgrade_user_outline($course, $user, $mod, $finalgrade) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $finalgrade the module instance record
 * @return void, is supposed to echp directly
 */
function finalgrade_user_complete($course, $user, $mod, $finalgrade) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in finalgrade activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function finalgrade_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link finalgrade_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function finalgrade_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see finalgrade_get_recent_mod_activity()}

 * @return void
 */
function finalgrade_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function finalgrade_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function finalgrade_get_extra_capabilities() {
    return array();
}

// Gradebook API.

/**
 * Is a given scale used by the instance of finalgrade?
 *
 * This function returns if a scale is being used by one finalgrade
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $finalgradeid ID of an instance of this module
 * @return bool true if the scale is used by the given finalgrade instance
 */
function finalgrade_scale_used($finalgradeid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('finalgrade', array('id' => $finalgradeid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of finalgrade.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any finalgrade instance
 */
function finalgrade_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('finalgrade', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give finalgrade instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $finalgrade instance object with extra cmidnumber and modname property
 * @return void
 */
function finalgrade_grade_item_update($finalgrade) {
    global $CFG, $DB;
    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir.'/gradelib.php');
    }

    $sql = "SELECT gi.*
              FROM {grade_items} gi
             WHERE gi.courseid = ?
               AND gi.itemtype = 'course'";
    $item = (array)$DB->get_record_sql($sql, array($finalgrade->course_for_grade));
    $item['itemname'] = $finalgrade->name;
    unset($item['id']);

    grade_update('mod/finalgrade', $finalgrade->course, 'mod', 'finalgrade', $finalgrade->id, 0, null, $item);
}

/**
 * Update finalgrade grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $finalgrade instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function finalgrade_update_grades(stdClass $finalgrade, $userid = 0) {
    global $CFG, $DB;

    $grades = grade_get_course_grades($finalgrade->course_for_grade, $userid);

    grade_update('mod/finalgrade', $finalgrade->course, 'mod', 'finalgrade', $finalgrade->id, 0, $grades->grades);
}

// File API.

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function finalgrade_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for finalgrade file areas
 *
 * @package mod_finalgrade
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function finalgrade_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the finalgrade file areas
 *
 * @package mod_finalgrade
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the finalgrade's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function finalgrade_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

// Navigation API.

/**
 * Extends the global navigation tree by adding finalgrade nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the finalgrade module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function finalgrade_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the finalgrade settings
 *
 * This function is called when the context for the page is a finalgrade module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $finalgradenode {@link navigation_node}
 */
function finalgrade_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $finalgradenode=null) {
}

function finalgrade_grade_regrade_final_grades($eventdata) {
    global $DB;

    $sql = "SELECT * FROM finalgrade WHERE course_for_grade = {$eventdata->courseid}";
    if ($modules = $DB->get_records_sql($sql)) {

        $gradegrades = grade_grade::fetch_users_grades($eventdata->updateditem, array($eventdata->userid), true);

        foreach ($modules as $m) {
            foreach ($gradegrades as $gg) {
                $grades = array('userid' => $gg->userid, 'rawgrade' => $gg->rawgrade, 'rawgrade' => $gg->finalgrade);
                grade_update('mod/finalgrade', $m->course, 'mod', 'finalgrade', $m->id, 0, $grades);
            }
        }
    }
}
