/*!
 * clipboard.js v2.0.6
 * https://clipboardjs.com/
 *
 * Licensed MIT © Zeno Rocha
 */
!function(t,e){"object"==typeof exports&&"object"==typeof module?module.exports=e():"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?exports.ClipboardJS=e():t.ClipboardJS=e()}(this,function(){return o={},r.m=n=[function(t,e){t.exports=function(t){var e;if("SELECT"===t.nodeName)t.focus(),e=t.value;else if("INPUT"===t.nodeName||"TEXTAREA"===t.nodeName){var n=t.hasAttribute("readonly");n||t.setAttribute("readonly",""),t.select(),t.setSelectionRange(0,t.value.length),n||t.removeAttribute("readonly"),e=t.value}else{t.hasAttribute("contenteditable")&&t.focus();var o=window.getSelection(),r=document.createRange();r.selectNodeContents(t),o.removeAllRanges(),o.addRange(r),e=o.toString()}return e}},function(t,e){function n(){}n.prototype={on:function(t,e,n){var o=this.e||(this.e={});return(o[t]||(o[t]=[])).push({fn:e,ctx:n}),this},once:function(t,e,n){var o=this;function r(){o.off(t,r),e.apply(n,arguments)}return r._=e,this.on(t,r,n)},emit:function(t){for(var e=[].slice.call(arguments,1),n=((this.e||(this.e={}))[t]||[]).slice(),o=0,r=n.length;o<r;o++)n[o].fn.apply(n[o].ctx,e);return this},off:function(t,e){var n=this.e||(this.e={}),o=n[t],r=[];if(o&&e)for(var i=0,a=o.length;i<a;i++)o[i].fn!==e&&o[i].fn._!==e&&r.push(o[i]);return r.length?n[t]=r:delete n[t],this}},t.exports=n,t.exports.TinyEmitter=n},function(t,e,n){var d=n(3),h=n(4);t.exports=function(t,e,n){if(!t&&!e&&!n)throw new Error("Missing required arguments");if(!d.string(e))throw new TypeError("Second argument must be a String");if(!d.fn(n))throw new TypeError("Third argument must be a Function");if(d.node(t))return s=e,f=n,(u=t).addEventListener(s,f),{destroy:function(){u.removeEventListener(s,f)}};if(d.nodeList(t))return a=t,c=e,l=n,Array.prototype.forEach.call(a,function(t){t.addEventListener(c,l)}),{destroy:function(){Array.prototype.forEach.call(a,function(t){t.removeEventListener(c,l)})}};if(d.string(t))return o=t,r=e,i=n,h(document.body,o,r,i);throw new TypeError("First argument must be a String, HTMLElement, HTMLCollection, or NodeList");var o,r,i,a,c,l,u,s,f}},function(t,n){n.node=function(t){return void 0!==t&&t instanceof HTMLElement&&1===t.nodeType},n.nodeList=function(t){var e=Object.prototype.toString.call(t);return void 0!==t&&("[object NodeList]"===e||"[object HTMLCollection]"===e)&&"length"in t&&(0===t.length||n.node(t[0]))},n.string=function(t){return"string"==typeof t||t instanceof String},n.fn=function(t){return"[object Function]"===Object.prototype.toString.call(t)}},function(t,e,n){var a=n(5);function i(t,e,n,o,r){var i=function(e,n,t,o){return function(t){t.delegateTarget=a(t.target,n),t.delegateTarget&&o.call(e,t)}}.apply(this,arguments);return t.addEventListener(n,i,r),{destroy:function(){t.removeEventListener(n,i,r)}}}t.exports=function(t,e,n,o,r){return"function"==typeof t.addEventListener?i.apply(null,arguments):"function"==typeof n?i.bind(null,document).apply(null,arguments):("string"==typeof t&&(t=document.querySelectorAll(t)),Array.prototype.map.call(t,function(t){return i(t,e,n,o,r)}))}},function(t,e){if("undefined"!=typeof Element&&!Element.prototype.matches){var n=Element.prototype;n.matches=n.matchesSelector||n.mozMatchesSelector||n.msMatchesSelector||n.oMatchesSelector||n.webkitMatchesSelector}t.exports=function(t,e){for(;t&&9!==t.nodeType;){if("function"==typeof t.matches&&t.matches(e))return t;t=t.parentNode}}},function(t,e,n){"use strict";n.r(e);var o=n(0),r=n.n(o),i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};function a(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}function c(t){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,c),this.resolveOptions(t),this.initSelection()}var l=(function(t,e,n){return e&&a(t.prototype,e),n&&a(t,n),t}(c,[{key:"resolveOptions",value:function(t){var e=0<arguments.length&&void 0!==t?t:{};this.action=e.action,this.container=e.container,this.emitter=e.emitter,this.target=e.target,this.text=e.text,this.trigger=e.trigger,this.selectedText=""}},{key:"initSelection",value:function(){this.text?this.selectFake():this.target&&this.selectTarget()}},{key:"selectFake",value:function(){var t=this,e="rtl"==document.documentElement.getAttribute("dir");this.removeFake(),this.fakeHandlerCallback=function(){return t.removeFake()},this.fakeHandler=this.container.addEventListener("click",this.fakeHandlerCallback)||!0,this.fakeElem=document.createElement("textarea"),this.fakeElem.style.fontSize="12pt",this.fakeElem.style.border="0",this.fakeElem.style.padding="0",this.fakeElem.style.margin="0",this.fakeElem.style.position="absolute",this.fakeElem.style[e?"right":"left"]="-9999px";var n=window.pageYOffset||document.documentElement.scrollTop;this.fakeElem.style.top=n+"px",this.fakeElem.setAttribute("readonly",""),this.fakeElem.value=this.text,this.container.appendChild(this.fakeElem),this.selectedText=r()(this.fakeElem),this.copyText()}},{key:"removeFake",value:function(){this.fakeHandler&&(this.container.removeEventListener("click",this.fakeHandlerCallback),this.fakeHandler=null,this.fakeHandlerCallback=null),this.fakeElem&&(this.container.removeChild(this.fakeElem),this.fakeElem=null)}},{key:"selectTarget",value:function(){this.selectedText=r()(this.target),this.copyText()}},{key:"copyText",value:function(){var e=void 0;try{e=document.execCommand(this.action)}catch(t){e=!1}this.handleResult(e)}},{key:"handleResult",value:function(t){this.emitter.emit(t?"success":"error",{action:this.action,text:this.selectedText,trigger:this.trigger,clearSelection:this.clearSelection.bind(this)})}},{key:"clearSelection",value:function(){this.trigger&&this.trigger.focus(),document.activeElement.blur(),window.getSelection().removeAllRanges()}},{key:"destroy",value:function(){this.removeFake()}},{key:"action",set:function(t){var e=0<arguments.length&&void 0!==t?t:"copy";if(this._action=e,"copy"!==this._action&&"cut"!==this._action)throw new Error('Invalid "action" value, use either "copy" or "cut"')},get:function(){return this._action}},{key:"target",set:function(t){if(void 0!==t){if(!t||"object"!==(void 0===t?"undefined":i(t))||1!==t.nodeType)throw new Error('Invalid "target" value, use a valid Element');if("copy"===this.action&&t.hasAttribute("disabled"))throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');if("cut"===this.action&&(t.hasAttribute("readonly")||t.hasAttribute("disabled")))throw new Error('Invalid "target" attribute. You can\'t cut text from elements with "readonly" or "disabled" attributes');this._target=t}},get:function(){return this._target}}]),c),u=n(1),s=n.n(u),f=n(2),d=n.n(f),h="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},p=function(t,e,n){return e&&y(t.prototype,e),n&&y(t,n),t};function y(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}var m=(function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}}),e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}(v,s.a),p(v,[{key:"resolveOptions",value:function(t){var e=0<arguments.length&&void 0!==t?t:{};this.action="function"==typeof e.action?e.action:this.defaultAction,this.target="function"==typeof e.target?e.target:this.defaultTarget,this.text="function"==typeof e.text?e.text:this.defaultText,this.container="object"===h(e.container)?e.container:document.body}},{key:"listenClick",value:function(t){var e=this;this.listener=d()(t,"click",function(t){return e.onClick(t)})}},{key:"onClick",value:function(t){var e=t.delegateTarget||t.currentTarget;this.clipboardAction&&(this.clipboardAction=null),this.clipboardAction=new l({action:this.action(e),target:this.target(e),text:this.text(e),container:this.container,trigger:e,emitter:this})}},{key:"defaultAction",value:function(t){return b("action",t)}},{key:"defaultTarget",value:function(t){var e=b("target",t);if(e)return document.querySelector(e)}},{key:"defaultText",value:function(t){return b("text",t)}},{key:"destroy",value:function(){this.listener.destroy(),this.clipboardAction&&(this.clipboardAction.destroy(),this.clipboardAction=null)}}],[{key:"isSupported",value:function(t){var e=0<arguments.length&&void 0!==t?t:["copy","cut"],n="string"==typeof e?[e]:e,o=!!document.queryCommandSupported;return n.forEach(function(t){o=o&&!!document.queryCommandSupported(t)}),o}}]),v);function v(t,e){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,v);var n=function(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}(this,(v.__proto__||Object.getPrototypeOf(v)).call(this));return n.resolveOptions(e),n.listenClick(t),n}function b(t,e){var n="data-clipboard-"+t;if(e.hasAttribute(n))return e.getAttribute(n)}e.default=m}],r.c=o,r.d=function(t,e,n){r.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},r.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)r.d(n,o,function(t){return e[t]}.bind(null,o));return n},r.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return r.d(e,"a",e),e},r.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},r.p="",r(r.s=6).default;function r(t){if(o[t])return o[t].exports;var e=o[t]={i:t,l:!1,exports:{}};return n[t].call(e.exports,e,e.exports,r),e.l=!0,e.exports}var n,o});

