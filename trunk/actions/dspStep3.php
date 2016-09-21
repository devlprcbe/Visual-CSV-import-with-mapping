<div style="text-align: center"><small>
  [Step 3 out of 3] - Final import of data
</small></div><hr/>

<?php echo (!empty($error) ? '<hr/><span style="font-weight: bold; color: red">Messages: ' . $error . '</span>' : "")?>

<?php echo ( empty($error) && ($fQuickCSV->rows_count) > 0 ? '<hr/>Rows in the table now: ' . $fQuickCSV->rows_count  : "")?>

