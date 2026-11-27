<?php

/****************

VERSION: 2024-10-14





* Put this file in the www/web-folder of task 2
* Change the group TODO to you group name (line 19)
example: export("A");
* Execute the file in your browser /export_dywe_task_3.php

*****************/
$year = 2026;

export("19");














/*
TRUNCATE TABLE `tiktok`.`user`;
TRUNCATE TABLE `tiktok`.`post_tag`;
TRUNCATE TABLE `tiktok`.`post`;
TRUNCATE TABLE `tiktok`.`tag`;

*/

function custom_db_connect($server, $username,$password, $database="tiktok")
{
     try {
        $conn_target = new PDO("mysql:host=$server;dbname=$database;charset=UTF8", $username, $password);
        // set the PDO error mode to exception
        $conn_target->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    } catch(PDOException $e) {
        echo "Connection to $server, database $database failed: " . $e->getMessage();
    }
    return $conn_target;
}


function export($group)
{
    //$target = custom_db_connect( "database-1.cvoirklvrwnp.eu-central-1.rds.amazonaws.com", "student", "toJ4cv29.w9sM4La" );
    $target = custom_db_connect( "jtrt.your-database.de", "haw_kiel_w", "f2}ATj%y)TtA" ,"haw_kiel");
    $source = custom_db_connect( "10.80.0.11:3306", "root", "cr4ftd4t4b4s3" );
    
    
    $posts = custom_db_get_posts($source);

    /************* */

    $i = 0;
    foreach ($posts as $post)
    {
        $i++;
        echo "<h1>exporting post $i (". substr ($post['post_text'],0, 10).")</h1>";
        
        $file_prefix = $group. "_";

        if (substr($post['user_image'],0,strlen($file_prefix)) != $file_prefix)
        {
            echo ("Error: This posts user_image (".$post['user_image'].") does not start with $file_prefix. Aborting.");
            break;
        }

        if (substr($post['post_video_file'],0,strlen($file_prefix)) != $file_prefix)
        {
            echo ("Error: This posts post_video_file (".$post['post_video_file'].") does not start with $file_prefix. Aborting.");
            break;
        }
        
     
     
        $post_id =  custom_db_post_exists($target, $post);
        if ($post_id > 0)
        {
            echo "post exists: $post_id<br>"; 
        } else{
            echo "post does not exist, creating...<br>";
            $user_id = custom_db_user_get($target, $post);
            
            if ($user_id == "")
            {
                echo "user does not exist, creating...<br>";
                custom_db_user_create($target,$post);
                $user_id = custom_db_user_get($target,$post);
                echo "user created, id: $user_id<br>";
    
            } else
            {
                echo "user exists: $user_id<br>"; 
            }
            
            if ($user_id > 0)
            {
                echo "creating post<br>";
                custom_db_post_create($target, $post, $user_id);
    
    
                $post_id =  custom_db_post_exists($target,$post);
                echo "checking tags<br>";
                foreach ($post['post_tags'] as $tag)
                {
                    $tag_id = custom_db_tag_get($target, $tag);
                    if ($tag_id == "")
                    {
                        echo "creating tag $tag...<br>";
                        custom_db_tag_create($target, $tag);
                        $tag_id = custom_db_tag_get($target, $tag);
                    } else
                    {
                        echo "tag $tag exists id:$tag_id<br>";
    
                    }
                    custom_db_tag_add($target,$tag_id, $post_id);
                }
    
    
                echo "finished<br>";
    
            } 
            
        }
        echo "<hr>";
    
    
    }

    /************* */
    
    
}