(function(o){var h,l=o();o.fn.sortable=function(s){var d=String(s);s=o.extend({connectWith:false,placeholderClass:""},s);return this.each(function(){if(/^enable|disable|destroy$/.test(d)){var e=o(this).children(o(this).data("items")).attr("draggable",d=="enable");if(d=="destroy"){e.add(this).removeData("connectWith items").off("dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s")}return}var r,a,i,t=o(this).children(s.items);var n=o("<"+(/^ul|ol$/i.test(this.tagName)?"li":/^tbody$/i.test(this.tagName)?"tr":"div")+' class="sortable-placeholder '+s.placeholderClass+'">').html("&nbsp;");t.find(s.handle).mousedown(function(){r=true}).mouseup(function(){r=false});o(this).data("items",s.items);l=l.add(n);if(s.connectWith){o(s.connectWith).add(this).data("connectWith",s.connectWith)}t.attr("draggable","true").on("dragstart.h5s",function(e){if(s.handle&&!r){return false}r=false;var t=e.originalEvent.dataTransfer;t.effectAllowed="move";t.setData("Text","dummy");i=(h=o(this)).addClass("sortable-dragging").index();a=h.parent()}).on("dragend.h5s",function(){if(!h){return}h.removeClass("sortable-dragging").show();l.detach();if(i!=h.index()){h.parent().trigger("sortupdate",{item:h})}if(!h.parent().is(a)){h.parent().trigger("sortconnect",{item:h})}h=null}).not("a[href], img").on("selectstart.h5s",function(){this.dragDrop&&this.dragDrop();return false}).end().add([this,n]).on("dragover.h5s dragenter.h5s drop.h5s",function(e){if(!t.is(h)&&s.connectWith!==o(h).parent().data("connectWith")){return true}if(e.type=="drop"){e.stopPropagation();l.filter(":visible").after(h);h.trigger("dragend.h5s");return false}e.preventDefault();e.originalEvent.dataTransfer.dropEffect="move";if(t.is(this)){if(s.forcePlaceholderHeight||s.forcePlaceholderSize){n.height(h.outerHeight())}if(s.forcePlaceholderWidth||s.forcePlaceholderSize){n.width(h.outerWidth())}h.hide();o(this)[n.index()<o(this).index()?"after":"before"](n);l.not(n).detach()}else if(!l.is(this)&&!o(this).children(s.items).length){l.detach();o(this).append(n)}return false})})}})(jQuery);

