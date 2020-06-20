<?php

function rmdir_recursive($directory) {
  foreach (scandir($directory) as $file) {
    if ('.' === $file || '..' === $file) continue;
    if (is_dir("$directory/$file")) rmdir_recursive("$directory/$file");
    else unlink("$directory/$file");
  }
  rmdir($directory);
}

foreach (array(
  __DIR__.'/config/wp-config.php' => __DIR__.'/public/wp-config.php',
  __DIR__.'/plugins' => __DIR__.'/public/wp-content/plugins',
  __DIR__.'/themes' => __DIR__.'/public/wp-content/themes'
) as $key => $value):
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

putenv('WP_DEBUG=true');
putenv('DISALLOW_FILE_EDIT=true');
putenv('DISALLOW_FILE_MODS=true');

exec('php -S 0.0.0.0:8000 -t '.__DIR__.'/public');
