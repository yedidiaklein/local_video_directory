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
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('locallib.php');
$selected = basename($_SERVER['SCRIPT_NAME']);
$settings=get_config('local_video_directory');
?>
<div class="alert alert-default alert-block" role="alert">
    <?php echo get_string('freedisk', 'local_video_directory') . ': ' . local_video_directory_human_filesize(disk_free_space($CFG->dataroot), 2, $settings->df) ?>
</div>
<ul id='videomenu'>
<?php
$menu = array('list', 'upload', 'mass', 'wget');

foreach ($menu as $item) {
    if ($item . '.php' == $selected) {
        echo '<li id="selected"><a href="' . $item . '.php">' . get_string($item, 'local_video_directory') . '</a></li>';
    } else {
        echo '<li><a href="' . $item . '.php">' . get_string($item, 'local_video_directory') . '</a></li>';
    }
}
?>
</ul>
<br>