(function($){
$.fn.serializeAny = function() {
    var ret = [];
    $.each( $(this).find(':input'), function() {
        ret.push( encodeURIComponent(this.name) + "=" + encodeURIComponent( $(this).val() ) );
    });

    return ret.join("&").replace(/%20/g, "+");
}
})(jQuery);

class SL {

  constructor(conf) {
    this.prefix = conf['prefix'];
    this.portal_url = conf['portal_url'];
    this.conf = conf;
    var _this = this;

    $('*[data-select-picker]').selectpicker({
      size: 5,
    });

    // prevent Bootstrap from hijacking TinyMCE modal focus
    $(document).on('focusin', function(e) {
      if ($(e.target).closest(".tox-dialog-wrap").length) {
        e.stopImmediatePropagation();
      }
    });

    $(document).on('click', '*[data-advanced-editor-save]', function(){
      var id = $(this).attr('data-advanced-editor-save');
      var content = tinyMCE.get('floateditor').getContent();

      tinymce.get(id).setContent(content);

      $('button[data-advanced-editor-dismiss').trigger('click');
    });

    $(document).on('click', '*[data-advanced-editor-dismiss]', function() {
      tinymce.get('floateditor').setContent('');
    });

    $(document).on('click', '*[data-target="#html5editoradvanced"]', function() {
      var id = $(this).attr('data-id');
      var content = tinymce.get(id).getContent();
      $('#html5editoradvanced').find('button[data-advanced-editor-save]').attr('data-advanced-editor-save',id);

       tinymce.get('floateditor').setContent(content);

      return false;
    });

    $(document).on('click', '*[data-apply-template]', function() {
      var tpl = $(this).attr('data-apply-template');
      var id = $(this).attr('data-id');

      //var decodedString = atob(tpl);
      //console.log(decodedString);
      tinymce.get(id).setContent(tpl);
    });

    this.editorRefresh();

    $('*[data-editor-advanced="1"]').each(function() {
      var template = '<a href="#" data-toggle="modal" data-target="#html5editoradvanced"\
      data-id="'+$(this).attr('id')+'" class="btn waves-effect waves-light btn-xs btn-info m-t-10">\
      <i class="fab fa-html5"></i> Gelişmiş Editör </a>';

      $(template).insertAfter($(this));
    });

    $("[data-filemanager]").fancybox({
        iframe : {
            css : {
                width : '900px',
                height : '600px'
            }
        }
    });
  }
  formToJson(nameForm) {
    var jsonForm={};
    $("input", $(nameForm)).each(function(index){
      jsonForm[$(this).attr("id")] = this.value;
    })

    return jsonForm;
  }

