<div style="text-align: center"><small>
  [Step 1 out of 3] - Upload your file
</small></div><hr/>

<form method="post" enctype="multipart/form-data">
  <table border="0" align="center">
    <tr>
      <td>Source CSV file to import:</td>
      <td rowspan="30" width="10px">&nbsp;</td>
      <td><input type="file" name="file_source" id="file_source" class="edt" value="<?=$file_source?>"></td>
    </tr>
    <tr>
      <td>Use CSV header:</td>
      <td><input type="checkbox" name="use_csv_header" id="use_csv_header" <?=(isset($_POST["use_csv_header"]) ? 'checked="checked"' : '' )?>/></td>
    </tr>
    <tr>
      <td>Separate char:</td>
      <td><input type="text" name="field_separate_char" id="field_separate_char" class="edt_30"  maxlength="1" value="<?=(""!=$_POST["field_separate_char"] ? htmlspecialchars($_POST["field_separate_char"]) : "")?>"/> <small>(leave empty for auto-detect)</small></td>
    </tr>
    <tr>
      <td>Enclose char:</td>
      <td><input type="text" name="field_enclose_char" id="field_enclose_char" class="edt_30"  maxlength="1" value="<?=(""!=$_POST["field_enclose_char"] ? htmlspecialchars($_POST["field_enclose_char"]) : htmlspecialchars("\""))?>"/></td>
    </tr>
    <tr>
      <td>Escape char:</td>
      <td><input type="text" name="field_escape_char" id="field_escape_char" class="edt_30"  maxlength="1" value="<?=(""!=$_POST["field_escape_char"] ? htmlspecialchars($_POST["field_escape_char"]) : "\\")?>"/></td>
    </tr>
    <tr>
      <td>Export to table:</td>
      <td>
        <select name="table" id="table" class="edt">
          <option value="0">- select -</option>
        <?
          if(!empty($arr_tables))
            foreach($arr_tables as $row)
              foreach($row as $table):
        ?>
          <option value="<?=$table?>"<?=($table == $_SESSION["table"] ? 'selected="selected"' : '')?>><?=$table?></option>
        <? endforeach;?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Encoding:</td>
      <td>
        <select name="encoding" id="encoding" class="edt">
        <?
          if(!empty($arr_encodings))
            foreach($arr_encodings as $charset=>$description):
        ?>
          <option value="<?=$charset?>"<?=($charset == $attributes["encoding"] ? 'selected="selected"' : '')?>><?=$description?></option>
        <? endforeach;?>
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" align="center"><input type="Submit" name="Go" value="Upload the file" class="btn" onclick="var s = document.getElementById('file_source'); if(null != s && '' == s.value) {alert('Define file name'); s.focus(); return false;} var s = document.getElementById('table'); if(null != s && 0 == s.selectedIndex) {alert('Define table name'); s.focus(); return false;}"></td>
    </tr>
  </table>
</form>