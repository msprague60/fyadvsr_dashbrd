<?php

  // This is a simple collection of functions local to the App.

function find_info($item, $data, $strict = false) {
  foreach ($data as $data_row) {
    if (array_search($item, $data_row, $strict)) {
      return true;
    }
  }

  return false;
}

?>
