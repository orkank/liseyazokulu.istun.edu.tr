<?php
  if(!empty($input['data'])) {
    $dataAttributes = @array_map(function($item, $key) {
      return 'data-'.$key.'="'.$item.'"';
    },array_values($input['data']), array_keys($input['data']));

    $dataAttributes = implode(' ', $dataAttributes);
  } else {
    $dataAttributes = '';
  }

  switch ($input['type']) {
    case 'parents':
      $template = '
      <div class="form-group">
        <label for="%SLUG%">%NAME%</label>
        <select class="form-control p-0" id="'.$input['slug'].'" '.$dataAttributes.' name="'.$input['slug'].'">
          %OPTIONS%
        </select>
        <div class="form-control-feedback"> <small></small> </div>
      </div>
      ';
      $data = $this->sl->tree(0,0,$this->data[$input['slug']], $this->self()['id_content'], $this->self()['id']);

      $data = str_replace(
        array('%SLUG%','%NAME%','%OPTIONS%'),
        array($input['slug'],$input['name'],$this->sl->tree_build($data)),
        $template
      );
    break;

    case 'modules':
      $template = '
      <div class="form-group">
        <label for="%SLUG%">%NAME%</label>
        <select class="form-control p-0" id="'.$input['slug'].'" name="'.$input['slug'].'" '.$dataAttributes.'>
          %OPTIONS%
        </select>
        <div class="form-control-feedback"> <small></small> </div>
      </div>
      ';

      $data = $this->sl->db->QueryArray("SELECT * FROM `modules`
        WHERE `type`='".$input['data_type']."' AND `parent`='".$input['data_parent']."'");

      $_data = array();
      for($i=0;$i<sizeof($data);$i++) {
        $checked = ($data[$i]['id'] == $this->data[$input['slug']])?' selected':'';
        $_data[] = "<option value=\"{$data[$i]['id']}\"{$checked}>{$data[$i]['name']}</option>";
      }

      $data = str_replace(
        array('%SLUG%','%NAME%','%OPTIONS%'),
        array($input['slug'],$input['name'],implode("\n",$_data)),
        $template
      );
    break;

    case 'templates':
      $template = '
      <div class="form-group">
        <label for="%SLUG%">%NAME%</label>
        <select class="form-control p-0" id="'.$input['slug'].'" name="'.$input['slug'].'" '.$dataAttributes.'>
          %OPTIONS%
        </select>
        <div class="form-control-feedback"> <small></small> </div>
      </div>
      ';

      $files = $this->sl->dir($this->sl->settings['theme_path'] .DS. 'templates');
      $data = array();
      $data[] = '<option value="" selected>'.$this->sl->languages('Seçiniz').'</option>';

      for($i=0;$i<sizeof($files);$i++) {
        $selected = (empty($selected) AND $this->data[$input['slug']] == $files[$i])?' selected':'';
        $data[] = '<option'.$selected.' value="'.$files[$i].'">'.$files[$i].'</option>';
      }

      if(!empty($files)) {
        $data = str_replace(
          array('%SLUG%','%NAME%','%OPTIONS%'),
          array($input['slug'],$input['name'],implode("\n", $data)),
          $template
        );
      }

    break;

    case 'checkbox':
      $template = '
      <div class="form-group">
      <label class="d-block">'.$input['name'].'</label>
      %ITEMS%
      <div class="form-control-feedback"> <small></small> </div>
      </div>
      ';

      $vals = array();
      $checked = '';

      for($z=0;$z<sizeof($input['values']);$z++){
        $uniq = uniqid($input['slug']);
        $checked = ((empty($checked) AND $input['values'][$z]['checked'] == '1') OR $this->data[$input['slug']] == '1')?' checked':'';
        $slug = (!empty($input['values'][$z]['slug']))?$input['values'][$z]['slug']:$input['slug'];

        $vals[] = '
            <input type="checkbox" id="'.$uniq.'" class="filled-in chk-col-blue" value="'.$input['values'][$z]['value'].'" name="'.$slug.'" '.$checked.'>
            <label for="'.$uniq.'">'.$input['values'][$z]['name'].'</label>
            ';
      }

      $data = str_replace(
        array('%ITEMS%'),
        array(
          implode("\n",$vals)
        ),
        $template
      );
    break;

    case 'switch':
      $checked = '';
      $checked = ((empty($checked) AND $input['checked'] == '1') OR $this->data[$input['slug']] == '1')?' checked':'';

      $template = '
        <div class="form-group">
          <label class="d-block" for="'.$input['name'].'">'.$input['name'].'</label>

          <div class="switch">
            <label>
              '.$input['values']['off'].'
                <input type="checkbox" name="'.$input['slug'].'" value="'.$input['value'].'" '.$checked.'><span class="lever"></span>
              '.$input['values']['on'].'
            </label>
          </div>

        </div>
      ';
      $data = $template;
    break;

    case 'radio':
        $template = '
        <div class="form-group">
          <label class="d-block" for="'.$input['name'].'">'.$input['name'].'</label>
          %INPUTS%
        </div>
        ';
        $input_template = '
        <input type="radio" %DATA_ATTIBUTES% class="check"%CHECKED% id="%ID%" name="'.$input['slug'].'" value="%VALUE%">
        <label for="%ID%">%NAME%</label>
        ';

        $input_templates = '';
        $selected = '';

        for($z=0;$z<sizeof($input['values']);$z++) {
          $selected = ((empty($selected) AND $input['values'][$z]['checked'] == '1') OR
          $this->data[$input['slug']] == $input['values'][$z]['value'])?' checked':'';

          $input_templates .= str_replace(
            array('%NAME%','%VALUE%','%ID%','%CHECKED%','%DATA_ATTIBUTES%'),
            array(
              $input['values'][$z]['name'],
              $input['values'][$z]['value'],
              uniqid($input['values'][$z]['value']),
              $selected,
              $this->prefix($dataAttributes, '')
            ),
            $input_template
          );
        }

        $data = str_replace(
          array('%INPUTS%'),
          array($input_templates)
          ,$template
        );
    break;
    case 'text':
      $wrap_uniq = uniqid('wrap');

      if($input['multilanguage'] == 1) {
        $template = '
          <div class="form-group">
            <label for="'.$wrap_uniq.'">'.$input['name'].'</label>
            <div class="input-group" id="'.$wrap_uniq.'">
              %INPUTS%
              <div class="input-group-append">
              %BUTTONS%
              </div>
            </div>
            <div class="form-control-feedback"> <small></small> </div>
          </div>
        ';
        $input_template   = '<input type="text" data-uniq="%UNIQ%" data-wrap="%UNIQ_WRAP%" class="form-control%ACTIVE%" %DATA_ATTIBUTES% name="%SLUG%" value="%VALUE%" id="%SLUG%" placeholder="">';
        $button_template  = '<a href="#" data-lang="%PREFIX%" data-wrap="%UNIQ_WRAP%" data-uniq="%UNIQ%" class="input-group-text%ACTIVE%">%CODE%</a>';

        $_inputs = array();
        $_buttons = array();

        for($z=0;$z<sizeof($this->sl->languages('langs'));$z++) {
          $active = ($z == 0?' active':' inactive');
          $uniq = uniqid($input['slug'].'_lang_'.$this->sl->languages('langs')[$z]['prefix']);

          $_inputs[] = str_replace(
            array('%ACTIVE%', '%SLUG%','%VALUE%','%DATA_ATTIBUTES%', '%UNIQ%','%UNIQ_WRAP%'),
            array(
              $active,
              $input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}',
              $this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'],
              $this->prefix($dataAttributes, $this->sl->languages('langs')[$z]['prefix']),
              $uniq,
              $wrap_uniq
            ),
              $input_template
            );

          $_buttons[] = str_replace(
            array('%ACTIVE%', '%SLUG%', '%UNIQ_WRAP%', '%CODE%', '%UNIQ%','%PREFIX%'),
            array(
              $active,
              $input['slug'],
              $wrap_uniq,
              strtoupper($this->sl->languages('langs')[$z]['prefix']),
              $uniq,
              $this->sl->languages('langs')[$z]['prefix']
            ),
              $button_template
            );
        }
        $data = str_replace(
          array('%INPUTS%','%BUTTONS%'),
          array(implode("\n",$_inputs),implode("\n",$_buttons)),
          $template
        );
      } else {
        $template = '
        <div class="form-group" id="'.$uniq.'">
            <label for="%SLUG%">%NAME%</label>
            <input %DATA_ATTIBUTES% type="text" data-uniq="'.$uniq.'" class="form-control" name="%SLUG%" id="%SLUG%" value="%VALUE%" placeholder="">
            <div class="form-control-feedback"> <small></small> </div>
        </div>
        ';

        $data = str_replace(
          array('%SLUG%','%NAME%','%VALUE%','%DATA_ATTIBUTES%'),
          array(
            $input['slug'],
            $input['name'],
            $this->data[$input['slug']],
            $this->prefix($dataAttributes, '')
          ),
            $template
        );
      }
    break;
    case 'textarea':
      $uniq = uniqid('editor');

      if($input['multilanguage'] == 1) {
        $template = '
        <h5 class="card-title">%NAME%</h5>
        <ul class="nav nav-tabs" role="tablist">
        %TABS%
        </ul>
        <div class="tab-content tabcontent-border m-b-20">
        %TABWINDOWS%
        </div>
        ';
        $tab_template = '<li class="nav-item"> <a data-lang="%PREFIX%" class="nav-link%ACTIVE%" data-toggle="tab" href="#tab_%ID%" role="tab"><span class="hidden-sm-up">%CODE%</span>
        <span class="hidden-xs-down">%NAME%</span></a> </li>';

        $tabwindow_template = '
            <div class="tab-pane%ACTIVE%" id="tab_%ID%" role="tabpanel">
                <div class="p-20">
                  <textarea class="form-control" id="%CODE%_%UNIQ%" %DATA_ATTIBUTES% rows="10" name="%SLUG%">%VALUE%</textarea>
                  <div class="form-control-feedback"> <small></small> </div>
                  '.$_editor.'
                </div>
            </div>
        ';

        $tabs = array();
        $tabwindows = array();

        for($z=0;$z<sizeof($this->sl->languages('langs'));$z++) {
          $active = ($z == 0?' active':'');

          $tabs[] = str_replace(
            array('%NAME%','%ID%','%CODE%','%ACTIVE%','%PREFIX%'),
            array(
              $this->sl->languages('langs')[$z]['name'],
              md5($input['name'].$this->sl->languages('langs')[$z]['name']),
              strtoupper($this->sl->languages('langs')[$z]['prefix']),
              $active,
              $this->sl->languages('langs')[$z]['prefix'],
            ),
            $tab_template
          );

          $tabwindows[] = str_replace(
            array('%ID%','%CODE%','%UNIQ%','%SLUG%','%ACTIVE%','%VALUE%','%DATA_ATTIBUTES%'),
            array(
              md5($input['name'].$this->sl->languages('langs')[$z]['name']),
              strtoupper($this->sl->languages('langs')[$z]['prefix']),
              $uniq,
              $input['slug'] .'{'. $this->sl->languages('langs')[$z]['prefix'] .'}',
              $active,
              $this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'],
              $this->prefix($dataAttributes, $this->sl->languages('langs')[$z]['prefix'])
            ),
            $tabwindow_template
          );
        }

        $data = str_replace(
          array('%NAME%','%TABS%','%TABWINDOWS%'),
          array(
            $input['name'],
            implode("\n",$tabs),
            implode("\n",$tabwindows)
          ),
          $template
        );
      } else {
        $_editor = ($input['custom_function'] == 'editor_advanced')?
        '<a href="#" data-toggle="modal" data-target="#html5editoradvanced" data-id="'.$uniq.'" class="btn waves-effect waves-light btn-xs btn-info m-t-10"> <i class="fab fa-html5"></i> '.$this->sl->languages('Gelişmiş Editör').' </a>'
        :'';

        $data = '
        <div class="form-group">
          <label for="'.$input['name'].'">'.$input['name'].'</label>
          <textarea '.$this->prefix($dataAttributes, '').' class="'.$editor.'form-control" id="'.$uniq.'" rows="10" name="'.$input['slug'].'">'.$this->data[$input['slug']].'</textarea>
          <div class="form-control-feedback"> <small></small> </div>
          '.$_editor.'
        </div>
        ';
      }
    break;

    default:
      // code...
    break;
  }