  editorRefresh() {
    tinymce.init({
      language: this.prefix,
      selector: 'textarea[data-editor="1"]',
      height: 500,
      menubar: false,
      cleanup : false,
      relative_urls : false,
      remove_script_host : false,
      entity_encoding: "raw",
      plugins: [
        'advlist autolink lists link image charmap print preview anchor textcolor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount responsivefilemanager'
      ],
      toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
      content_css: [
      ],

      content_style: 'body {padding:20px !important;}\
	  ul.accordion li > div {\
		display:block !important;\
		}',
      content_css: [
        '/templates/default/assets/css/style.css'
      ],

      external_filemanager_path: this.portal_url + "includes/filemanager/",
    	filemanager_title: "Dosya Yöneticisi",
    	external_plugins: {
    		"responsivefilemanager": this.portal_url + "assets/plugins/tinymce/plugins/responsivefilemanager/plugin.min.js",
    		"filemanager": this.portal_url + "includes/filemanager/plugin.min.js"
    	}

    });

    tinymce.init({
      /*
      below plugins are premium, I removed.

      linkchecker
      mentions
      pageembed
      permanentpen
      formatpainter
      a11ychecker
      tinymcespellchecker
      mediaembed
      advcode
      powerpaste
      */
      oninit : "setPlainText",
      verify_html: false,
      language: this.prefix,
      selector: 'textarea#floateditor',
      plugins: 'responsivefilemanager print preview searchreplace autolink directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help',
      toolbar: 'formatselect | bold italic strikethrough forecolor backcolor permanentpen formatpainter | link image media pageembed | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | addcomment',


      image_advtab: true,
      cleanup: false,
      relative_urls: false,
      remove_script_host: false,
      height: 600,
      entity_encoding: "raw",
      /*
      link_list: [
        { title: 'My page 1', value: 'http://www.tinymce.com' },
        { title: 'My page 2', value: 'http://www.moxiecode.com' }
      ],
      image_list: [
        { title: 'My page 1', value: 'http://www.tinymce.com' },
        { title: 'My page 2', value: 'http://www.moxiecode.com' }
      ],
      image_class_list: [
        { title: 'None', value: '' },
        { title: 'Some class', value: 'class-name' }
      ],
      importcss_append: true,
      templates: [
        { title: 'Some title 1', description: 'Some desc 1', content: 'My content' },
        { title: 'Some title 2', description: 'Some desc 2', content: '<div class="mceTmpl"><span class="cdate">cdate</span><span class="mdate">mdate</span>My content2</div>' }
      ],
      content_style: '.mce-annotation { background: #fff0b7; } .tc-active-annotation {background: #ffe168; color: black; }',
      */
      content_style: 'body {padding:20px !important;}\
	  ul.accordion li > div {\
		display:block !important;\
		}',
      content_css: [
        '/templates/default/assets/css/style.css'
      ],

      template_cdate_format: '[CDATE: %Y/%m/%d : %H:%M:%S]',
      template_mdate_format: '[MDATE: %Y/%m/%d : %H:%M:%S]',
      image_caption: true,
      spellchecker_dialog: true,
      external_filemanager_path: "includes/filemanager/",
      filemanager_title: "Dosya Yöneticisi",
      external_plugins: {
        "responsivefilemanager": this.portal_url + "assets/plugins/tinymce/plugins/responsivefilemanager/plugin.min.js",
        "filemanager": this.portal_url + "includes/filemanager/plugin.min.js"
      }

     });


    tinymce.init({
      /*
      below plugins are premium so I remove them.

      linkchecker
      mentions
      pageembed
      permanentpen
      formatpainter
      a11ychecker
      tinymcespellchecker
      mediaembed
      advcode
      powerpaste
      */
      verify_html: false,
      language: this.prefix,
      selector: 'textarea[data-editor="2"]',
      plugins: 'responsivefilemanager print preview searchreplace autolink directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help paste',
      toolbar: 'formatselect | bold italic strikethrough forecolor backcolor permanentpen formatpainter | link image media pageembed | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | addcomment',

      image_advtab: true,
      cleanup: false,
      relative_urls: false,
      remove_script_host: false,
      height: 600,
      entity_encoding: "raw",
      /*
      link_list: [
        { title: 'My page 1', value: 'http://www.tinymce.com' },
        { title: 'My page 2', value: 'http://www.moxiecode.com' }
      ],
      image_list: [
        { title: 'My page 1', value: 'http://www.tinymce.com' },
        { title: 'My page 2', value: 'http://www.moxiecode.com' }
      ],
      image_class_list: [
        { title: 'None', value: '' },
        { title: 'Some class', value: 'class-name' }
      ],
      importcss_append: true,
      templates: [
        { title: 'Some title 1', description: 'Some desc 1', content: 'My content' },
        { title: 'Some title 2', description: 'Some desc 2', content: '<div class="mceTmpl"><span class="cdate">cdate</span><span class="mdate">mdate</span>My content2</div>' }
      ],
      content_style: '.mce-annotation { background: #fff0b7; } .tc-active-annotation {background: #ffe168; color: black; }',
      */
      content_style: 'body {padding:20px !important;}\
  	  ul.accordion li > div {\
		display:block !important;\
  		}',
      content_css: [
        '/templates/default/assets/css/style.css'
      ],

      template_cdate_format: '[CDATE: %Y/%m/%d : %H:%M:%S]',
      template_mdate_format: '[MDATE: %Y/%m/%d : %H:%M:%S]',
      image_caption: true,
      spellchecker_dialog: true,
      external_filemanager_path: "includes/filemanager/",
      filemanager_title: "Dosya Yöneticisi",
      external_plugins: {
        "responsivefilemanager": this.portal_url + "assets/plugins/tinymce/plugins/responsivefilemanager/plugin.min.js",
        "filemanager": this.portal_url + "includes/filemanager/plugin.min.js"
      }

     });

  }
  datePicker() {
    $.each($('*[data-datepicker]'), function(){
      // if($(this).data('style') == 'inline') {
        var id = $(this).attr('id');
        $('#' + id).datepicker({
          todayHighlight: true
        });

        if($('input[name="' +id+ '"]').val().length > 0) {
          $('#' + id).datepicker('setDate', $('input[name="' +id+ '"]').val());
        }

        $('#' + id).on('changeDate', function() {
          $('input[name="' + id + '"]').val(
            $('#' + id).datepicker('getFormattedDate')
          );
        });
      // } else {
      //   $(this).datepicker({
      //     todayHighlight: true
      //   });
      // }
    });
  }
  editImages() {
    $(document).on('click', 'button[data-edit-image]', function(){
      var id = $(this).data('edit-image'),
          target = $(this).data('target');

      $(target).find('input').val('');

      $.post('rpc.php', {id:id, action:"image-edit"}, function(e) {
        if(e.status == 1) {
          $(target).find('input[name="id"]').val(id);

          $.each(e.custom, function(key, value){
            $(target).find('input[id="'+key+'"]').val(value);
          });
        } else {
          sl.alert(e.alerts);
        }
      },'json');

      return false;
    });
    $('button[data-images-save]').on('click',function(){
      var uniq = $(this).data('uniq'),
          //data = $('#' + uniq + ' .modal-body :input').serialize(),
          data = sl.formToJson('#' + uniq + ' .modal-body'),
          id = $('#' + uniq + ' .modal-body input[name="id"]').val();

      $.post('rpc.php?id=' + id + '&action=image-update', data, function(e) {
        if(e.status == 1) {
          $('#' + uniq + ' .modal-body :input').val('');
          $('#' + uniq + ' button[data-dismiss]').trigger('click');
        }

        sl.alert(e.alerts);
      },'json');

      return false;
    });
  }
  changeLang() {
    $('a[data-change-lang]').on('click',function(){
      var prefix = $(this).attr('data-change-lang');
      var prefix_upper = prefix.toUpperCase();
      var prefix_lower = prefix.toLowerCase();

      $('*[data-lang="'+prefix_lower+'"], *[data-lang="'+prefix_upper+'"]').trigger('click');
    });
  }

