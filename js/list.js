require.config({catchError:true});
require(['jquery', 'jqueryui', 'datatables'], function($, jqueryui, datatables) {
    $( document ).ready(function() {
        var table = $("#video_table").DataTable({
            "ajax": {
                "url": M.cfg.wwwroot + '/local/video_directory/ajax_list.php',
                "dataSrc": ""
            },
            "order": [[2, "desc"]],
            "columns": [
                {"data": "actions"},
                {"data": "thumb"},
                {"data": "id"},
                {"data": "name"},
                {"data": "orig_filename"},
                {"data": "filename"},
                {"data": "length"},
                {"data": "convert_status"},
                {"data": "private"},
                {"data": "streaming_url"},
                {"data": "tags"}
            ]
        });
        
        $('#datatable_ajax_reload').click(function(){
            table.ajax.reload();
        });

        $('#datatable_ajax_clear_tags').click(function(){
            window.location = 'list.php';
        });

        $('#video_table').on('change', '.ajax_edit', function () {
            var data = this.id.split('_');
            var field = this.type == 'checkbox' ? 'private' : 'orig_filename';
            var id = data.pop();
            var status = this.type == 'checkbox' ? this.checked : null;
            var value = this.type == 'checkbox' ? null : this.value;
            $.post(M.cfg.wwwroot + '/local/video_directory/ajax_edit.php', {field: field, id: id, value: value, status: status}, function (data){
                // do nothing.
            })
            .fail(function() {
                alert("error");
            });
        });

        $('.play_video').click(function () {
            $("#video_player").show();
        });
    });
});

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
}
