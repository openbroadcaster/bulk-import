OBModules.BulkImport = new function () {
  this.init = function () {
    OB.Callbacks.add('ready', 0, OBModules.BulkImport.initMenu);
  }
  
  this.initMenu = function () {
    OB.UI.addSubMenuItem('admin', 'Bulk Import', 'bulk_import',
                         OBModules.BulkImport.open, 152);
  }
  
  this.open = function () {
    OB.UI.replaceMain('modules/bulk_import/bulk_import.html');
    
    OBModules.BulkImport.getMediaForm('Imported Media Settings');
  }
  
  this.getMediaForm = function (header) {
    OB.Media.mediaAddeditForm(1, header);
    $('.copy_to_all').hide();
    $('.new_media_only').hide();
    $('.title_field').val('<import filename>').prop('disabled', true);
    
    $('#media_data_middle').after(OB.UI.getHTML('media/addedit_metadata.html'));
  }
  
  this.updateSettings = function () {
    var post = {};
    post.dir_source = $('#bulk_import_dir_source').val();
    post.dir_failed = $('#bulk_import_dir_failed').val();
    post.dir_target = $('#bulk_import_dir_target').val();
    
    if ($('#bulk_import_settings .artist_field').is(':visible')) {
      post.artist = $('#bulk_import_settings .artist_field').val(); 
    }
    if ($('#bulk_import_settings .album_field').is(':visible')) {
      post.album = $('.album_field').val();
    }
    if ($('#bulk_import_settings .year_field').is(':visible')) {
      post.year = $('.year_field').val();
    }
    if ($('#bulk_import_settings .category_field').is(':visible')) {
      post.category = $('.category_field').val();
    }
    if ($('#bulk_import_settings .genre_field').is(':visible')) {
      post.genre = $('.genre_field').val();
    }
    if ($('#bulk_import_settings .country_field').is(':visible')) {
      post.country = $('.country_field').val();
    }
    if ($('#bulk_import_settings .language_field').is(':visible')) {
      post.language = $('.language_field').val();
    }
    if ($('#bulk_import_settings .comments_field').is(':visible')) {
      post.comments = $('.comments_field').val();
    }
    
    post.is_copyright = $('#bulk_import_settings .copyright_field').val();
    post.is_public = $('#bulk_import_settings .public_field').val();
    post.is_approved = $('#bulk_import_settings .approved_field').val();
    post.status = $('#bulk_import_settings .status_field').val();
    post.is_dynamic = $('#bulk_import_settings .dynamic_select_field').val();
    
    
    // TODO: Debug metadata fields not showing up, check addedit.js and 
    // other code to make sure it can find the data.
    $.each(OB.Settings.media_metadata, function (index, metadata) {
      post['metadata_' + metadata.name] = 
        $('#bulk_import_settings .metadata_' + metadata.name + '_field').val();
    });
    
    // TODO: Actually handle data.
    console.log(post);
  }
}