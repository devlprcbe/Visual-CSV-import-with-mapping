<?php
error_reporting(0);
ini_set("error_reporting", E_ALL & ~E_DEPRECATED);
class DB 
{
  public $login;
  public $password;
  public $dbname;
  public $host;
  
  public $link;
  
  public $fResult;  
  public $fRowsNumber;
  public $fErrorCode;
  public $fError;
  public $fRecordSet;
  public $fQueries;
  public $sql;
  public $dump;
  public $cache_count;
  protected $iterator;
  
  function db($host, $login, $password, $dbname)
  {
    $this->host = $host;
    $this->login = $login;
    $this->password = $password;
    $this->dbname = $dbname;
    $this->fRowsNumber = -1;
    $this->fRecordSet = array();
    $this->fQueries = array();
    $this->iterator = 0;
    $this->dump = array("total_time"=>0);
    $this->cache_count = array();
}

  function show_dump($caption="Dump", $count_percentage=true)
  {
    if($count_percentage)
    {
      for($i=1; $i<sizeof($this->dump); $i++)
      {
        $this->dump[$i]["percent"] = number_format($this->dump[$i]["time"]/$this->dump["total_time"]*100, 2) . " %";
        $this->dump[$i]["percent_graph"] = str_repeat("*", round($this->dump[$i]["percent"]));
      }
    }
    
    echo "<pre style=\"border:2px solid blue;margin-left:10px;background-color:white;\"><b>".htmlspecialchars($caption)."</b>:<br/>";
    print_r($this->dump);
    echo "</pre>";
    return true;
  }

  function connect()
  {
    //
  }

  function query($sql)
  {
    $this->sql = $sql;;
    $this->iterator++;
    $this->fQueries[$this->iterator]["sql"] =  nl2br($sql);
    
  }

  function getRowsCount()
  {
    return $this->fRowsNumber;
  }

  function getID()
  {
    //
  }

  function getDump()
  {
    return $this->fQueries;
  }

  function update($table_name, $field, $value, $id="", $id_field="")
  {
    $sql = "UPDATE `".$this->escape($table_name)."` SET
            `".$this->escape($field)."`='".$this->escape($value)."'
            ".
            ( empty($id) ? '' : 'WHERE '.(empty($id_field) ? '`id`' : '`'.$this->escape($id_field).'`') . "='". $this->escape($id) . "'");
    $res = $this->query($sql);
    //new dBug($sql);
    $rez = $this->getRowsCount();
    return $rez;
  }
  
  function update_raw($table_name, $field, $raw_value, $id="", $id_field="")
  {
    $sql = "UPDATE `".$this->escape($table_name)."` SET
            `".$this->escape($field)."`=".$raw_value."
            ".
            ( empty($id) ? '' : 'WHERE '.(empty($id_field) ? '`id`' : '`'.$this->escape($id_field).'`') . "='". $this->escape($id) . "'");
    $res = $this->query($sql);
    //new dBug($sql);
    $rez = $this->getRowsCount();
    return $rez;
  }
  
  function update_array($table, $data, $where)
  {
    foreach ($where as $k => $v)
    {
      $where[$k] = '`' . trim($k, '`') . '`' . " = '" . $this->escape($v) . "'";
    }
    
    foreach ($where as $k => $v)
    {
      unset($data[$k]);
    }
    
    $query = "UPDATE " . $table . "
      " . $this->assoc2set($data, true) . "
      WHERE " . join("\n\tAND ", $where);
    
    $this->query($query);
    
    return $this->fRowsNumber;
  }

  function delete($table, $where, $limit = 0)
  {
    foreach ($where as $k => $v)
    {
      $where[$k] = $k . "='" . $this->escape($v) . "'";
    }
  
    $query = "DELETE FROM `{$table}`
      WHERE " . join("\n\tAND", $where) . "
      " . ($limit > 0 ? 'LIMIT '.$limit : '') . "
    ";
    
    $this->query($query);
  
    return $this->fRowsNumber;
  }

  function delete_group($table, $where_in_field, $where_in_arr, $limit = 0)
  {
    foreach ($where_in_arr as $k => $v)
    {
      $where[$k] = $this->escape($v) . "'";
    }
  
    $query = "DELETE FROM `{$table}`
      WHERE `{$where_in_field}` IN (" . join(",", $where_in_arr) . ")
      " . ($limit > 0 ? 'LIMIT '.$limit : '') . "
    ";
    
    $this->query($query);
  
    return $this->fRowsNumber;
  }

