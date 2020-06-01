<?php

class CustomField
{
  static $types = array(
    'text', 'date', 'time', 'textarea', 'select'
  );

  public function __construct($config)
  {
    $this->name = $config['name'];
    $this->key = $config['key'];
    $this->type = $config['type'];
    $this->options = @$config['options'];
    $this->show_in_rest = boolval(@$config['show_in_rest']);
    $this->required = boolval(@$config['required']);
    $this->value = !empty($config['value']) ? $config['value'] : null;
  }

  public function setValue($value)
  {
    $this->value = $value;
  }

  public function register()
  {
    /** @link https://developer.wordpress.org/reference/functions/register_meta/ */
    register_meta('post', $this->key, array(
      // 'object_subtype' => null,
      'type' => 'string', // TODO: (Extend types) 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
      'description' => "Field {$this->name}",
      'single' => true, // TODO: Extend for repeateable fields
      // 'sanitize_callback' => function() {},
      // 'auth_callback' => function() {},
      'show_in_rest' => $this->show_in_rest
    ));
  }

  public function render()
  {
    switch ($this->type) {
      case 'text': return $this->renderText();
      case 'date': return $this->renderDate();
      case 'time': return $this->renderTime();
      case 'textarea': return $this->renderTextarea();
      case 'select': return $this->renderSelect();
      case 'number': return $this->renderSelect();
      default: return $this->renderText();
    }
  }

  public function getRequiredAttr()
  {
    if ($this->required) {
      return 'required';
    }
  }

  public function renderText() { ?>
    <div style="margin-bottom: 5px">
      <label for="<?= $this->key; ?>"><?= $this->name; ?></label>
      <input style="width: 100%" id="<?= $this->key; ?>" class="form-control" type="text" name="<?= $this->key; ?>" value="<?= $this->value; ?>" <?= $this->getRequiredAttr(); ?>>
    </div><hr>
  <?php }

  public function renderDate() { ?>
    <div style="margin-bottom: 5px">
      <label for="<?= $this->key; ?>"><?= $this->name; ?></label>
      <input style="width: 100%" id="<?= $this->key; ?>" class="form-control" type="date" name="<?= $this->key; ?>" value="<?= $this->value; ?>" <?= $this->getRequiredAttr(); ?>>
    </div><hr>
  <?php }

  public function renderTime() { ?>
    <div style="margin-bottom: 5px">
      <label for="<?= $this->key; ?>"><?= $this->name; ?></label>
      <input style="width: 100%" id="<?= $this->key; ?>" class="form-control" type="time" name="<?= $this->key; ?>" value="<?= $this->value; ?>" <?= $this->getRequiredAttr(); ?>>
    </div><hr>
  <?php }

  public function renderTextarea() { ?>
    <div style="margin-bottom: 5px">
      <label for="<?= $this->key; ?>"><?= $this->name; ?></label>
      <textarea style="width: 100%" id="<?= $this->key; ?>" class="form-control" name="<?= $this->key; ?>" <?= $this->getRequiredAttr(); ?>><?= $this->value; ?></textarea>
    </div><hr>
  <?php }

  public function renderSelect() { ?>
    <div style="margin-bottom: 5px">
      <label for="<?= $this->key; ?>"><?= $this->name; ?></label>
      <select style="width: 100%" class="form-control" name="<?= $this->key; ?>" <?= $this->getRequiredAttr(); ?>>
        <?php foreach (explode("\n", $this->options) as $option): ?>
          <option <?= $this->value === $option ? 'selected' : ''; ?> value="<?= $option; ?>"><?= $option; ?></option>
        <?php endforeach; ?>
      </select>
    </div><hr>
  <?php }

  public function renderNumber() { ?>
    <div style="margin-bottom: 5px">
      <label for="<?= $this->key; ?>"><?= $this->name; ?></label>
      <input style="width: 100%" id="<?= $this->key; ?>" type="number" name="<?= $this->key; ?>" value="<?= $this->value; ?>" <?= $this->getRequiredAttr(); ?>>
    </div><hr>
  <?php }
}
