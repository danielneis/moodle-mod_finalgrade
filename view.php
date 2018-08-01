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
 * Prints a particular instance of finalgrade
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_finalgrade
 * @copyright  2018 Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/finalgrade/lib.php');
require_once($CFG->libdir.'/gradelib.php');

$id = optional_param('id', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);

if ($id) {
    $cm         = get_coursemodule_from_id('finalgrade', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $finalgrade = $DB->get_record('finalgrade', array('id' => $cm->instance), '*', MUST_EXIST);
    $sourcecourse = $DB->get_record('course', array('id' => $finalgrade->course_for_grade), 'id,fullname', MUST_EXIST);
} else if ($n) {
    $finalgrade = $DB->get_record('finalgrade', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $finalgrade->course), '*', MUST_EXIST);
    $sourcecourse = $DB->get_record('course', array('id' => $finalgrade->course_for_grade), 'id,fullname', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('finalgrade', $finalgrade->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/finalgrade/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($finalgrade->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

echo $OUTPUT->heading($finalgrade->name);

$gradinginfo = grade_get_grades($course->id, 'mod', 'finalgrade', $finalgrade->id, $USER->id);
$str = new stdclass();
$str->coursename = $sourcecourse->fullname;
$str->coursegrade = $gradinginfo->items[0]->grades[$USER->id]->str_grade;
echo html_writer::tag('p', get_string('plugindescription', 'mod_finalgrade', $str));

if ($finalgrade->intro) {
    echo $OUTPUT->box(format_module_intro('finalgrade', $finalgrade, $cm->id), 'generalbox mod_introbox', 'finalgradeintro');
}

echo $OUTPUT->footer();
