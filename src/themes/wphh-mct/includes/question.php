<?php

/**
 * @link https://developer.wordpress.org/reference/functions/register_post_type/
 */
register_post_type('question', [
  'label'              => 'Preguntas',
  'public'             => true,
  'publicly_queryable' => true,
  'show_ui'            => true,
  'show_in_menu'       => true,
  'query_var'          => true,
  'rewrite'            => ['slug' => 'questions'],
  'capability_type'    => 'post',
  'show_in_rest'       => true,
  'rest_base'          => 'questions',
  'has_archive'        => true,
  'hierarchical'       => true,
  'menu_position'      => null,
  'taxonomies'         => ['category'],
  'supports'           => ['title', 'thumbnail'],
  'register_meta_box_cb' => function() {
    add_meta_box('options_meta_box', 'Opciones de respuesta', function($post) { ?>
      <?php wp_nonce_field('question_meta', 'question_meta_nonce'); ?>
      <?php $options = get_post_meta($post->ID, 'options', true); ?>
      <style media="screen">
      .question-meta {}
      .question-meta input[type=text] { width: 100%; }
      .question-meta table { width: 100%; }
      .question-meta table th { text-align: left; padding: 10px; border: 1px dashed #eee; }
      .question-meta table td { text-align: left; padding: 5px; border: 1px solid #eee; }
      </style>
      <div class="question-meta" x-data='{ options: <?= !empty($options) ? json_encode($options) : '[{ title: "", correct: false }]'; ?> }'>
        <table>
          <thead>
            <tr>
              <th>Correcta</th>
              <th style="width: 100%">Opción de respuesta</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <template x-for="(option, index) in options" :key="option">
              <tr>
                <td style="text-align: center"><input type="checkbox" :name="'options[' + index + '][correct]'" x-model="option.correct" value="true" /></td>
                <td><input required type="text" :name="'options[' + index + '][title]'" placeholder="Añadir texto de la posible respuesta" x-model="option.title" /></td>
                <td><span class="button" @click="options.splice(index, 1)">Borrar</span></td>
              </tr>
            </template>
          </tbody>
        </table>
        <span class="button" style="margin-top: 5px" @click="options.push({ title: '', correct: false })">Añadir nueva opción</span>
      </div>
      <script type="text/javascript">
      jQuery(document).ready(function () {
        var $postTitleElement = jQuery('[name=post_title]');
        var $textAreaElement  = jQuery('<textarea />', { id: 'title', name: 'post_title', style: 'width: 100%' })
        $textAreaElement.append($postTitleElement.val());
        $postTitleElement.replaceWith($textAreaElement);
        $textAreaElement.animate({ height: '100px' });
      });
      </script>
    <?php }, null, 'advanced', 'high');

    add_meta_box('group_meta_box', 'Grupo', function($post) { ?>
      <?php global $wpdb; ?>
      <?php $groups = $wpdb->get_col("select distinct(meta_value) from {$wpdb->postmeta} where meta_key = 'group'"); ?>
      <?php $postGroup = get_post_meta($post->ID, 'group', true); ?>
      <div x-data='{ newGroup: "", groups: <?= is_array($groups) ? json_encode($groups) : '[]'; ?> }'>
        <select x-ref="groupSelect" name="group" style="width: 100%">
          <option value="">Free (Gratuita)</option>
          <?php if (!empty($postGroup)): ?>
            <option value="<?= $postGroup; ?>" selected><?= $postGroup; ?></option>
          <?php endif; ?>
          <template x-for="group in groups" :key="group">
            <option :value="group" x-text="group"></option>
          </template>
        </select>
        <hr>
        <input type="text" style="width: 100%" x-model="newGroup" placeholder="Añade un nuevo grupo a la lista">
        <span class="button" style="margin-top: 5px" @click="groups.push(newGroup); newGroup = ''; setTimeout(function() { $refs.groupSelect.selectedIndex = $refs.groupSelect.length - 1; }, 250)">Añadir nuevo grupo</span>
        <p>Ten en cuenta el nombre de los grupos con atención. Ya que estos grupos son los que dividen las preguntas para venderlas por paquetes</p>
      </div>
    <?php }, 'question', 'side');
  }
]);

/**
 * @link https://developer.wordpress.org/reference/hooks/save_post/
 */
add_action('save_post', function($postId) {
  $nonce  = @$_POST['question_meta_nonce'];
  $fields = ['options', 'group'];

  if (!wp_verify_nonce($nonce, 'question_meta') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)):
    return $postId;
  endif;

  foreach ($_POST as $key => $value):
    if (in_array($key, $fields)):
      update_post_meta($postId, $key, $value);
    endif;
  endforeach;
});

// TODO: Add rest field options, group
