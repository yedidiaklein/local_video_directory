require(['jquery', 'local_video_directory/select2'] , function($) {
    $('#id_owner').select2({
        minimumInputLength: 1, // only start searching when the user has input 3 or more characters
        ajax: {
            url : M.cfg.wwwroot + '/lib/ajax/service.php?sesskey=' + M.cfg.sesskey + '&info=local_video_directory_userslist',
            data : function ( d ) {
                return JSON.stringify({"0" : {"index":0,"methodname":"local_video_directory_userslist","args" : {"data": JSON.stringify(d)}}});
            },
            dataType : 'json',
            method : 'POST',
            processResults: function (d) {
                return {
                    results : JSON.parse(d[0].data)
                };
            },
          }
      });
});