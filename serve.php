<?php

foreach (array(
  __DIR__.'/config/wp-config.php' => __DIR__.'/public/wp-config.php',
  __DIR__.'/plugins' => __DIR__.'/public/wp-content/plugins',
  __DIR__.'/theme' => __DIR__.'/public/wp-content/themes/theme'
) as $key => $value):
  if (file_exists($value) && is_dir($value)):
    @rmdir($value);
  elseif (file_exists($value) && is_file($value)):
    @unlink($value);
  endif;

  @symlink($key, $value);
endforeach;

exec('php -S localhost:8000 -t '.__DIR__.'/public');
