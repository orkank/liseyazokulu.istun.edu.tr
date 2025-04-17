<?php
class Portal {

  public $modules = array();
  public $module  = array();
  public $breadcrumbs  = array();
  public $trees  = array();
  public $data = array();
  public $readySQL = array();

  public function __construct(&$sl) {
    $this->sl = $sl;

    $this->sl->smarty->setTemplateDir($this->sl->settings['portal_theme_path']);
  }

  public function data($variable = '', $key = '', $overwrite = true) {
    switch ($variable) {
      case '_clear':
        $this->data = array();

        return;
      break;
      case '_merge_config':
        $merged = array();
        $data = array();

        for($i=0;$i<sizeof($this->config['tabs']);$i++) {
          if(isset($this->config['tabs'][$i]['inputs']) AND is_array($this->config['tabs'][$i]['inputs']))
            $merged = array_merge($merged, $this->config['tabs'][$i]['inputs']);
        }

        $d = 0;

        for($i=0;$i<sizeof($merged);$i++) {
          switch (@$merged[$i]['type']) {
            case 'images':
              // Nothing do for images
            break;
            case 'checkbox':
              for($z=0;$z<sizeof($merged[$i]['values']);$z++) {
                if(!empty($key[$merged[$i]['values'][$z]['slug']])) {
                  $this->data[$merged[$i]['values'][$z]['slug']] = $merged[$i]['values'][$z];
                  $this->data[$merged[$i]['values'][$z]['slug']]['value'] = $key[$merged[$i]['values'][$z]['slug']];
                }
              }
            break;

            default:
              if(isset($merged[$i]['multilanguage']) AND $merged[$i]['multilanguage'] == 1) {
                for($z=0;$z<sizeof($this->sl->languages('langs'));$z++) {
                  $this->data[$merged[$i]['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'] = $merged[$i];
                  $this->data[$merged[$i]['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}']['value'] =
                  $key[$merged[$i]['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'];
                }
              } else {
                $this->data[$merged[$i]['slug']] = $merged[$i];
                if(isset($key[$merged[$i]['slug']]))
                  $this->data[$merged[$i]['slug']]['value'] = $key[$merged[$i]['slug']];

                if(isset($merged[$i]['multiple']) AND $merged[$i]['multiple'] == '1') {
                } else {
                }
              }
            break;
          }
          $d++;
        }

        return $this->data;
      break;

      default:
        if(!empty($variable)) {
          if(is_array($variable) AND $overwrite) {
            $this->data = array_merge($variable, $this->data);

            return true;
          } elseif(is_array($variable) AND !$overwrite){
            $this->data = array_merge($this->data, $variable);

            return true;
          }

          if(!empty($variable) AND !is_array($variable)) {
            if(isset($this->data[$key]))
              $this->data[$key] = array_merge([$variable], (is_array($this->data[$key])?$this->data[$key]:[$this->data[$key]]) );
            else
              $this->data[$key] = $variable;

            return true;
          }
        }
      break;
    }

    return $this->data;
  }

  public function validate($input = '', $value = '', $add = '') {
    // DÜZGÜN YAZ GUDUBET
    $DYG = false;

    if($input['validate']['require'] AND empty($value))
      $DYG = true;

    if($input['validate']['type'] == 'int' AND !is_numeric($value))
      $DYG = true;

    if($input['validate']['type'] == 'int' AND !is_numeric($value))
      $DYG = true;

    if($DYG)
      $this->sl->alert(
        array(
          $this->sl::ALERT_WARNING,
          sprintf($this->sl->languages($add. ' - "%s", hatalı giriş yaptınız lütfen tekrar kontrol ediniz.'), $input['name'])
        )
      );
  }

  public function SLEntities($str) {
    if(is_array($str)) {
			$str_keys = array_keys($str);
			for($i=0;$i<sizeof($str);$i++) {
				if(is_array($str[$str_keys[$i]])) {
					$arr[$str_keys[$i]] = $this->SLEntities($str[$str_keys[$i]]);
				} else {
					$arr[$str_keys[$i]] = htmlspecialchars($str[$str_keys[$i]]);
				}
			}
		} else {
			$arr = htmlspecialchars($str);
		}

		if(isset($arr))
			return $arr;
		else
			return false;
  }

  public function readySQL($validate = false) {
    $keys = array_keys($this->data);
    $columns = $this->sl->columns; //$this->sl->columns($this->self()['table']);

    for($key=0;$key<sizeof($this->data);$key++) {
      $is_column = (!$columns[$keys[$key]])?false:true;

      /*
      $this->data[$keys[$key]]['default_value'] = (!isset($this->data[$keys[$key]]['default_value']))?$this->data[$keys[$key]]['default_value']:'';

      $this->data[$keys[$key]]['value'] =
      (isset($this->data[$keys[$key]]['value']) AND !empty($this->data[$keys[$key]]['value'])) ?
      $this->data[$keys[$key]]['value'] :
      $this->data[$keys[$key]]['default_value'];
      */

      if($is_column) {
        $this->data[$keys[$key]]['value'] = $this->sl->db->Escape($this->data[$keys[$key]]['value']);
          /*
          $this->readySQL['columns'][] = array(
            'key' => $this->data[$key]['slug'],
            'value' => $this->data[$key]['value']
          );
          */
          $this->readySQL['columns'][$keys[$key]] =
          (
            (isset($this->data[$keys[$key]]['int']) AND $this->data[$keys[$key]]['int'] == 1 AND !empty($this->data[$keys[$key]]['value']))?
            $this->data[$keys[$key]]['value'] :
            "'{$this->data[$keys[$key]]['value']}'"
          );
      } else {
        if($this->data[$keys[$key]]['serialize'] == 1 AND !empty($this->data[$keys[$key]]['value'])) {
          $this->readySQL['nodes'][$keys[$key]] = "'".serialize(($this->data[$keys[$key]]['value'])) . "'";
          continue;
        }

        if(isset($this->data[$keys[$key]]['multiple']) AND $this->data[$keys[$key]]['multiple'] == '1') {
          $this->readySQL['nodes'][$keys[$key]] = $this->data[$keys[$key]]['value'];
          continue;
        }

        if(isset($this->data[$keys[$key]]['implode']) AND !empty($this->data[$keys[$key]]['value'])) {
          $this->readySQL['nodes'][$keys[$key]] = "'".implode($this->data[$keys[$key]]['implode'], $this->data[$keys[$key]]['value']) . "'";
          continue;
        }

        if(is_array($this->data[$keys[$key]]['value'])) {
          if($this->data[$keys[$key]]['encode'] == '1' AND !empty($this->data[$keys[$key]]['value'])) {
            $this->data[$keys[$key]]['value'] = $this->sl->encode($this->data[$keys[$key]]['value']);
          }

          $this->readySQL['nodes'][$keys[$key]] = "'".serialize(($this->data[$keys[$key]]['value'])). "'";
          //$this->data[$keys[$key]]['value'] = $this->sl->decode($this->data[$keys[$key]]['value']);
        } else {
          $this->data[$keys[$key]]['value'] = $this->sl->db->Escape($this->data[$keys[$key]]['value']);
          $this->readySQL['nodes'][$keys[$key]] = "'{$this->data[$keys[$key]]['value']}'";
        }
      }
    }
  }

  public function config() {
    if(!isset($this->config))
      $this->config = json_decode(file_get_contents(PB . $this->sl->settings['portal_url']. 'config' .DS. $this->self()['config']), true);

    return $this->config;
  }

  public function getcontent($merge = true) {
    if(isset($this->content) AND !empty($this->content))
      return $this->content;

    $this->content = $this->sl->db->QuerySingleRowArray("SELECT * FROM `contents` WHERE `id`='".$this->self()['id_content']."'", MYSQLI_ASSOC);

    if($this->content) {
      if($this->content['sl_module'] != $this->module['module'])
        header("Location: {$this->module['modules'][$this->content['sl_module']]['link']}&id={$this->content['id']}");

      $this->nodes = $this->sl->db->QueryArray("SELECT * FROM `content_nodes` WHERE `cid`='".$this->content['id']."'",MYSQLI_ASSOC);

      if($merge) {
        $this->data('_clear');
        $this->data($this->content, false, true);

        for($i=0;$i<sizeof($this->nodes);$i++) {
          $this->data($this->nodes[$i]['value'], $this->nodes[$i]['key'], true);
        }
      }
    } else {
      return false;
    }
    return $this->content;
  }

  public function self($variables = '') {
    if(empty($variables) AND !empty($this->module))
      return $this->module;

    if(
      isset($variables['uniq']) AND
      isset($this->modules[$variables['uniq']])) {

      $this->module = array_merge($this->modules[$variables['uniq']], $variables);
      $this->module['link'] = (isset($variables['link']))?$variables['link']:$this->module['file'];

      if(
        isset($variables['module'])
      ) {
        $this->breadcrumb('set', $this->module);
        $this->module = array_merge($this->module, $this->module['modules'][$variables['module']]);
      }
    } elseif(!empty($variables['name']) AND !empty($variables['file'])) {
      $this->module = array(
        'name' => $variables['name'],
        'file' => $variables['file'],
        'link' => (isset($variables['link']))?$variables['link']:$variables['file']
      );
    }

    if(!empty($variables['params'])) {
      $this->module['link'] = $this->module['link'] . (parse_url($this->module['link'], PHP_URL_QUERY) ? '&' : '?') . $variables['params'];
    }

    $this->breadcrumb('set', $this->module);
  }

  public function break() {
    $this->sl->nocache();
    header("Location: ".$this->sl->settings['url']."".$this->sl->settings['portal_url']);

    exit;
  }

  public function breadcrumb($com, $variables) {
    switch ($com) {
      case 'set':
        $this->breadcrumbs[] = array('name' => $variables['name'], 'link' => $variables['link']);

        return true;
      break;

      default:
        return $this->breadcrumbs;
      break;
    }
  }

  public function addmodule($module) {
    $this->modules[$module['uniq']] = $module;
  }

  public function modules() {
    return array_values($this->modules);
  }

  public function prefix( String $str, $prefix = false, $seperator = '') {
    if(strpos($str, '%prefix%') !== FALSE) {
      return str_replace('%prefix%', ($prefix != false?$prefix:$this->sl->languages('prefix')).$seperator, $str);
    }
    if(strpos($str, '%lang%') !== FALSE) {
      return str_replace('%lang%', ($prefix != false?$prefix:$this->sl->languages('prefix')).$seperator, $str);
    }

    return $str;
  }

  public function table($table, $columns) {
    if(!$table OR !$columns)
      return false;

    $keys = array_keys($this->sl->langs);
    return;

  	for($s=0;$s<sizeof($columns);$s++) {
      if($columns[$s]['multilanguage']) {
        for($i=0;$i<sizeof($this->sl->langs);$i++) {
          $index = (isset($columns[$s]['index']) AND $columns[$s]['index'] == '1')?
          ", ADD INDEX (`".$columns[$s]['name']."{".$this->sl->langs[$keys[$i]]['prefix']."}`)":
          '';

          $query = "SHOW COLUMNS FROM `".$table."`
            LIKE '{$columns[$s]['name']}{{$this->sl->langs[$keys[$i]]['prefix']}}'";

          $col = $this->sl->db->QuerySingleValue($query);

      		if(!is_string($col)) {
            $query = "ALTER TABLE `".$table."` ADD
              `".$columns[$s]['name']."{".$this->sl->langs[$keys[$i]]['prefix']."}`
              ".$columns[$s]['type']."".$index."";
            echo $query;
            $this->sl->db->Query($query);
          }

        }
      } else {
        $index = ($columns[$s]['index'] == '1')?
        ", ADD INDEX (`".$columns[$s]['name']."`)":
        '';

        $col = $this->sl->db->QuerySingleValue("SHOW COLUMNS FROM `".$table."`
          LIKE '".$columns[$s]."'");

        if(!$col)
          $this->sl->db->Query("ALTER TABLE `".$table."` ADD
            `".$columns[$s]['name']."`".$index."
            ".$columns[$s]['type']."");
      }
    }
  }

  public function parents($id = 0, $order = '') {
    #error_reporting(E_ALL);
    $array = array();
    $where = array();
    $where[] = "`parent` = {$id}";
    $where = implode(" AND ",$where);

    $count = $this->sl->db->QuerySingleValue("SELECT COUNT(*) FROM `contents` WHERE {$where}");

    if($count > 0) {
      $array = $this->sl->db->QueryArray("SELECT
        `id`,
        `slug{".$this->sl->languages('prefix')."}` AS `slug`,
        `name{".$this->sl->languages('prefix')."}` AS `name`,
        `status`
        FROM `contents`
        WHERE {$where}
        {$order}
      ",MYSQLI_ASSOC);

      for($i=0;$i<sizeof($array);$i++) {
        $where = array();
        $where[] = "`parent` = {$array[$i]['id']}";
        $where = implode(" AND ",$where);

        $count = $this->sl->db->QuerySingleValue("SELECT COUNT(*) FROM `contents` WHERE {$where}");

        if($count > 0)
          $array[$i]['subs'] = $this->parents($array[$i]['id']);
      }
    }

    return $array;
  }

  public function FormGenerator($inputs = []) {
    $value = array();
  	if(isset($this->sl->get['parent']))
  		$this->data['parent'] = $this->sl->get['parent'];

    $this->sl->smarty->assign('data', $this->data);
    $this->sl->smarty->assign('module', $this->module);

    if(!empty($inputs))
    for($e=0;$e<sizeof($inputs);$e++) {
      switch ($inputs[$e]['module'] ?? '') {
        case 'uploader':
          $value[] = $this->uploader($inputs[$e],'plupload');
        break;
        case 'images':
          $value[] = $this->images($inputs[$e]);
        break;
        case 'products_connected':
          #$this->
          $this->sl->smarty->assign('input',$inputs[$e]);
          $value[] = $this->sl->smarty->fetch('input.parents.html', microtime());
        break;

        default:
          $dataAttributes = '';

          if(!empty($inputs[$e]['data'])) {
            $inputs[$e]['dataAttributes'] = $dataAttributes = @array_map(function($item, $key) {
              return 'data-'.$key.'="'.$item.'"';
            }, array_values($inputs[$e]['data']), array_keys($inputs[$e]['data']));

            $inputs[$e]['dataAttributes'] = implode(' ', $dataAttributes);
            $dataAttributes = &$inputs[$e]['dataAttributes'];
          }
          $inputs[$e]['grid'] = $inputs[$e]['grid'] ?? 'col-12';

          switch ($inputs[$e]['type']) {
            case 'accordions':
              if($inputs[$e]['multilanguage'] == 1) {
                for($z=0;$z<sizeof($this->sl->languages('langs'));$z++) {

                  if(isset($this->data["{$inputs[$e]['slug']}{{$this->sl->languages('langs')[$z]['prefix']}}"])) {
                    $data = (unserialize($this->data["{$inputs[$e]['slug']}{{$this->sl->languages('langs')[$z]['prefix']}}"]));
                    if($inputs[$e]['encode'] == '1')
                      $data = $this->sl->decode($data);

                    $this->sl->smarty->assign('accordion_data_'.$this->sl->languages('langs')[$z]['prefix'],
                      $data);
                  } else {
                    $this->sl->smarty->assign('accordion_data_'.$this->sl->languages('langs')[$z]['prefix'], array());
                  }
                }
              } else {
                if(isset($this->data[$inputs[$e]['slug']])) {
                  $this->sl->smarty->assign('accordion_data', (unserialize($this->data[$inputs[$e]['slug']])));
                } else
                  $this->sl->smarty->assign('accordion_data', array());
              }

              $this->sl->smarty->assign('input', $inputs[$e]);
              $value[] = $this->sl->smarty->fetch('accordions.html', uniqid());
            break;
            case 'parents':
            /*
              $inputs[$e]['options'] = $this->sl->tree_build(
                $this->sl->tree(0,0,isset($this->data[$inputs[$e]['slug']]), $this->self()['id_content'], $this->self()['id'])
              );
            */
              if(isset($this->data[$inputs[$e]['slug']]) && $this->data[$inputs[$e]['slug']] == 0) {
                $inputs[$e]['value'] = array('id' => 0,'name' => 'Yok');
              } else {
                if($inputs[$e]['multiple'] == '1') {
                  $ids = (is_array($this->data[$inputs[$e]['slug']]))?implode(',', $this->data[$inputs[$e]['slug']]):$this->data[$inputs[$e]['slug']];

                  $inputs[$e]['values'] = [];

                  if($ids)
                  $inputs[$e]['values'] = $this->sl->db->QueryArray("SELECT id,`name{tr}` as name FROM `contents`
                    WHERE `id` IN ({$ids}) ");
                } else {
                  $inputs[$e]['value'] = $this->sl->db->QuerySingleRowArray("SELECT id,`name{tr}` as name FROM `contents`
                    WHERE `id`='".$this->data[$inputs[$e]['slug']]."'");
                }
              }



              $inputs[$e]['options'] = $this->parents(0);

              $this->sl->smarty->assign('input',$inputs[$e]);
              $value[] = $this->sl->smarty->fetch('input.parents.html', microtime());
            break;

            case 'datepicker':
              $inputs[$e]['uniq'] = uniqid($inputs[$e]['slug']);
              $this->sl->smarty->assign('input',$inputs[$e]);
              $value[] = $this->sl->smarty->fetch('input.datepicker.html', microtime());
            break;

            case 'modules':
              $data = $this->sl->db->QueryArray("SELECT * FROM `content_modules`
                WHERE `type`='".$inputs[$e]['data_type']."'");

              $inputs[$e]['options'] = $data;

              $this->sl->smarty->assign('input',$inputs[$e]);
              $value[] = $this->sl->smarty->fetch('input.modules.html', microtime());
            break;

            case 'templates':
              $inputs[$e]['options'] = $this->sl->dir($this->sl->settings['templates']['path'] .DS. 'default' .DS. 'templates');

              $this->sl->smarty->assign('input',$inputs[$e]);
              $value[] = $this->sl->smarty->fetch('input.templates.html', microtime());
            break;

            case 'sl.order':
              if(isset($this->data['id'])) {
                $items = $this->parents($this->data['id'], !empty($this->data['sl_order']) ? "ORDER BY FIELD(`id`, {$this->data['sl_order']})" : '' );
                $this->sl->smarty->assign('items', $items);
              }

              $this->sl->smarty->assign('input',$inputs[$e]);
              $value[] = $this->sl->smarty->fetch('sl.order.html', microtime());
            break;

            case 'checkbox':
              $inputs[$e]['uniq'] = uniqid($inputs[$e]['slug']);
              $checked = '';

              for($z=0;$z<sizeof($inputs[$e]['values']);$z++) {
                $inputs[$e]['values'][$z]['checked'] =
                ((empty($this->data) AND $inputs[$e]['values'][$z]['checked'] == '1')
                OR (isset($this->data[$inputs[$e]['values'][$z]['slug']]) AND $this->data[$inputs[$e]['values'][$z]['slug']] == '1'))?' checked':'';

                $inputs[$e]['values'][$z]['uniq'] = uniqid($inputs[$e]['values'][$z]['slug']);
              }

              $this->sl->smarty->assign('input',$inputs[$e]);
              $value[] = $this->sl->smarty->fetch('input.checkbox.html', microtime());
            break;

            case 'switch':
              $inputs[$e]['checked'] = ((empty($checked) AND $inputs[$e]['checked'] == '1') OR (isset($this->data[$inputs[$e]['slug']]) AND $this->data[$inputs[$e]['slug']] == '1'))?' checked':'';

              $this->sl->smarty->assign('input',$inputs[$e]);
              $value[] = $this->sl->smarty->fetch('input.switch.html', microtime());
            break;

            case 'radio':
                $template = '
                <div class="'.$inputs[$e]['grid'].'">
                <div class="form-group">
                  <label class="d-block" for="'.$inputs[$e]['name'].'">'.$inputs[$e]['name'].'</label>
                  %INPUTS%
                </div>
                </div>
                ';
                $input_template = '
                <input type="radio" %DATA_ATTIBUTES% class="check"%CHECKED% id="%ID%" name="'.$inputs[$e]['slug'].'" value="%VALUE%">
                <label for="%ID%">%NAME%</label>
                ';

                $input_templates = '';
                $selected = '';

                for($z=0;$z<sizeof($inputs[$e]['values']);$z++) {
                  $selected = (
                    (empty($selected) AND isset($inputs[$e]['values'][$z]['checked']) AND $inputs[$e]['values'][$z]['checked'] == '1')
                    OR (
                    isset($this->data[$inputs[$e]['slug']]) AND
                    $this->data[$inputs[$e]['slug']] == $inputs[$e]['values'][$z]['value'])
                    )?' checked':'';

                  $input_templates .= str_replace(
                    array('%NAME%','%VALUE%','%ID%','%CHECKED%','%DATA_ATTIBUTES%'),
                    array(
                      $inputs[$e]['values'][$z]['name'],
                      $inputs[$e]['values'][$z]['value'],
                      uniqid($inputs[$e]['values'][$z]['value']),
                      $selected,
                      $this->prefix($dataAttributes, '')
                    ),
                    $input_template
                  );
                }

                $value[] = str_replace(
                  array('%INPUTS%'),
                  array($input_templates)
                  ,$template
                );
            break;
            case 'text':
              $wrap_uniq = uniqid('wrap');
              $inputs[$e]['muted'] = $inputs[$e]['muted'] ?? '';

              if($inputs[$e]['multilanguage'] == 1) {
                $template = '
                <div class="'.$inputs[$e]['grid'].'">
                  <div class="form-group">
                    <label for="'.$wrap_uniq.'">'.$inputs[$e]['name'].'</label>
                    <div class="input-group" id="'.$wrap_uniq.'">
                      %INPUTS%
                      <div class="input-group-append">
                      %BUTTONS%
                      </div>
                    </div>
                    <div class="form-control-feedback"> <small>'.$inputs[$e]['muted'].'</small> </div>
                  </div>
                </div>
                ';
                $input_template   = '<input type="text" data-uniq="%UNIQ%" data-wrap="%UNIQ_WRAP%" class="form-control%ACTIVE%" %DATA_ATTIBUTES% name="%SLUG%" value="%VALUE%" id="%SLUG%" placeholder="">';
                $button_template  = '<a href="#" data-lang="%PREFIX%" data-wrap="%UNIQ_WRAP%" data-uniq="%UNIQ%" class="input-group-text%ACTIVE%">%CODE%</a>';

                $_inputs = array();
                $_buttons = array();

                for($z=0;$z<sizeof($this->sl->languages('langs'));$z++) {
                  $active = ($z == 0?' active':' inactive');
                  $uniq = uniqid($inputs[$e]['slug'].'_lang_'.$this->sl->languages('langs')[$z]['prefix']);

                  $_inputs[] = str_replace(
                    array('%ACTIVE%', '%SLUG%','%VALUE%','%DATA_ATTIBUTES%', '%UNIQ%','%UNIQ_WRAP%'),
                    array(
                      $active,
                      $inputs[$e]['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}',
                      (isset($this->data[$inputs[$e]['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'])?
                        $this->data[$inputs[$e]['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}']:$inputs[$e]['default']),
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
                      $inputs[$e]['slug'],
                      $wrap_uniq,
                      strtoupper($this->sl->languages('langs')[$z]['prefix']),
                      $uniq,
                      $this->sl->languages('langs')[$z]['prefix']
                    ),
                      $button_template
                    );
                }
                $value[] = str_replace(
                  array('%INPUTS%','%BUTTONS%'),
                  array(implode("\n",$_inputs),implode("\n",$_buttons)),
                  $template
                );
              } else {
                $template = '
                <div class="'.$inputs[$e]['grid'].'">
                  <div class="form-group" id="'.$uniq.'">
                      <label for="%SLUG%">%NAME%</label>
                      <input %DATA_ATTIBUTES% type="text" data-uniq="'.$uniq.'" class="form-control" name="%SLUG%" id="%SLUG%" value="%VALUE%" placeholder="">
                      <div class="form-control-feedback"> <small>'.$input[$e]['muted'].'</small> </div>
                  </div>
                </div>
                ';

                $value[] = str_replace(
                  array('%SLUG%','%NAME%','%VALUE%','%DATA_ATTIBUTES%'),
                  array(
                    $inputs[$e]['slug'],
                    $inputs[$e]['name'],
                    (!empty($this->data[$inputs[$e]['slug']])) ? $this->data[$inputs[$e]['slug']] : $inputs[$e]['default'],
                    $this->prefix($dataAttributes, '')
                  ),
                    $template
                );
              }
            break;
            case 'textarea':
              $uniq = uniqid('editor');
              $_editor = '';
              $editor = '';

              if(isset($inputs[$e]['multilanguage']) AND $inputs[$e]['multilanguage'] == 1) {
                $template = '
                <div class="'.$inputs[$e]['grid'].'">
                <h5 class="card-title">%NAME%</h5>
                <ul class="nav nav-tabs" role="tablist">
                %TABS%
                </ul>
                <div class="tab-content tabcontent-border m-b-20">
                %TABWINDOWS%
                </div>
                </div>
                ';
                $tab_template = '<li class="nav-item"> <a data-lang="%PREFIX%" class="nav-link%ACTIVE%" data-toggle="tab" href="#tab_%ID%" role="tab"><span class="hidden-sm-up">%CODE%</span>
                <span class="hidden-xs-down">%NAME%</span></a> </li>';

                $tabwindow_template = '
                    <div class="tab-pane%ACTIVE%" id="tab_%ID%" role="tabpanel">
                        <div class="p-20">
                          %TEMPLATES%
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
                      md5($inputs[$e]['name'].$this->sl->languages('langs')[$z]['name']),
                      strtoupper($this->sl->languages('langs')[$z]['prefix']),
                      $active,
                      $this->sl->languages('langs')[$z]['prefix'],
                    ),
                    $tab_template
                  );

                  $templates = '';

                  if(!empty($inputs[$e]['template'])) {
                    $id = strtoupper($this->sl->languages('langs')[$z]['prefix']) .'_'. $uniq;

                    for($o=0;$o<sizeof($inputs[$e]['template']);$o++) {
                      $temp = htmlspecialchars($inputs[$e]['template'][$o]['value']);//base64_encode($inputs[$e]['template'][$o]['value']);
                      $templates = '<button data-id="'.$id.'" data-apply-template="'.$temp.'" type="button" class="btn btn-primary mb-2 mr-2">'.$inputs[$e]['template'][$o]['name'].'</button>';
                    }
                  }

                  $tabwindows[] = str_replace(
                    array('%TEMPLATES%','%ID%','%CODE%','%UNIQ%','%SLUG%','%ACTIVE%','%VALUE%','%DATA_ATTIBUTES%'),
                    array(
                      $templates,
                      md5($inputs[$e]['name'].$this->sl->languages('langs')[$z]['name']),
                      strtoupper($this->sl->languages('langs')[$z]['prefix']),
                      $uniq,
                      $inputs[$e]['slug'] .'{'. $this->sl->languages('langs')[$z]['prefix'] .'}',
                      $active,
                      (isset($this->data[$inputs[$e]['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'])?
                        $this->data[$inputs[$e]['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}']:''),
                      $this->prefix($dataAttributes, $this->sl->languages('langs')[$z]['prefix'])
                    ),
                    $tabwindow_template
                  );
                }

                $value[] = str_replace(
                  array('%NAME%','%TABS%','%TABWINDOWS%'),
                  array(
                    $inputs[$e]['name'],
                    implode("\n",$tabs),
                    implode("\n",$tabwindows)
                  ),
                  $template
                );
              } else {
                $_editor = (isset($inputs[$e]['custom_function']) AND $inputs[$e]['custom_function'] == 'editor_advanced')?
                '<a href="#" data-toggle="modal" data-target="#html5editoradvanced" data-id="'.$uniq.'" class="btn waves-effect waves-light btn-xs btn-info m-t-10"> <i class="fab fa-html5"></i> '.$this->sl->languages('Gelişmiş Editör').' </a>'
                :'';

                $value[] = '
                <div class="'.$inputs[$e]['grid'].'">
                <div class="form-group">
                  <label for="'.$inputs[$e]['name'].'">'.$inputs[$e]['name'].'</label>
                  <textarea '.$this->prefix($dataAttributes, '').' class="'.$editor.'form-control" id="'.$uniq.'" rows="10" name="'.$inputs[$e]['slug'].'">'.(isset($this->data[$inputs[$e]['slug']])?$this->data[$inputs[$e]['slug']]:'').'</textarea>
                  <div class="form-control-feedback"> <small></small> </div>
                  '.$_editor.'
                </div>
                </div>
                ';
              }
            break;

            default:
              // code...
            break;
          }

        break;
      }
    }

    return implode("\n", $value);
  }

  public function images($uploader) {
    $uploader = array_merge(
      array(
        'maxfilesize' => '10mb',
        'width' => '4000',
        'height' => '4000',
        'resize' => [],
        'quality' => '70',
        'multilanguage' => '0',
        'multi' => '0',
        'uniq' => uniqid($uploader['slug']),
        'uniq_' => uniqid($uploader['slug']),
        'id' => (isset($this->data['id'])?$this->data['id']:''),
        'group' => $this->self()['group'],
        'lang' => ''
      ),
      $uploader
    );

    if(!empty($uploader['types'])) {
      $uploader['mime_types'] = '';

      for($i=0;$i<sizeof($input['types']);$i++)
        $uploader['mime_types'] .= "{title : '".$uploader['types'][$i]['title']."', extensions : '".$uploader['types'][$i]['extensions']."'},";
    } else {
      $uploader['mime_types'] = "{title : '".$this->sl->languages('Tüm Dosyalar')."', extensions : '*'}";
    }
    $this->sl->smarty->assign('uploader',$uploader);

    if($uploader['multilanguage'] == '1') {
      for($i=0;$i<sizeof($this->sl->languages('langs'));$i++) {
        if(isset($this->data['id'])) {
          $prefix = $this->sl->languages('langs')[$i]['prefix'];

          $uploader['images'][$prefix] = $this->sl->db->QueryArray("SELECT * FROM `images`
            WHERE (`rid`='{$this->data['id']}' OR `_rid`='{$this->data['id']}') AND `group`='{$this->self()['group']}'
            AND `slug`='{$uploader['slug']}{{$prefix}}'
            ", MYSQLI_ASSOC);

          if(is_array($uploader['images'][$prefix])) {
            for($s=0;$s<sizeof($uploader['images'][$prefix]);$s++)
              $uploader['images'][$prefix][$s]['images'] = unserialize($uploader['images'][$prefix][$s]['images']);
          }
        }

        $_uploader =
                  array_merge($uploader,
                    array(
                      'slug' => $uploader['slug'] . "{{$this->sl->languages('langs')[$i]['prefix']}}",
                      'uniq' => $uploader['uniq'] .'_'. $this->sl->languages('langs')[$i]['id'],
                      'lang' => $this->sl->languages('langs')[$i]['prefix']
                    )
                  );

        $this->sl->smarty->assign('uploader',$_uploader);
        $this->sl->scripts(
          $this->sl->smarty->fetch($this->sl->settings['portal_theme_path'] . 'images.script.js', $uploader['uniq'], uniqid('FUCK_THIS_SHIT'))
        );
        $this->sl->smarty->assign('uploader',$uploader);
      }
    } else {
      if($this->data['id']) {
        $uploader['images'] = $this->sl->db->QueryArray("SELECT * FROM `images`
          WHERE (`rid`='{$this->data['id']}' OR `_rid`='{$this->data['id']}')
          AND `group`='{$this->self()['group']}'
          AND `slug`='{$uploader['slug']}'
          ORDER BY `sort` ASC
          ", MYSQLI_ASSOC);

        if(is_array($uploader['images'])) {
          for($i=0;$i<sizeof($uploader['images']);$i++)
            $uploader['images'][$i]['images'] = unserialize($uploader['images'][$i]['images']);
        }

        $this->sl->smarty->assign('uploader',$uploader);
      }

      $this->sl->scripts(
        $this->sl->smarty->fetch($this->sl->settings['portal_theme_path'] . 'images.script.js', $uploader['uniq'])
      );
    }

    $html = $this->sl->smarty->fetch($this->sl->settings['portal_theme_path'] . 'images.html', $uploader['uniq'], time());
    return $html;
  }

  function slug($string) {
	  $string = mb_strtolower($string,"UTF-8");
	  $string = str_replace(array('ş','ı','ü','ğ','ç','ö'),array('s','i','u','g','c','o'),$string);

	  $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	  return $slug;
	}

  public function uploader($input, $uploader, $template = '') {
    $input['grid'] = $input['grid'] ?? 'col-12';

    $input = array_merge(
      array(
        'muted' => '',
        'maxfilesize' => '10mb',
        'width' => '4000',
        'height' => '4000',
        'quality' => '80',
        'multilanguage' => '0',
        'multi' => '0',
        'group' => $this->module['group']
      ),
      $input
    );

    switch ($uploader) {
      case 'plupload':
        if(!empty($input['types'])) {
          $mime_types = '';

          for($i=0;$i<sizeof($input['types']);$i++)
            $mime_types .= "{title : '".$input['types'][$i]['title']."', extensions : '".$input['types'][$i]['extensions']."'},";
        } else {
          $mime_types = "{title : '".$this->sl->languages('Tüm Dosyalar')."', extensions : '*'}";
        }
        $uniq = uniqid($input['slug']);

        if(empty($template)) {
          $template = array();

          $template['item'] = '
            <a class="media" href="%URL%" data-fancybox>
              <button data-remove-image="%URL%" class="btn btn-danger waves-effect waves-light" type="button">
                <i class="fas fa-trash-alt"></i></button>
              %ELEMENT%
              <input type="hidden" name="%SLUG%" value="%URL%">
            </a>
          ';

          $resize = ($input['resize'] != '0')?
          "
          resize: {
            width: ".$input['width'].",
            height: ".$input['height'].",
            crop: false,
            quality: ".$input['quality'].",
            preserve_headers: false
          },
          ":"";

          $multipart_params = array();

          if(!empty($input['width']))
            $multipart_params[] = "'width' : '{$input['width']}'";

          if(!empty($input['height']))
            $multipart_params[] = "'height' : '{$input['height']}'";

          if(!empty($input['quality']))
            $multipart_params[] = "'quality' : '{$input['quality']}'";

          if(!empty($input['group']))
            $multipart_params[] = "'group' : '{$input['group']}'";

          $multipart_params = (sizeof($multipart_params) > 0) ? implode(",\n", $multipart_params) : '';

          $multipart_params = "
          multipart_params : {
            {$multipart_params}
          },";

          $template['script'] = "
          var %UNIQ%_uploader = new plupload.Uploader({
              runtimes : 'html5,html4',

              browse_button : '%UNIQ%_select',
              container: document.getElementById('%UNIQ%_container'),
              url : 'upload.php',
              multi_selection: ".$input['multi'].",
              drop_element: '%UNIQ%_container',
              {$resize}
              {$multipart_params}

              filters : {
                  max_file_size : '".$input['maxfilesize']."',
                  mime_types: [
                      $mime_types
                  ]
              },

              init: {
                  PostInit: function() {
                  },

                  FilesAdded: function(up, files) {
                    ".($input['multi'] != true?'while (up.files.length > 1) { up.removeFile(up.files[0]); }':'')."

                    $('%UNIQ%_console').addClass('info');

                    plupload.each(files, function(file) {
                      $('#%UNIQ%_console') . html( '<div id=' + file.id + '>' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>' ).show();
                    });

                    %UNIQ%_uploader.start();
                  },

                  UploadProgress: function(up, file) {
                    $('#%UNIQ%_console #' + file.id + ' b').html('".$this->sl->languages('Dosya yükleniyor').", ' + file.percent + '%');
                  },

                  FileUploaded: function(up, file, response) {
          					var obj = jQuery.parseJSON(response.response);
          					var re = /(?:\.([^.]+))?$/;
          					var ext = re.exec(obj.file)[1];

                    var element = '".str_replace(array("\n","\t"),'',$template['item'])."';

                    $('#%UNIQ%_console #' + file.id + ' b').html('".$this->sl->languages('Yüklendi.')."');

                    element = $(element).wrap('<div>').parent().html().replace(/%URL%/g,obj.file);
                    var IMG = '<img src=\"'+obj.file+'\">';
                    element = $(element).wrap('<div>').parent().html().replace(/%ELEMENT%/g,IMG);
                    $('#%UNIQ%_images').append(element);

                    /*
          					if(ext.toLowerCase() == 'pdf' || ext.toLowerCase() == 'mp4') {
          					} else {
                      $('#%UNIQ%_console #' + file.id + ' b').html('".$this->sl->languages('Yüklendi.')."');
          					}
                    */
          				},

                  Error: function(up, err) {
                    $('#%UNIQ%_console').html('".$this->sl->languages('Yükleme hatası oluştu, tekrar deneyiniz.')."');
                  }
              }
          });

          %UNIQ%_uploader.init();
          ";

          $template['main'] = '
          <div class="'.$input['grid'].'">
            <div class="card">
                <div class="card-header">
                  <h4 class="card-title m-0">%NAME%</h4>
                  <p class="card-text">%DESC%</p>
                </div>

                <ul class="nav nav-tabs customtab" role="tablist">
                  %LANGS%
                </ul>

                <div class="tab-content">
                  %BODY%
                </div>
            </div>
          </div>
          ';

          $template['body'] = '
            <div role="tabpanel" class="tab-pane%ACTIVE%" id="%UNIQ%">
              <div class="p-0">
                <div class="card-body" id="%UNIQ%_container">
                    <div id="%UNIQ%_console" class="alert alert-info">Yüklemeye hazır.</div>
                    <small class="d-block text-muted">'.$input['types'][0]['title'].' ('.$input['types'][0]['extensions'].')</small>

                    <div id="%UNIQ%_images" class="images">
                    %ITEMS%
                    </div>

                    <small class="d-block text-muted mb-2">'.$input['muted'].'</small>

                    <button id="%UNIQ%_select" class="btn btn-outline-primary waves-effect waves-light" type="button">
                    <span class="btn-label"> <i class="fas fa-mouse-pointer"></i> </span>'.$this->sl->languages('Dosya Seç').'</button>
                    <!--
                    <button type="button" class="btn waves-effect waves-light btn-primary"><i class="fas fa-upload"></i> '.$this->sl->languages('Yükle').'</a>
                    -->
                </div>
              </div>
            </div>
          ';
          $template['lang_tab'] = '
          <li class="nav-item"> <a data-lang="%PREFIX%" class="nav-link%ACTIVE%" data-toggle="tab" href="#%UNIQ%" role="tab">
            <span class="hidden-sm-up">%PREFIX%</span> <span class="hidden-xs-down">%NAME%</span></a>
          </li>';
        }

        if($input['multilanguage']) {
          $tabs = array();
          $tab_body = array();

          for($z=0;$z<sizeof($this->sl->languages('langs'));$z++) {
            $uniq = uniqid($this->sl->languages('langs')[$z]['prefix'] .'_'. $input['slug']);
            $active = ($z == 0?' active':'');

            $script = str_replace(
              array('%UNIQ%', '%PREFIX%', '%SLUG%'),
              array(
                $uniq,
                $this->sl->languages('langs')[$z]['prefix'],
                $input['slug'].'{'.$this->sl->languages('langs')[$z]['prefix'].'}[]'
              ),
              $template['script']
            );

            $this->sl->scripts($script);

            $tabs[] = str_replace(
              array('%SLUG%','%PREFIX%','%NAME%','%ACTIVE%', '%UNIQ%'),
              array(
                md5($input['name'].$this->sl->languages('langs')[$z]['name']),
                strtoupper($this->sl->languages('langs')[$z]['prefix']),
                $this->sl->languages('langs')[$z]['name'],
                $active,
                $uniq
              ),
              $template['lang_tab']
            );

            $items = array();

            if(!empty($this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'])) {
              $this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'] =
              (is_array($this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'])?
              $this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}']:
              unserialize($this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}']));

              for($s=0;$s<sizeof($this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}']);$s++) {
                $file = $this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'][$s];
                $ext = end(explode('.', $file));

                $item =
                (strtolower($ext) == 'mp4')?
                'VIDEO DOSYASI'
                :
                "<img src=\"{$file}\">";

                $items[] = str_replace(
                  array('%URL%','%ELEMENT%','%SLUG%'),
                  array(
                    $this->data[$input['slug'] . '{'.$this->sl->languages('langs')[$z]['prefix'].'}'][$s],
                    $item,
                    $input['slug'] .'{'. $this->sl->languages('langs')[$z]['prefix'].'}[]'
                  ),
                  $template['item']
                );
              }
            } else {
            }

            $tab_body[] = str_replace(
              array('%ITEMS%','%UNIQ%','%ACTIVE%'),
              array(@implode("\n",$items), $uniq, $active),
              $template['body']
            );
          }

          $template['main'] = str_replace(
            array('%LANGS%','%BODY%'),
            array(
              implode("\n",$tabs),
              @implode("\n",$tab_body)
            ),
            $template['main']
          );
          // Multilanauge end
        } else {
          $items = array();

          if(!empty($this->data[$input['slug']])) {
            $this->data[$input['slug']] =
            (is_array($this->data[$input['slug']])?$this->data[$input['slug']]:unserialize($this->data[$input['slug']]));

            for($z=0;$z<sizeof($this->data[$input['slug']]);$z++) {
              $items[] = str_replace(
                array('%URL%','%ELEMENT%','%SLUG%'),
                array(
                  $this->data[$input['slug']][$z],
                  '<img src="'.$this->data[$input['slug']][$z].'">',
                  $input['slug'].'[]'
                ),
                $template['item']
              );
            }
          }

          $template['main'] = str_replace(
            array('%LANGS%','%BODY%'),
            array('',
              str_replace(
                array('%UNIQ%','%ITEMS%', '%ACTIVE%'),
                array($uniq, implode("\n", $items), ' active'),
                $template['body']
              )
            ),
            $template['main']
          );

          $script = str_replace(
            array('%NAME%','%UNIQ%','%SLUG%'),
            array($input['name'], $uniq, $input['slug'] .'[]'),
            $template['script']
          );

          $this->sl->scripts($script);
        }
      break;

      default:
        // Default uploader input
      break;
    }
    $template = str_replace(
      array('%NAME%','%DESC%'),
      array($input['name'], isset($input['desc'])?$input['desc']:''),
      $template['main']
    );

    return $template;
  }

  public function generate_option($option){
    $options = array();

    for($i=$option[0];$i<=$option[1];$i++) {
      $options[] = "<option value='{$i}'>{$i}</option>";
    }

    return implode("\n", $options);
  }

  public function file($com) {
    switch ($com) {
      case 'header':
        include('header.php');
      break;
      case 'footer':
        include('footer.php');
      break;
      case 'scripts':
        include('scripts.php');
      break;

      default:
        // code...
        break;
    }
  }

}