  clipboard(input) {
    /* Get the text field */
    var copyText = document.querySelector(input);

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /*For mobile devices*/

    /* Copy the text inside the text field */
    document.execCommand("copy");

    /* Alert the copied text */
    alert("Panoya kopyalandı. \n" + copyText.value);
  }

  removeImage() {
    var uniq = this.conf["uniq"];

    $(document).on('click','button[data-remove-image]',function(){
      var image = $(this).attr('data-remove-image'),
          rid   = $(this).attr('data-rid'),
          group = $(this).attr('data-group'),
          _this = $(this);

      $.post('rpc.php', {image:image, rid: rid, group: group, action:"image-remove", "uniq": uniq}, function(e) {
        if(e.status == 1) {
          $(_this).parent().remove();
        }
        sl.alert(e.alerts);
      },'json');

      return false;
    });
  }

  langs() {
    $.extend( $.fn.dataTable.defaults, {
      "language": {
        "url": "lang/"+this.prefix+".datatables.json"
       }
    });
    $.fn.datepicker.defaults.language = this.prefix;
    $.fn.datepicker.defaults.format = 'mm/dd/yyyy';
  }
  alert(alerts) {
    // icon = 'warning', subject = 'Dikkat!', message = ''
    $.each(alerts, function(index, value) {
      swal({
        title: value[2],
        text: value[1],
        icon: value[0]
      });
    });
  }
  dataLang() {
    $(document).on('click', '*[data-lang]', function() {
      var uniq = $(this).attr('data-uniq');
      var wrap = $(this).attr('data-wrap');

      if(wrap) {
        wrap = '#'+wrap+' ';

        $(wrap).find('a[class*="active"]').removeClass('active');
        $(this).addClass('active');
        $(wrap+'input[class*="active"]').removeClass('active').addClass('inactive');
      }

      $(wrap+'input[data-uniq="'+uniq+'"]').addClass('active').removeClass('inactive');
      $(wrap+'input[data-uniq="'+uniq+'"]').focus().val($(wrap+'input[data-uniq="'+uniq+'"]').val());

      return false;
    });
  }
  Switchery() {
    // Switchery
    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
    $('.js-switch').each(function() {
        new Switchery($(this)[0], $(this).data());
    });
  }
  alphaLength(target = '') {
    if(!target)
      target = 'input[data-function="alphaLength"]';

    $(target).each(function(e){
      var name = $(this).attr('name');
      var uniq = $(this).data('uniq');
      var wrap = $(this).data('wrap');
      var _uniq = $(this).data('uniq') +'_progress';
      var max = $(this).data('max');

      var progress = '\
      <div class="col-md-12" id="'+_uniq+'">\
      <div class="progress m-t-20">\
        <div class="progress-bar bg-danger" style="width: 0%; height:25px; color: #fff;" role="progressbar">0%</div>\
      </div>\
      </div>\
      ';

      $(this).on('keyup focus hover change',function(){
        var value = $(this).val();
        var length = value.length;

        var percent =  Math.round((length / max) * 100);

        $('#' + _uniq).find('.progress-bar').css('width', percent +'%').html(length + ' / ' + max);
        $('#' + _uniq).show();
      });

      $(this).focusout(function(){
        $('#' + _uniq).hide();
      });

      $('#'+ wrap).append(progress);
      $('#'+ _uniq).hide();
    });
  }
  slugs(element = '', from = '') {
    if(!element)
      element = 'input[data-function="slug"]';

    $(element).each(function(e) {
      if(!from)
        var from = $('input[name="' + $(this).data('from') + '"]');

      var _this = $(this);
      var target = $(this);
      var target_value = $(this).val();
      var value = $(from).val();

      if(value != undefined && target_value == '') {
        $(this).val(SL.prototype.slugify(value));
      }

      $(from).on('keyup focus hover change',function() {
        var value = $(this).val();
        var target_value = $(_this).val();

        if(value != undefined && target_value == '') {

        }

        $(target).val(SL.prototype.slugify(value));
      });
    });
  }
  isFileImage(file) {
      return file && file['type'].split('/')[0] === 'image';
  }
  slugify(string) {
    const a = 'àáäâãåăæçèéëêǵḧìíïîḿńǹñòóöôœøṕŕßśșțùúüûǘẃẍÿź·/_,:;ŞşİıĞğÜüÖö';
    const b = 'aaaaaaaaceeeeghiiiimnnnooooooprssstuuuuuwxyz------ssiigguuoo';
    const p = new RegExp(a.split('').join('|'), 'g');
    return string.toString().toLowerCase()
      .replace(/\s+/g, '-') // Replace spaces with -
      .replace(p, c => b.charAt(a.indexOf(c))) // Replace special characters
      .replace(/&/g, '-and-') // Replace & with ‘and’
      .replace(/[^\w\-]+/g, '') // Remove all non-word characters
      .replace(/\-\-+/g, '-') // Replace multiple - with single -
      .replace(/^-+/, '') // Trim - from start of text
      .replace(/-+$/, ''); // Trim - from end of text
  }

