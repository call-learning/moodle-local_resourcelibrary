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
 * Behat data generator for local_resourcelibrary.
 *
 * @category    test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();



/**
 * Behat data generator for resource library
 *
 * @package    local_resourcelibrary
 * @category    test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_quiz_generator extends behat_generator_base {

    protected function get_creatable_entities(): array {
        return [
            'course category' => [
                'datagenerator' => 'resourcelibrary',
                'required' => ['quiz', 'group'],
                'switchids' => ['quiz' => 'quiz', 'group' => 'groupid'],
            ],
            'course field' => [
                'datagenerator' => 'resourcelibrary',
                'required' => ['quiz', 'user'],
                'switchids' => ['quiz' => 'quiz', 'user' => 'userid'],
            ],
            'activity category' => [
                'datagenerator' => 'resourcelibrary',
                'required' => ['quiz', 'group'],
                'switchids' => ['quiz' => 'quiz', 'group' => 'groupid'],
            ],
            'activity field' => [
                'datagenerator' => 'override',
                'required' => ['quiz', 'user'],
                'switchids' => ['quiz' => 'quiz', 'user' => 'userid'],
            ],
        ];
    }

    /**
     * Look up the id of a quiz from its name.
     *
     * @param string $quizname the quiz name, for example 'Test quiz'.
     * @return int corresponding id.
     */
    protected function get_quiz_id(string $quizname): int {
        global $DB;

        if (!$id = $DB->get_field('quiz', 'id', ['name' => $quizname])) {
            throw new Exception('There is no quiz with name "' . $quizname . '" does not exist');
        }
        return $id;
    }
}
