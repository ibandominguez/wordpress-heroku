<?php

function rmdir_recursive($directory) {
  foreach (scandir($directory) as $file) {
    if ('.' === $file || '..' === $file) continue;
    if (is_dir("$directory/$file")) rmdir_recursive("$directory/$file");
    else unlink("$directory/$file");
  }
  rmdir($directory);
}

foreach ([
  __DIR__.'/src/config/wp-config.php' => __DIR__.'/public/wp-config.php',
  __DIR__.'/src/plugins' => __DIR__.'/public/wp-content/plugins',
  __DIR__.'/src/themes' => __DIR__.'/public/wp-content/themes',
  __DIR__.'/src/languages' => __DIR__.'/public/wp-content/languages'
] as $key => $value):
  if (is_link($value)):
    continue;
  endif;

  if (file_exists($value) && is_dir($value)):
    @rmdir_recursive($value);
  elseif (file_exists($value) && is_file($value)):
    @unlink($value);
  endif;

  @symlink($key, $value);
endforeach;

exec(implode([
  'DISALLOW_FILE_EDIT=false',
  'DISALLOW_FILE_MODS=false',
  'DEBUG=true',
  'WP_DEBUG=true',
  'WP_DEBUG_DISPLAY=true',
  'SAVEQUERIES=true',
  'php -S 0.0.0.0:8000 -t '.__DIR__.'/public'
], ' '));
