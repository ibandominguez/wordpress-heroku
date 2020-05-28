jQuery(function($){
    var mediaUploader;
    $('.file-uploader-field > .button-primary').click(function(e) {
        e.preventDefault();
        var input_id = $( this ).data('file');
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose File',
            button: {
                text: 'Choose'
            }, multiple: false });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $( '#' + input_id ).val(attachment.url);
        });
        mediaUploader.open();
    });
    
    
    
    $('button.advanced-cpt-settings-btn').click(function(e) {
        e.preventDefault();
        $('.advanced-cpt-settings').css("display", "flex");
        $( this ).hide();
        $('button.normal-cpt-settings-btn').show();
    });
    
    $('button.normal-cpt-settings-btn').click(function(e) {
        e.preventDefault();
        $('.advanced-cpt-settings').css("display", "none");
        $( this ).hide();
        $('button.advanced-cpt-settings-btn').show();
    });
});