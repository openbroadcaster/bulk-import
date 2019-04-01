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
    $('#media_data_middle').after(OB.UI.getHTML('media/addedit_metadata.html'));
    OB.Media.mediaAddeditForm(1, header);
    $('.copy_to_all').hide();
    $('.new_media_only').hide();
    $('.title_field').val('<import filename>').prop('disabled', true);
    
    OB.API.post('bulkimport', 'load_settings', {}, function (response) {
      console.log(response);
      
      $.each(response.data.directories, function (key, dir) {
        $('#bulk_import_' + key).val(dir);
      });
      
      $.each(response.data.settings, function (key, setting) {
        switch (key) {
          case 'artist':
            $('#bulk_import_settings .artist_field').val(setting);
            break;
          case 'album':
            $('#bulk_import_settings .album_field').val(setting);
            break;
          case 'year':
            $('#bulk_import_settings .year_field').val(setting);
            break;
          case 'category_id':
            $('#bulk_import_settings .category_field').val(setting);
            OB.Media.updateGenreList(1);
            $('#bulk_import_settings .genre_field').val(response.data.settings.genre_id);
            break;
          case 'country_id':
            $('#bulk_import_settings .country_field').val(setting);
            break;
          case 'language_id':
            $('#bulk_import_settings .language_field').val(setting);
            break;
          case 'comments':
            $('#bulk_import_settings .comments_field').val(setting);
            break;
          case 'is_copyright_owner':
            $('#bulk_import_settings .copyright_field').val(setting);
            break;
          case 'is_public':
            $('#bulk_import_settings .public_field').val(setting);
            break;
          case 'is_approved':
            $('#bulk_import_settings .approved_field').val(setting);
            break;
          case 'status':
            $('#bulk_import_settings .status_field').val(setting);
            break;
          case 'dynamic_select':
            $('#bulk_import_settings .dynamic_select_field').val(setting);
            break;
          case 'permission_users':
            $('#bulk_import_settings .advanced_permissions_users_field').val(setting);
            break;
          case 'permission_groups':
            $('#bulk_import_settings .advanced_permissions_groups_field').val(setting);
            break;
          default:
            if (key.startsWith('metadata')) {
              $('#bulk_import_settings .' + key + '_field').val(setting);
            }            
            break;
        }
      });
    });
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
      post.category_id = $('.category_field').val();
    }
    if ($('#bulk_import_settings .genre_field').is(':visible')) {
      post.genre_id = $('.genre_field').val();
    }
    if ($('#bulk_import_settings .country_field').is(':visible')) {
      post.country_id = $('.country_field').val();
    }
    if ($('#bulk_import_settings .language_field').is(':visible')) {
      post.language_id = $('.language_field').val();
    }
    if ($('#bulk_import_settings .comments_field').is(':visible')) {
      post.comments = $('.comments_field').val();
    }
    
    post.is_copyright_owner = $('#bulk_import_settings .copyright_field').val();
    post.is_public = $('#bulk_import_settings .public_field').val();
    post.is_approved = $('#bulk_import_settings .approved_field').val();
    post.status = $('#bulk_import_settings .status_field').val();
    post.dynamic_select = $('#bulk_import_settings .dynamic_select_field').val();
    
    post.permission_users  = $('#bulk_import_settings .advanced_permissions_users_field').val();
    post.permission_groups = $('#bulk_import_settings .advanced_permissions_groups_field').val()
    
    $.each(OB.Settings.media_metadata, function (index, metadata) {
      post['metadata_' + metadata.name] = 
        $('#bulk_import_settings .metadata_' + metadata.name + '_field').val();
    });
    
    OB.API.post('bulkimport', 'update_settings', post, function (response) {
      if (!response.status) {
        $('#bulk_import_message').obWidget('error', response.msg);
      } else {
        $('#bulk_import_message').obWidget('success', response.msg);
      }
    });
  }
}