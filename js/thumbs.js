var local_video_directory;

require(['jquery', 'core/ajax'], function($, ajax) {
    $('.mform[action$="thumbs.php"] input[type="radio"]').parent().addClass('local_video_directory_thumbselectorelement');
    local_video_directory = {
        getThumb: function(id,second) {
            var promises = ajax.call([
                { methodname: 'local_video_directory_thumb', args: { videoid: id, seconds: second } }
            ]);

            promises[0].done(function(data){
                local_video_directory.ChangeRBText(
                        second,
                        data === 'noimage'
                            ?
                            local_video_directory_vars.errorcreatingthumbat + ' ' + second + ' s'
                            :
                            "<img class='local_video_directory_thumb' height='80px' src='" + data + "'>"
                );

                if (data === 'noimage') {
                    $('#id_thumb_' + second).hide();
                }
            });
        },
        ChangeRBText: function (rbId, html) {
            $('#video_thumb_' + rbId).html(html);
        }
    }

    for (second in local_video_directory_vars.seconds) {
        var s = local_video_directory_vars.seconds[second];
        // Change default text to loading gif.
        local_video_directory.ChangeRBText(s,
            "<img class='local_video_directory_thumb' src='" + M.cfg.wwwroot + "/local/video_directory/pix/loading36.gif'>");
        // Ajax get the thumbnail.
        local_video_directory.getThumb(local_video_directory_vars.id, s);
    }
});
