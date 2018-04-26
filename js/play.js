require.config({catchError:true});

var local_video_directory = {
    play: function(stream) {
        var video = document.getElementById('my-video'), source = document.createElement('source');
        document.getElementById('video_player').style.display = 'block';
        video.pause();
        source.setAttribute('src', stream);
        if (video.childElementCount == 1) {
            video.replaceChild(source,video.childNodes[0]);
        } else {
            video.appendChild(source);
        }

        video.load();
        video.play();
    },
    close_player: function() {
        event.preventDefault();
        document.getElementById('my-video').pause();
        document.getElementById('video_player').style.display = 'none';
    }
};
