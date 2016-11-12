This local plugin is for managing a video directory inside moodle.

The idea of this plugin is that every allowed user can upload any kind of video to the system. The system convert it to mp4 and generate thumbnails.

There's another reposiroty plugin, that works w/ this directory - and allow adding easilly video to everywhere using atto and media button.

_Installation_

- Before installing make sure that you have ffmpeg installed on your server, if not - please reffer your distro way to install it...
- This plugin was developped and tested on linux w/ mysql/mariadb DB - there's a good chance that it'll not work well on other environment.
- Get plugin files via git or download and extract them in your [moodle_dir]/local/
- Go to your moodle "Notification" page
- Install the plugin
- Access it via http://moodle-address/local/video_directory/
- You can allow non administrator users to access this plugin by adding them to the cohort that is set in settings.

