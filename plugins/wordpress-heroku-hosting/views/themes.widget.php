<style media="screen">
.wrapper { display: flex; overflow-x: scroll; }
.theme { position: relative; display: flex; flex-direction: column; margin-right: 10px; height: 300px; min-width: 300px; background: rgba(0, 0, 0, 0.05); border: 1px solid #eee; }
.theme .content { padding: 20px; flex: 1; }
.theme .footer { display: flex; background: #fff; }
.theme .footer a { padding: 10px; text-decoration: none; border-right: 1px solid #eee; }
.theme small.version { padding: 5px; position: absolute; top: 0; right: 0; background: #fff; }
</style>

<div class="wrapper">
  <?php foreach ($themes as $key => $value): ?>
    <div class="theme">
      <div class="content">
        <h2><?= $value['Name']; ?></h2>
        <p><?= $value['Description']; ?></p>
        <a href="<?= $value['AuthorURI']; ?>"><?= $value['AuthorName']; ?></a>
      </div>

      <?php if (!empty($value['AuthorURI']) || !empty($value['PluginURI'])): ?>
        <div class="footer">
          <?php if (!empty($value['AuthorURI'])): ?>
            <a href="<?= $value['AuthorURI']; ?>" target="_blank">Author<span class="dashicons dashicons-external"></span></a>
          <?php endif; ?>
          <?php if (!empty($value['PluginURI'])): ?>
            <a href="<?= $value['PluginURI']; ?>" target="_blank">Plugin<span class="dashicons dashicons-external"></span></a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <small class="version">v<?= $value['Version']; ?></small>
    </div>
  <?php endforeach; ?>
</div>
