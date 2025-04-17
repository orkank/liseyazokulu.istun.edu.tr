sortable("#<%$uploader.uniq%>_uploaded", { forcePlaceholderSize: true });

sortable('#<%$uploader.uniq%>_uploaded')[0].addEventListener('sortupdate', function(e) {

    console.log(e.detail.origin.items);
    var data = [];

    $.each(e.detail.origin.items, function(index, e) {
      data.push($(e).data('id'));
    });

    $.post('rpc.php', {action: "images-sort", group: '<%$module.group%>', data: data, id: '<%if isset($data.id)%><%$data.id%><%/if%>'}, function(response){
      if(response.status == 'ok') {

      } else {

      }
    }, 'json');
    /*
    This event is triggered when the user stopped sorting and the DOM position has changed.

    e.detail.item - {HTMLElement} dragged element

    Origin Container Data
    e.detail.origin.index - {Integer} Index of the element within Sortable Items Only
    e.detail.origin.elementIndex - {Integer} Index of the element in all elements in the Sortable Container
    e.detail.origin.container - {HTMLElement} Sortable Container that element was moved out of (or copied from)
    e.detail.origin.itemsBeforeUpdate - {Array} Sortable Items before the move
    e.detail.origin.items - {Array} Sortable Items after the move

    Destination Container Data
    e.detail.destination.index - {Integer} Index of the element within Sortable Items Only
    e.detail.destination.elementIndex - {Integer} Index of the element in all elements in the Sortable Container
    e.detail.destination.container - {HTMLElement} Sortable Container that element was moved out of (or copied from)
    e.detail.destination.itemsBeforeUpdate - {Array} Sortable Items before the move
    e.detail.destination.items - {Array} Sortable Items after the move
    */
});

var <%$uploader.uniq%>_uploader = new plupload.Uploader({
    runtimes : 'html5,html4',

    browse_button : '<%$uploader.uniq%>_select',
    container: document.getElementById('<%$uploader.uniq%>_container'),
    url : 'upload.php?type=images',
    multi_selection: '<%$uploader.multi%>',
    drop_element: '<%$uploader.uniq%>_container',
    resize: {
      width: <%$uploader.width%>,
      height: <%$uploader.height%>,
      crop: false,
      quality: <%$uploader.quality%>,
      preserve_headers: false
    },

    filters : {
        max_file_size : '<%$uploader.maxfilesize%>',
        mime_types: [
            <%$uploader.mime_types%>
        ]
    },
    multipart_params : {
      "resize": {
        <%foreach from=$uploader.resize item=i key=k%>
            <%$k%>: {
              "width":"<%$i.width%>",
              "height":"<%$i.height%>",
              "quality":"<%$i.quality%>"
            },
        <%/foreach%>
      },
      <%foreach from=$uploader item=i key=k%>
        <%if !is_array($i)%>
          "<%$k%>":"<%$i%>",
        <%/if%>
      <%/foreach%>
    },

    init: {
        PostInit: function() {
        },

        FilesAdded: function(up, files) {
          <%if $uploader.multi != 'true'%>
          while (up.files.length > 1) {
              up.removeFile(up.files[0]);
          }
          <%/if%>

          $('<%$uploader.uniq%>_console').addClass('info');

          plupload.each(files, function(file) {
            $('#<%$uploader.uniq%>_console') . html( '<div id=' + file.id + '>' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>' ).show();
          });

          <%$uploader.uniq%>_uploader.start();
        },

        UploadProgress: function(up, file) {
          $('#<%$uploader.uniq%>_console #' + file.id + ' b').html('<%tr%>Dosya yükleniyor<%/tr%>, ' + file.percent + '%');
        },

        FileUploaded: function(up, file, response) {
          var obj = jQuery.parseJSON(response.response);
          var re = /(?:\.([^.]+))?$/;
          var ext = re.exec(obj.file)[1];

          var element = '<a class="media" href="%URL%" data-fancybox>\
                        <button data-rid="%PID%" data-group="%GROUP%" data-remove-image="%ID%" class="btn btn-danger waves-effect waves-light" type="button"><i class="fas fa-trash-alt"></i></button>\
                          %ELEMENT%\
                          <button data-toggle="modal" data-target="#<%$uploader.uniq%>_edit-image" data-edit-image="%ID%" class="btn btn-primary waves-effect waves-light" type="button"><i class="fas fa-pencil"></i></button>\
                        </a>';

          $('#<%$uploader.uniq%>_console #' + file.id + ' b').html('<%tr%>Yüklendi.<%/tr%>');

          element = $(element).wrap('<div>').parent().html().replace(/%URL%/g,obj.file);
          if(ext.toLowerCase() == 'pdf' || ext.toLowerCase() == 'mp4') {
            var ELM = '<i class="filetype '+ext.toLowerCase()+'">'+ext.toLowerCase()+'</i>';
          } else {
            var ELM = '<img src=\"'+obj.file+'\">';
          }
          element = $(element).wrap('<div>').parent().html().replace(/%ELEMENT%/g,ELM);
          element = $(element).wrap('<div>').parent().html().replace(/%ID%/g,obj.id);
          element = $(element).wrap('<div>').parent().html().replace(/%PID%/g,'');
          element = $(element).wrap('<div>').parent().html().replace(/%GROUP%/g,<%$uploader.group%>);

          $('#<%$uploader.uniq%>_uploaded').append(element);

          sortable('#<%$uploader.uniq%>_uploaded', 'reload');

          /*
          if(ext.toLowerCase() == 'pdf' || ext.toLowerCase() == 'mp4') {
          } else {
            $('#%uniq%_console #' + file.id + ' b').html('".$this->sl->languages('Yüklendi.')."');
          }
          */
        },

        Error: function(up, err) {
          $('#<%$uploader.uniq%>_console').html('<%tr%>Yükleme hatası oluştu, tekrar deneyiniz.<%/tr%>');
        }
    }
});

<%$uploader.uniq%>_uploader.init();
