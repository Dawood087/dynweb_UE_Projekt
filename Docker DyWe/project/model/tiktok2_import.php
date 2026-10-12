<?



/*
TRUNCATE TABLE `tiktok`.`user`;
TRUNCATE TABLE `tiktok`.`post_tag`;
TRUNCATE TABLE `tiktok`.`post`;
TRUNCATE TABLE `tiktok`.`tag`;
*/

if (file_exists('../model/post_data.php'))
{
    require('../model/post_data.php');
} else{
    echo "Error, no post_data.php in this directory.";
    return;
}

if (!isset($posts))
{
    echo ("Error, did you rename \$posts in post_data.php?");
}


require('tiktok2_model_encoded.php');

custom_db_connect();


$i = 0;
foreach ($posts as $post)
{
    $i++;
    echo "importing post $i (". substr ($post['post_text'],0, 10).")<br>";

    $post_id =  custom_db_post_exists($post);
    if ($post_id > 0)
    {
        echo "post exists: $post_id<br>"; 
    } else{
        echo "post does not exist, creating...<br>";
        $user_id = custom_db_user_get($post);
        
        if ($user_id == "")
        {
            echo "user does not exist, creating...<br>";
            custom_db_user_create($post);
            $user_id = custom_db_user_get($post);
            echo "user created, id: $user_id<br>";

        }
        
        if ($user_id > 0)
        {
            echo "creating post<br>";
            custom_db_post_create($post, $user_id);


            $post_id =  custom_db_post_exists($post);
            echo "checking tags<br>";
            foreach ($post['post_tags'] as $tag)
            {
                $tag_id = custom_db_tag_get($tag);
                if ($tag_id == "")
                {
                    echo "creating tag $tag...<br>";
                    custom_db_tag_create($tag);
                    $tag_id = custom_db_tag_get($tag);
                } else
                {
                    echo "tag $tag exists id:$tag_id<br>";

                }
                custom_db_tag_add($tag_id, $post_id);
            }


            echo "finished<br>";

        } 
        
    }
    echo "<hr>";


}

?>