  cronjob(key, options, value, action, type = 0, alert = true) {

    $.ajax({
        url: 'cronjobs.php',
        type: 'post',
        data: {
          key:key,
          value:value,
          options: options,
          type: type,
          action: action
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (e) {
          if(e.status == 'ok' && alert) {
            swal("Başarılı!", "Zamanlandırılmış göreve eklendi.", "success");

            return true;
          } else if(alert) {
            swal('Başarısız!','İşlemi başarısız lütfen tekrar deneyiniz.);?>','error');

            return false;
          }
        }
    });

  }

  // Strips HTML and PHP tags from a string
  // returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
  // example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
  // returns 2: '<p>Kevin van Zonneveld</p>'
  // example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
  // returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
  // example 4: strip_tags('1 < 5 5 > 1');
  // returns 4: '1 < 5 5 > 1
  strip_tags(str, allowed_tags) {
      var key = '';
      var allowed = false;
      var matches = [];
      var allowed_array = [];
      var allowed_tag = '';
      var i = 0;
      var k = '';
      var html = '';
      var replacer = function (search, replace, str) {
          return str.split(search).join(replace);
      };
      // Build allowes tags associative array
      if (allowed_tags) {
          allowed_array = allowed_tags.match(/([a-zA-Z0-9]+)/gi);
      }
      str += '';

      // Match tags
      matches = str.match(/(<\/?[\S][^>]*>)/gi);
      // Go through all HTML tags
      for (key in matches) {
          if (isNaN(key)) {
                  // IE7 Hack
              continue;
          }

          // Save HTML tag
          html = matches[key].toString();
          // Is tag not in allowed list? Remove from str!
          allowed = false;

          // Go through all allowed tags
          for (k in allowed_array) {            // Init
              allowed_tag = allowed_array[k];
              i = -1;

              if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+'>');}
              if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+' ');}
              if (i != 0) { i = html.toLowerCase().indexOf('</'+allowed_tag)   ;}

              // Determine
              if (i == 0) {                allowed = true;
                  break;
              }
          }
          if (!allowed) {
              str = replacer(html, "", str); // Custom replace. No regexing
          }
      }
      return str;
  }
}
