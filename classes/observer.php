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
 * Event observers used in forum.
 *
 * @package    mod_forum
 * @copyright  2018 Daniel Neis Araujo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class mod_finalgrade_observer {
    /**
     * Triggered via user_graded event.
     *
     * @param \core\event\user_graded $event
     */
    public static function final_grades_regraded(\core\event\user_graded $event) {
        global $DB;

        // If we have a finalgrade module instance that import grades from the updated course.
        $sql = "SELECT * FROM {finalgrade} WHERE course_for_grade = ?";
        $courseid = $event->get_context()->get_course_context()->instanceid;
        if ($modules = $DB->get_records_sql($sql, array($courseid))) {

            $grade_item =  grade_item::fetch(array('id' => $event->other['itemid']));
            $grade_grades = grade_grade::fetch_users_grades($grade_item, array($event->relateduserid), true);

            foreach ($modules as $m) {
                foreach ($grade_grades as $gg) {
                    $grades = array('userid' => $gg->userid, 'rawgrade' => $gg->rawgrade, 'rawgrade' => $gg->finalgrade);
                    grade_update('mod/finalgrade', $m->course, 'mod', 'finalgrade', $m->id, 0, $grades);
                }
            }
        }
    }
}
