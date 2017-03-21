var local_video_directory;

require(['jquery'], function($) {
	local_video_directory = {
		getThumb: function(id, second) {
			$.get(M.cfg.wwwroot + '/local/video_directory/ajax_thumbs.php?id=' + id + '&second=' + second, function(data){
				local_video_directory.ChangeRBText("id_thumb_" + second,
					data == 'noimage' ?
					local_video_directory_vars.errorcreatingthumbat + ' ' + second + ' s':
					 "<img class='thumb' height='80px' src='" + data + "'>"
				);
				
				if (data == 'noimage') {
					$('#id_thumb_' + second).hide();
				}
			});
		},
		ChangeRBText: function (rbId, html) {
			$('#' + rbId).next().html(html);
		}
	}
	
	for (second in local_video_directory_vars.seconds) {
		var s = local_video_directory_vars.seconds[second];
		// change default text to loading gif
		local_video_directory.ChangeRBText('id_thumb_' + s, "<img class='thumb' src='" + M.cfg.wwwroot + "/local/video_directory/pix/loading36.gif'>");
		// ajax get the thumbnail 
		local_video_directory.getThumb(local_video_directory_vars.id, s);
	}
})
