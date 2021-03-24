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
  'taxonomies'         => ['category', 'post_tag'],
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
      .question-meta table td textarea { width: 100%; }
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
                <td>
                  <textarea required type="text" :name="'options[' + index + '][title]'" placeholder="Añadir texto de la posible respuesta" x-model="option.title"></textarea>
                </td>
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
      <?php $groups = $wpdb->get_col("select distinct(meta_value) from {$wpdb->postmeta} where meta_key = 'group' and meta_value is not null and meta_value != ''"); ?>
      <?php $postGroup = get_post_meta($post->ID, 'group', true); ?>
      <div x-data='{ newGroup: "", groups: <?= json_encode(!empty($groups) ? $groups : []); ?> }'>
        <select x-ref="groupSelect" name="group" style="width: 100%">
          <option value="">Free (Gratuita)</option>
          <template x-for="group in groups" :key="group">
            <option :value="group" x-text="group" :selected="group === '<?= $postGroup; ?>'"></option>
          </template>
        </select>
        <hr>
        <input type="text" style="width: 100%" x-model="newGroup" placeholder="Añade un nuevo grupo a la lista">
        <span class="button" style="margin-top: 5px" @click="groups.push(newGroup); newGroup = ''; setTimeout(function() { $refs.groupSelect.selectedIndex = $refs.groupSelect.length - 1; }, 250)">Añadir nuevo grupo</span>
        <p>Ten en cuenta el nombre de los grupos con atención. Ya que estos grupos son los que dividen las preguntas para venderlas por paquetes</p>
        <small>* Si cambias las preguntas y el grupo no está en ningún uso, este se eliminará de la lista para evitar tener paquetes en venta sin preguntas.</small>
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

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_field/
 */
register_rest_field('question', 'options', [
  'schema'          => [
    'type' => 'array',
    'items' => ['title' => 'string', 'correct' => 'boolean']
  ],
  'update_callback' => null,
  'get_callback'    => function($object, $fieldName, $request) {
    $options = get_post_meta($object['id'], $fieldName, true);
    return !empty($options) ? $options : [];
  }
]);

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_field/
 */
register_rest_field('question', 'group', [
  'schema'          => 'string',
  'update_callback' => null,
  'get_callback'    => function($object, $fieldName, $request) {
    $group = get_post_meta($object['id'], $fieldName, true);
    return !empty($group) ? $group : null;
  }
]);

/**
 * Filtering by group
 */
add_action('admin_init', function () {
  add_action('restrict_manage_posts', function () {
    global $wpdb, $table_prefix, $typenow;

    if ($typenow !== 'question') {
      return;
    }

    $groups = $wpdb->get_col("
      select distinct(meta_value) from {$wpdb->postmeta}
      where meta_key = 'group' and meta_value is not null
    ");

    printf('<select name="group">');
    !isset($_GET['group']) && printf('<option disabled selected>Todas</option>');
    foreach ($groups as $group):
      printf(
        '<option value="%s"%s>%s</option>',
        $group,
        isset($_GET['group']) && $group === $_GET['group'] ? 'selected' : '',
        empty($group) ? 'Free (Gratuita)' : $group
      );
    endforeach;
    printf('</select>');
  });

  add_filter('parse_query', function($query) {
    global $pagenow;

    if (
      isset($_GET['post_type']) &&
      $_GET['post_type'] === 'question' &&
      $pagenow === 'edit.php' &&
      isset($_GET['group'])
    ):
      $query->set('meta_query', [
        ['key' => 'group', 'value' => $_GET['group'], 'compare' => '=']
      ]);
    endif;
  });
});

/**
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_posts_columns
 */
add_filter('manage_question_posts_columns', function($columns) {
  $dateColumn = $columns['date'];
  unset($columns['date']);
  $columns['group'] = 'Grupo (Ventas)';
  $columns['date'] = $dateColumn;
  return $columns;
});

/**
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/manage_posts_custom_column
 */
add_action('manage_question_posts_custom_column', function($column, $postId) {
  if (in_array($column, ['group'])):
    $value = get_post_meta($postId, $column, true);
    print($value ? $value : 'Free (Gratuita)');
  endif;
}, 10, 2);
