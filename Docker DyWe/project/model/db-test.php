<html>
<head>
	<style>
		td{
			border:1px solid black;
		}
		</style>
</head>
<body>
<?php

$definition = array(
	"post" => array("id" => array("int(11)", "int", "primary", "auto_increment"),
					"fk_user" => array("int(11)", "int"),
	                "post_text" => array("varchar(128)","text","tex","var"),
					"post_video_file" => array("varchar(128)","text", "var"),
					"post_date" => array("timestamp"),

					),
	"post_tag"=> array("id" => array("int(11)", "int", "primary", "auto_increment"),
					"fk_tag" => array("int(11)","int"),
	                "fk_post" => array("int(11)", "int"),
					),
	"user"=> array("id" => array("int(11)", "int", "primary", "auto_increment"),
					"username" => array("varchar(128)","text","tex","var"),
					"user_realname" => array("varchar(128)","text","tex","var"),
					"user_image" => array("varchar(128)","tex","var"),
					),
	"tag"=> array("id" => array("int(11)", "int", "primary", "auto_increment"),
					"name" => array("varchar(32)","text","tex","var"),
					),
	
	
	
);



/********** CONNECT TO DATABASE ***********/
$servername = "10.80.0.11:3306";
$username = "root";
$password = "cr4ftd4t4b4s3";

try {
    $conn = new PDO("mysql:host=$servername;dbname=tiktok", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully"; 
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
	return;
}
/********** CONNECT TO DATABASE ***********/


/********** LIST TABLES ***********/

$tableList = array();
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tableList[] = $row[0];
}

if (count($tableList) == 0)
{
	echo "no tables found";
	return;
}

foreach($tableList as $k=>$table) { 
	echo "<h1>TABLE: $table</h1><table>";

		echo "<tr>
				<td>Feld</td>
				<td>Type</td>
				<td>Key</td>
				<td>Extra</td>
				<td>Status</td>
				<td>Allowed Types</td>
 			   </tr>";


    /********** LOAD Table-Data  ***********/
    $stmt_columns = $conn->query("SHOW COLUMNS FROM $table;"); 
    $stmt_columns->execute();
	foreach($stmt_columns->fetchAll() as $k=>$column) { 
		$error = "";
		if (!isset($definition[$table][$column['Field']]))
		{
			$error = "Field not expected";
			$color = "red";
			
		} else
		{
	
			if (!isset($definition[$table][$column['Field']]))
			{
				$type = "unknow field";
			} else{
				$type = $definition[$table][$column['Field']];
			}
			


			if (  !in_array($column['Type'], $type) 
				&&  !in_array(substr($column['Type'],0,3), $type))
			{
				$error = "Field type should be in:". implode(",",$type);
				$color = "red";
			} else
			{
						$color = "green";
						$error = "OK";
			}
		
			if (in_array("primary", $type) && $column['Key'] != "PRI") // should be primary
			{
				$error = "Field should be primary";
				$color = "red";
			} 

			if (in_array("auto_increment", $type) && $column['Extra'] != "auto_increment") // should be auto_inc
			{
				$error = "Field should be auto_increment";
				$color = "red";
			} 
	
		}
		
			echo "<tr>
					<td>".$column['Field']."</td>
					<td>".$column['Type']."</td>
					<td>".$column['Key']."</td>
					<td>".$column['Extra']."</td>
					<td style='background-color:$color'>".$error."</td>
					<td>".implode(",",$type)."</td>
	 			   </tr>";
		
	}
	echo "</table>";


}


?>