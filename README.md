This local plugin is for managing a video directory inside moodle.

The idea of this plugin is that every allowed user can upload any kind of video to the system. The plugin will convert them to streamable mp4 and generate thumbnails for it.

_Installation_

- Before installing make sure that you have ffmpeg installed on your server, if not - you can download it from here: [link](https://ffmpeg.org/download.html)
- This plugin was developed and tested on Linux with MySQL / MariaDB / Postgres - there's a chance that it'll not work well with other DB types.
- Get plugin files via GIT (from the root of your Moodle installation directory do: cd local; git clone https://github.com/yedidiaklein/local_video_directory.git video_directory) or download the zip file and extract it into your [moodle_dir]/local/
- Go to your moodle "Notification" page (http://moodle-address/admin)  and install the plugin by clicking on "Upgrade Moodle Database"
- It will be available from the Course Administration Block when you are inside a course.
- Direct URL access is via http://moodle-address/local/video_directory/
- You can allow non administrator users to access this plugin by adding them to the system role name local_video_directory.

_Usage_

There are a few ways to use this video in moodle:
1.  Using a symlink:
    a. Make sure that multimedia plugin (videojs) is enabled.
    b. Create a symlink from your moodle web root to the converted video root.
        i.e. ln -s /var/www/moodledata/local_video_directory_videos/converted/ /var/www/html/streaming
    c. Set a streaming address in video directory settings to : http[s]://your_moodle_address/streaming/
    d. After all that, you can copy the video link (first one) from video directory list page to any place in moodle (any atto editor, label, book, page, lesson etc..)
    e. Make sure it's a link (sometime you will have to click on the link button in atto) and the multimedia plugin will change it to a player.
    f. Note that you can set the default size of player in videojs settings.
    g. Second note, using this way allow EVERYONE (including non authenticated to moodle users) to view your videos - if they have the address.

2.  Using PHP pseudo streaming:
    a. Make sure that multimedia plugin (videojs) is enabled.
    b. Use video button in atto, click on it and copy second line of video link from list (the play.php one) into video address.
    c. This way do check that user is authenticated to moodle, but do not check that user is enrolled to the course where the video appear...

3.  Using Streaming server: (simple mp4 streaming)
    a. Make sure that multimedia plugin (videojs) is enabled.
    b. Set your streaming server to share at least the converted directory in [moodledata]/local_video_directory_videos/
    c. Set a streaming address in video directory settings to the address of your streaming server converted directory.
    d. After all that, you can copy the video link (first one) from video directory list page to any place in moodle (any atto editor, label, book, page, lesson etc..)
    e. Make sure it's a link (sometime you will have to click on the link button in atto) and the multimedia plugin will change it to a player.
    f. Note that you can set the default size of player in videojs settings.
    g. Second note, using this way allow EVERYONE (including non authenticated to moodle users) to view your videos - if they have the address.

4.  Using Streaming server: (Advanced dash/hls)
    a. For this you will have to set hls/dash streaming server (see blow)
    b. You will need to find dash/hls player for moodle.
    c. You will have to know how to generate the streaming address... (see below in streaming server configuration)
    d. You should also make sure that in video directory settings you set multi resolution encoding.

5. There is an easy way to add a video from this video directory using a special module - it is still in developemnt but can be tried from here :
    https://github.com/yedidiaklein/mod_videostream.git
   This way allow you easilly to add these files in symlink/php/dash/hls streaming. (setting a streaming server will be explained below...)
   This way also does not yet check that user has rights, and advanced users could find the streaming URL and use it without authentication at all.

_Streaming Server_

The intructions here are for linux, It should be similar in other OSes... I assume that you have git and gcc compiler installed..
We will use nginx and kaltura vod plugin.
wget http://nginx.org/download/nginx-1.14.0.tar.gz
git clone https://github.com/kaltura/nginx-vod-module.git
cd nginx-1.14.2
./configure --add-module=../nginx-vod-module/ --with-file-aio --with-threads \ --with-cc-opt="-O3"
make
make install

The default installation is going to /usr/local/nginx/
Now let’s configure NGINX to use DASH.
Main conf file is : /usr/local/nginx/conf/nginx.conf
My “serer” section looks like this: 
    server {
        listen       80;
        server_name  localhost;

#vod
	        vod_mode local;
	        vod_last_modified 'Sun, 19 Nov 2000 08:52:00 GMT';
	        vod_last_modified_types *;

#cache
	       	vod_metadata_cache metadata_cache 512m;
	        vod_response_cache response_cache 128m;
#gzip
	        gzip on;
		gzip_types application/vnd.apple.mpegurl video/f4m application/dash+xml text/xml;

# file cache and aio
	        open_file_cache          max=1000 inactive=5m;
	        open_file_cache_valid    2m;
	        open_file_cache_min_uses 1;
	        open_file_cache_errors   on;
	        aio on;

        location / {
            root   /usr/local/nginx/html/;
            index  index.html index.htm
        }

	location /dash/ {
		root /usr/local/nginx/html/;
		vod dash;

			vod_segment_duration 4000;
			vod_bootstrap_segment_durations 3500;
			vod_align_segments_to_key_frames on;
			vod_dash_manifest_format segmenttemplate;
			vod_multi_uri_suffix multiuri;
			
			add_header Last-Modified "Sun, 19 Nov 2000 08:52:00 GMT";
			add_header Access-Control-Allow-Headers "origin,range,accept-encoding,referer";
			add_header Access-Control-Expose-Headers "Server,range,Content-Length,Content-Range";
			add_header Access-Control-Allow-Methods "GET, HEAD, OPTIONS";
			add_header Access-Control-Allow-Origin "*";
			expires 100d;

	}

### End of configuration

Save and start nginx, make sure that it’s listening on port 80 using this command
netstat -lnp | grep nginx

Create a systemd startup script.
See : https://www.nginx.com/resources/wiki/start/topics/examples/systemd/ for centos7/ubuntu
Change binary path (/usr/local/nginx…)
And set pid file in nginx.conf to fit your systemd settings !!!

Media
For having real mpeg dash we’ve to encode our file to every quality that is wanted.
Let’s get a  file from the web i.e. :
wget http://trailers.apple.com/movies/magnolia_pictures/harry-benson-shoot-first/harry-benson-shoot-first-trailer-1_h1080p.mov
mv harry-benson-shoot-first-trailer-1_h1080p.mov input.mov
Now, using ffmpeg we’ll convert it to some resolutions:
1080 (orig) , 720, 648 and 360… you can continue if u want...

ffmpeg -i input.mov -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut'  movie_1080.mp4
ffmpeg -i input.mov -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut' -vf scale=-2:720  movie_720.mp4
ffmpeg -i input.mov -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut' -vf scale=-2:648  movie_648.mp4
ffmpeg -i input.mov -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut' -vf scale=-2:360  movie_360.mp4

Put all encoded files in /usr/local/nginx/html/dash directory (I’m using this directory as symlink to another name for having at the same time access to these files using plain http way.

Now we have to build the mpeg-dash link, according to kaltura vod module it should be like this:
The structure of a multi URL is: http://<domain>/<location>/<prefix>,<middle1>,<middle2>,<middle3>,<postfix>.urlset/<filename>
The sample URL above represents 3 URLs:
http://<domain>/<location>/<prefix><middle1><postfix>/<filename>
http://<domain>/<location>/<prefix><middle2><postfix>/<filename>
http://<domain>/<location>/<prefix><middle3><postfix>/<filename>
The suffix .urlset (can be changed with vod_multi_uri_suffix) indicates that the URL should be treated as a multi URL.
So for our files it’s: (assuming our server is http://streaming/)
http://streaming/dash/movie_,1080,720,648,360,.mp4multiuri/manifest.mpd
Then test it using:
wget -O- http://streaming/dash/movie_,1080,720,648,360,.mp4multiuri/manifest.mpd
If you see an XML on the screen - you are on the right way!
