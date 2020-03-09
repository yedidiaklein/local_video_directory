# Video Directory for Moodle

This local plugin is for managing a video directory from within Moodle.

The idea of this plugin is that every allowed user can upload any kind of video to the system. The plugin will convert it to streamable mp4 and generate thumbnails for it.

## Installation

- Before installing make sure that you have ffmpeg installed on your server, if not - you can download it from here: [link](https://ffmpeg.org/download.html)
- This plugin was developed and tested on Linux and Windows with MySQL / MariaDB / Postgres - there's a chance that it'll not work well with other DB types.
- Get plugin files via GIT (from the root of your Moodle installation directory do: cd local; git clone https://github.com/yedidiaklein/local_video_directory.git video_directory) or download the zip file and extract it into your [moodle_dir]/local/
- Go to your moodle "Notification" page (http://[moodle-address]/admin)  and install the plugin by clicking on "Upgrade Moodle Database"
- It will be available from the Course Administration Block when you are inside a course.
- Direct URL access is via http://[moodle-address]/local/video_directory/
- You can allow non-administrator users to access this plugin by adding them to the system role "local_video_directory".

## Usage

There are a few ways to use this video in moodle:

1.  Using a symlink:

	a. Make sure that [multimedia plugin (videojs)](https://docs.moodle.org/34/en/VideoJS_player) is enabled.
    
	b. Create a symlink from a directory accessible from the web - for example: your moodle www root directory - to the directory that will hold all the converted videos - this is the "local_video_directory_videos/converted" that is below your moodledata directory.
    
	So, for example, if your moodledata directory is "/var/www/moodledata" and your moodle wwwroot directory is "/var/www/html", then in linux you would, from the server command line, execute the command:
	
	`ln -s /var/www/moodledata/local_video_directory_videos/converted/ /var/www/html/streaming`
	
	In Windows, if your moodledata directory is "C:\xampp\moodledata" and your moodle wwwroot directory is "C:\xampp\htdocs" you would, from the command line, execute:
	
	`mklink /D C:\xampp\htdocs\streaming C:\xampp\moodledata\local_video_directory_videos/converted`
	
	c. Set the streaming address in the video directory settings to : http(s)://[moodle-address]/streaming/ (replace "[moodle-address]" with your actual moodle web address)
	
	d. Once you have uploaded videos using the interface, you can then copy the link from the video directory list page (use the first link in the row) to any place in rich text editor in moodle (for example, the atto editor fields in activities of type "label", "book", "page", "lesson" etc.)
	
	e. If it's a legal link to a video (in Google Chrome, for example, you can test it by pasting directly into the browser address bar, pressing "enter" and seeing if it plays) and the multimedia plugin will automatically change it to a media player widget.
	
	f. Note that you can set the default size of player in videojs settings.
	
	g. Note also that using this method allows ANYONE with the video link (including non authenticated moodle users) to view your videos.

2.  Using PHP pseudo streaming:

	a. Make sure that multimedia plugin (videojs) is enabled.
	
	b. Use video button in atto, click on it and copy second line of video link from list (the play.php one) into video address.
	
	c. This way do check that user is authenticated to moodle, but do not check that user is enrolled to the course where the video appear.

3.  Using Streaming server: (simple mp4 streaming). Please note that this solution requires prior knowledge of the operation of a streaming server:

    a. Make sure that [multimedia plugin (videojs)](https://docs.moodle.org/34/en/VideoJS_player) is enabled.
	
    b. Set your streaming server to share the converted directory in [moodledata]/local_video_directory_videos/
	
    c. Set a streaming address in video directory settings to the address of your streaming server converted directory.
	
    d. Once you have uploaded videos using the interface, you can then copy the link from the video directory list page (use the first link in the row) to any place in rich text editor in moodle (for example, the atto editor fields in activities of type "label", "book", "page", "lesson" etc.)
	
    e. If it's a legal link to a video (in Google Chrome, for example, you can test it by pasting directly into the browser address bar, pressing "enter" and seeing if it plays) and the multimedia plugin will automatically change it to a media player widget.
	
    f. Note that you can set the default size of player in videojs settings.
	
    g. Note also that using this method allows ANYONE with the video link (including non authenticated moodle users) to view your videos.

4.  Using Streaming server: (Advanced dash/hls):

    a. For this you will have to set hls/dash streaming server (see below)
	
    b. You will need to find dash/hls player for moodle.
	
    c. You will have to know how to generate the streaming address... (see below in streaming server configuration)
	
    d. You should also make sure that in video directory settings you set multi resolution encoding.

5. There is an easy way to add a video from this video directory using a special module - it is still in developemnt but can be tried from here:

    https://github.com/yedidiaklein/mod_videostream.git
	
   This way allows you to easily add these files in symlink/php/dash/hls streaming (setting a streaming server will be explained below).
   This way also does not yet check that user has rights, and advanced users could find the streaming URL and use it without authentication at all.

### Streaming Server

The intructions here are for linux. It should be similar in other OSes. It is assumed that you have git and gcc compiler installed.

We will use nginx and kaltura vod plugin.

```
wget http://nginx.org/download/nginx-1.16.1.tar.gz 

git clone https://github.com/kaltura/nginx-vod-module.git

cd nginx-1.16.1

./configure --add-module=../nginx-vod-module/ --with-file-aio --with-threads --with-cc-opt="-O3" --with-http_ssl_module

make

make install
```

The default installation is going to /usr/local/nginx/

Now let’s configure NGINX to use DASH.

Main conf file is : /usr/local/nginx/conf/nginx.conf

The "server" section should look like this:

```
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
```

Save, and start nginx and make sure that it’s listening on port 80 using this command:

`netstat -lnp | grep nginx`

Create a systemd startup script.

See : [https://www.nginx.com/resources/wiki/start/topics/examples/systemd/] for centos7/ubuntu

Change binary path (/usr/local/nginx) and set pid file in nginx.conf to correspond to your systemd settings.

#### Media

For having real mpeg dash we need to encode our file to every desired quality level.

Let’s get a  file from the web i.e. :

```
wget http://trailers.apple.com/movies/magnolia_pictures/harry-benson-shoot-first/harry-benson-shoot-first-trailer-1_h1080p.mov

mv harry-benson-shoot-first-trailer-1_h1080p.mov input.mov
```

Now, using ffmpeg we’ll convert it to some resolutions, for example: 1080 (original), 720, 648 and 360.

```
ffmpeg -i input.mov -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut'  movie_1080.mp4
ffmpeg -i input.mov -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut' -vf scale=-2:720  movie_720.mp4
ffmpeg -i input.mov -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut' -vf scale=-2:648  movie_648.mp4
ffmpeg -i input.mov -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut' -vf scale=-2:360  movie_360.mp4
```

Put all encoded files in /usr/local/nginx/html/dash directory (I’m using this directory as symlink to another name for having at the same time access to these files using plain http way).

Now we have to build the mpeg-dash link. according to kaltura vod module it should be like this:

  The structure of a multi URL is: http://&lt;domain>/&lt;location>/&lt;prefix>,&lt;middle1>,&lt;middle2>,&lt;middle3>,&lt;postfix>.urlset/&lt;filename>

The sample URL above represents 3 URLs:

  http://&lt;domain>/&lt;location>/&lt;prefix>&lt;middle1>&lt;postfix>/&lt;filename>

  http://&lt;domain>/&lt;location>/&lt;prefix>&lt;middle2>&lt;postfix>/&lt;filename>

  http://&lt;domain>/&lt;location>/&lt;prefix>&lt;middle3>&lt;postfix>/&lt;filename>

The suffix .urlset (can be changed with vod_multi_uri_suffix) indicates that the URL should be treated as a multi URL.

So if our streaming root URL is http://streaming/, then for our files it’s:

  http://streaming/dash/movie_,1080,720,648,360,.mp4multiuri/manifest.mpd

Then test it using:

`wget -O- http://streaming/dash/movie_,1080,720,648,360,.mp4multiuri/manifest.mpd`

If you see an XML on the screen then you are good to go.
