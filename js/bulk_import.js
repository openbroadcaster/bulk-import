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

    OBModules.BulkImport.loadOverview();
  }

  this.loadOverview = function () {
    OB.API.post('bulkimport', 'load_overview', {}, function (response) {
      $('#bulk_import_directories tbody').empty();

      $.each(response.data, function (key, dir) {
        var $row = $('<tr/>').attr('data-id', dir.id);
        $row.append($('<td/>').html(dir.name));
        $row.append($('<td/>').html(dir.dir_source));
        $row.append($('<td/>').html(dir.description));
        $row.append($('<td/>').html('<span><button class="edit" onclick="OBModules.BulkImport.editBulkDirectory(' + dir.id + ')">Edit</button><button class="delete" onclick="OBModules.BulkImport.deleteBulkDirectory(' + dir.id + ')">Delete</button></span>'));
        $('#bulk_import_directories tbody').append($row);
      });
    });
  }

  this.addBulkDirectory = function () {
    OB.UI.openModalWindow('modules/bulk_import/bulk_import_addedit.html');

    $('#bulk_import_isnew').val('true');
    OBModules.BulkImport.getMediaForm('Imported Media Settings', null);
  }

  this.editBulkDirectory = function (id) {
    OB.UI.openModalWindow('modules/bulk_import/bulk_import_addedit.html');

    $('#bulk_import_isnew').val('false');
    $('#bulk_import_id').val(id);
    OBModules.BulkImport.getMediaForm('Imported Media Settings', id);
  }

  this.deleteBulkDirectory = function (id) {
    OB.UI.confirm({
      text: "Are you sure you want to delete this bulk import directory?",
      okay_class: "delete",
      callback: function () {
        OBModules.BulkImport.deleteBulkDirectoryConfirm(id);
      }
    });
  }

  this.deleteBulkDirectoryConfirm = function (id) {
    OB.API.post('bulkimport', 'delete_settings', {id: id}, function (response) {
      var msg_result = (response.status ? 'success' : 'error');
      $('#bulk_import_message').obWidget(msg_result, response.msg);

      OBModules.BulkImport.loadOverview();
    });
  }

  this.getMediaForm = function (header, id) {
    $('#media_data_middle').after(OB.UI.getHTML('media/addedit_metadata.html'));
    OB.Media.mediaAddeditForm(1, header);
    $('.copy_to_all').hide();
    $('.new_media_only').hide();
    $('#bulk_import_settings .addedit_form_legend legend').hide();
    $('.title_field').val('<import filename>').prop('disabled', true);

    if (id != null) {
      OB.API.post('bulkimport', 'load_settings', {id: id}, function (response) {
        $('#bulk_import_name').val(response.data.name);
        $('#bulk_import_description').val(response.data.description);
        $('#bulk_import_dir_source').val(response.data.dir_source);
        $('#bulk_import_dir_failed').val(response.data.dir_failed);
        $('#bulk_import_dir_target').val(response.data.dir_target);

        $.each(JSON.parse(response.data.id3), function (key, value) {
          $('#bulk_import_id3_' + key).prop('checked', value);
        });

        $.each(JSON.parse(response.data.settings), function (key, setting) {
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
  }

  this.updateSettings = function () {
    var post = {};
    post.dir_source  = $('#bulk_import_dir_source').val();
    post.dir_failed  = $('#bulk_import_dir_failed').val();
    post.dir_target  = $('#bulk_import_dir_target').val();
    post.isnew       = $('#bulk_import_isnew').val();
    post.id          = $('#bulk_import_id').val();
    post.name        = $('#bulk_import_name').val();
    post.description = $('#bulk_import_description').val();

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

    post.advanced_permissions_users  = $('#bulk_import_settings .advanced_permissions_users_field').val();
    post.advanced_permissions_groups = $('#bulk_import_settings .advanced_permissions_groups_field').val()

    post.id3_artist   = $('#bulk_import_id3_artist').is(':checked');
    post.id3_album    = $('#bulk_import_id3_album').is(':checked');
    post.id3_title    = $('#bulk_import_id3_title').is(':checked');
    post.id3_comments = $('#bulk_import_id3_comments').is(':checked');

    $.each(OB.Settings.media_metadata, function (index, metadata) {
      post['metadata_' + metadata.name] =
        $('#bulk_import_settings .metadata_' + metadata.name + '_field').val();
    });

    OB.API.post('bulkimport', 'update_settings', post, function (response) {
      if (!response.status) {
        $('#bulk_import_addedit_message').obWidget('error', response.msg);
      } else {
        OB.UI.closeModalWindow();
        OBModules.BulkImport.loadOverview();
        $('#bulk_import_message').obWidget('success', response.msg);
      }
    });
  }
}