function custom_db_post_create($connection, $post, int $user_id)
{
    global $year;

    $stmt = $connection->prepare("INSERT INTO post (fk_user,post_text,post_video_file,post_date, year) 
                    VALUES (:fk_user,:post_text,:post_video_file,CURRENT_TIMESTAMP, :year)"); 


    $stmt->bindParam(':fk_user', $user_id);
    $stmt->bindParam(':year', $year);
    $stmt->bindParam(':post_text', $post['post_text']);
    $stmt->bindParam(':post_video_file', $post['post_video_file']);

    $stmt->execute();
}


function custom_db_get_posts($connection)
{
    $sql_posts = "SELECT post.id,fk_user,post_text,post_video_file,post_date 
    FROM post 
    WHERE 1";

    $stmt = $connection->prepare($sql_posts); 
  

    $stmt->execute();

    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 
    $post_all = array();

    foreach($stmt->fetchAll() as $k=>$post) { 
    
        /********** LOAD USERDATA  ***********/
        $stmt_user = $connection->prepare("SELECT username,user_realname,user_image
                                        FROM user
                                        WHERE id=:fk_user;"); 
        $stmt_user->bindParam(":fk_user",$post['fk_user']);
        $stmt_user->execute();
        $user = $stmt_user->fetch(); 
        /********** LOAD USERDATA  ***********/

        /********** LOAD TAG-ASSOCIATION DATA  ***********/
        $stmt_tags_ass = $connection->prepare("SELECT fk_tag
                                        FROM post_tag
                                        WHERE fk_post=:post_id;"); 
        $stmt_tags_ass->bindParam(":post_id",$post['id']);

        $result = $stmt_tags_ass->setFetchMode(PDO::FETCH_ASSOC); 
        $stmt_tags_ass->execute();
        $tags = array();
        foreach($stmt_tags_ass->fetchAll() as $k=>$tag_ass) { 
            /********** LOAD TAGS DATA  ***********/
            $stmt_tags = $connection->prepare("SELECT name
                FROM tag
                WHERE id=:tag_id;"); 
            $stmt_tags->bindParam(":tag_id",$tag_ass['fk_tag']);

            $result = $stmt_tags->setFetchMode(PDO::FETCH_ASSOC); 
            $stmt_tags->execute();
            $tag = $stmt_tags->fetchColumn();

            /********** LOAD TAG  ***********/
            $tags[] = $tag;
        }
        /********** LOAD TAG  ***********/







        /********** PUT Post together  ***********/

        $post['user_image'] = $user['user_image'];
        $post['user_realname'] = $user['user_realname'];
        $post['username'] = $user['username'];
        $post['post_tags'] = $tags;


        $post_all[] = $post;


    }

    return $post_all;

}


function custom_db_post_exists($connection, $post)
{
    $stmt = $connection->prepare("SELECT id 
                            FROM post 
                            WHERE post_text=:post_text 
                            AND post_video_file=:post_video_file
                            "); 

    $stmt->bindValue(':post_text', $post['post_text']);
    $stmt->bindValue(':post_video_file', $post['post_video_file']);


    $stmt->execute();

    
    $id = $stmt->fetchColumn();
    return $id;
}



function custom_db_user_get($connection,$post)
{
    $stmt = $connection->prepare("SELECT id 
                            FROM user 
                            WHERE 
                                user_realname=:user_realname
                                AND user_image=:user_image
                                AND username=:username
                            "); 

    

    

    $stmt->bindValue(':user_realname', $post['user_realname'], PDO::PARAM_STR);
    $stmt->bindValue(':user_image', $post['user_image'], PDO::PARAM_STR);
    $stmt->bindValue(':username', $post['username'], PDO::PARAM_STR);


    $stmt->execute();
    $id = $stmt->fetchColumn();

    return $id;
}


function custom_db_user_create($connection, $post)
{
    global $year;

    $stmt = $connection->prepare("INSERT INTO user (user_realname,user_image,username,year ) 
    VALUES (:user_realname,:user_image,:username, :year)"); 

	$post['user_realname'] = ($post['user_realname']);
	$post['username'] = ($post['username']);
	
    $stmt->bindParam(':user_realname', $post['user_realname']);
    $stmt->bindParam(':user_image', $post['user_image']);
    $stmt->bindParam(':username', $post['username']);
    $stmt->bindParam(':year', $year);

	


    $stmt->execute();
}




/********************* tags *********************/

function custom_db_tag_create($connection, string $tag)
{
    global $year;

    $stmt = $connection->prepare("INSERT INTO tag (name,year) VALUES (:tag, :year)"); 

    $stmt->bindParam(':tag', $tag);
    $stmt->bindParam(':year', $year);

    
    $stmt->execute();
}





function custom_db_tag_get($connection,$tag)
{
    $stmt = $connection->prepare("SELECT id 
                            FROM tag 
                            WHERE                                 name=:tag
                            "); 

    $stmt->bindValue(':tag', $tag, PDO::PARAM_STR);
    $stmt->execute();
    $id = $stmt->fetchColumn();

    return $id;
}

function custom_db_tag_add($connection,int $tag_id, int $post_id)
{
    global $year;
    $stmt = $connection->prepare("INSERT INTO post_tag (fk_tag, fk_post, year) VALUES (:tag_id,:post_id, :year)"); 

    $stmt->bindParam(':tag_id', $tag_id);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':year', $year);

    
    $stmt->execute();
}



?>