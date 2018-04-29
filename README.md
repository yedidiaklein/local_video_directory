This local plugin is for managing a video directory inside moodle.

The idea of this plugin is that every allowed user can upload any kind of video to the system. The plugin will convert it to mp4 and generate thumbnails for it.
The easiest and best way to use it is with mod_videostream that can be found here : https://github.com/yedidiaklein/mod_videostream.git

_Installation_

- Before installing make sure that you have ffmpeg installed on your server, if not - you can download it from here: [link](https://ffmpeg.org/download.html)
- This plugin was developed and tested on Linux with MySQL / MariaDB / Postgres - there's a chance that it'll not work well with other DB types.
- Get plugin files via GIT (from the root of your Moodle installation directory do: cd local; git clone https://github.com/yedidiaklein/local_video_directory.git video_directory) or download the zip file and extract it into your [moodle_dir]/local/
- Go to your moodle "Notification" page (http://moodle-address/admin)  and install the plugin by clicking on "Upgrade Moodle Database"
- It will be available from the Course Administration Block when you are inside a course.
- Direct URL access is via http://moodle-address/local/video_directory/
- You can allow non administrator users to access this plugin by adding them to the system role name local_video_directory.

