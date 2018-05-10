define(['jquery'], function($) {
    return {
        play: function(stream, videotag) {
            var $video = $(videotag);
            var video = $video[0];
            var container = $video.closest('.local_video_directory_video-player'),
                source = $('<source>').attr('src', stream);
            container.show();
            video.pause();
            $video.empty().append(source);
            video.load();
            video.play();
        },
        closePlayer: function(container) {
            container = $(container);
            event.preventDefault();
            container.find('video')[0].pause();
            container.hide();
        }
    };
});