  function assoc2set($data, $slash = false)
  {
    foreach ($data as $k => $v)
    {
      if ($slash)
      {
        $v = $this->escape($v);
      }

      $data[$k] = '`' . $k . "` = '" . $v . "'";
    }

    return 'SET ' . join(",\n\t", $data);
  }

  function insert($table, $data)
  {
    $query = "INSERT INTO `" . $table . "`
      " . $this->assoc2set($data, true) . "
    ";

    return $this->query($query) ? $this->getId() : false;
  }

}

class mysql extends db 
{
  public $time;
  function mysql($host, $login, $password, $dbname)
  {
    parent::db($host, $login, $password, $dbname);
  }

  function connect()
  {
    $this->link = @mysql_connect($this->host, $this->login, $this->password, false, 128);
    return !empty($this->link) && mysql_select_db($this->dbname);
  }

  function query($sql, $debug=false)
  {
//    if($debug)
    {
      list($usec, $sec) = explode(" ", microtime());
      $start_time = ((float)$usec + (float)$sec);
    }
    
    $sql = trim($sql);
    
    $this->fResult = mysql_query($sql, $this->link);
      //new dBug($sql);
    if (strtoupper(substr($sql,0,6))=="SELECT")//was a SELECT statement
    {
      if ($this->fResult !== false) //has been done successfully
        $this->fRowsNumber = mysql_num_rows($this->fResult);
    }
    else
      $this->fRowsNumber = mysql_affected_rows($this->link);
    
    $this->fError = mysql_error($this->link);
    $this->fErrorCode = mysql_errno($this->link);
    
    if( $this->fErrorCode > 0)
    {
      error_log($sql);
      error_log($this->fError);
    }
    
//    echo $sql."<br/>";
    
    list($usec, $sec) = explode(" ", microtime());
    $end_time = ((float)$usec + (float)$sec);
    $this->time = number_format($end_time-$start_time, 3);
    if($debug)
    {
      $len = sizeof($this->dump);
      $this->dump[$len]["sql"]      = $sql;
      $this->dump[$len]["error"]    = $this->fError;
      $this->dump[$len]["err_code"] = $this->fErrorCode;
      $this->dump[$len]["rows"]     = $this->fRowsNumber;
      $this->dump[$len]["time"]     = number_format($end_time-$start_time, 3);
      $this->dump["total_time"]    += $this->dump[$len]["time"];
    }
    parent::query($sql);
    $this->fQueries[$this->iterator]["time"] =  number_format($end_time-$start_time, 3);
    return $this->fResult;
  } 

  function getQueryCount($sql)
  {
    $pos = strpos(strtolower($sql), "from");
    if ((strtoupper(substr($sql,0,6))!="SELECT") && $pos === false)
    {
      return false;
    }
    else
    {
      $rest = substr($sql, $pos);
      $md5_rest = md5($rest);
      if(""==$this->cache_count[$md5_rest])
      {
        $sql2 = "SELECT SQL_CACHE COUNT(*) AS cnt " . $rest;
        $res = mysql_query($sql2);         
        $this->fRowsNumber = @mysql_result($res, 0, 0);
        parent::query($sql2);
        $this->cache_count[$md5_rest] = $this->fRowsNumber;
      }
      return $this->cache_count[$md5_rest];
    }
  }

  function getColumn($column)
  {
    $this->fRecordSet = array();
    if ($this->fRowsNumber> 0)
    {
      while ($a = mysql_fetch_assoc($this->fResult))
      {
        array_push($this->fRecordSet, $a[$column]);
      }
    }
    return $this->fRecordSet;
  }

  function getRow()
  {
    $this->fRecordSet = array();
    if ($this->fRowsNumber> 0)
      if (!empty($this->fResult))
        $this->fRecordSet = mysql_fetch_assoc($this->fResult);
    return $this->fRecordSet;
  }

  function getRecordSet()
  {
    $this->fRecordSet = array();
    if ($this->fRowsNumber> 0)
    {
      if (!empty($this->fResult))
      {
        while ($a = mysql_fetch_assoc($this->fResult))
        {
          array_push($this->fRecordSet, $a);
        }
      }
    }
    return $this->fRecordSet;
  }

  function getID()
  {
    return mysql_insert_id();
  }

  function escape($str)
  {
    return mysql_real_escape_string($str);
  }

  function getField($row = 0, $col = 0)
  {
    return @mysql_result($this->fResult, $row, $col);
  }

  function getTotalResultsCount()
  {
    $sql = "SELECT FOUND_ROWS()";
    $this->query($sql);
    return (int)$this->getField();
  }

  function getQueryField($sql, $row = 0, $col = 0)
  {
    $this->query($sql);
    return $this->getField($row = 0, $col = 0);
  }
}
