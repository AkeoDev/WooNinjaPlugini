jQuery(document).ready(function($){
    var mediaUploader;
    $('#upload_image_button').click(function(e) {
      e.preventDefault();
        if (mediaUploader) {
        mediaUploader.open();
        return;
      }
      mediaUploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose Image',
        button: {
        text: 'Choose Image'
      }, multiple: false });
      mediaUploader.on('select', function() {
        var attachment = mediaUploader.state().get('selection').first().toJSON();
        $('#logo_image_email').val(attachment.url);
      });
      mediaUploader.open();
    });
  });

  jQuery( document ).ready( function( $ ) {
    $('.email_color_border' ).wpColorPicker();
    $('.email_cololor_button_link' ).wpColorPicker(); 
    $('.email_color_bg' ).wpColorPicker(); 
    $('.email_color_typography' ).wpColorPicker(); 
    $('.wp-color-result-text').text('Izberi barvo');
    $('.wp-picker-clear').val('Izbriši');
} );

