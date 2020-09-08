<?php
/*
*ファイルパス:C:\xampp\htdocs\DT\buy_regist\admin.php
*ファイル名:admin.php
*アクセスURL:http://localhost/DT/buy_regist/admin.php
*/

$db_host = 'localhost';
$db_name = 'buy_db';
$db_user = 'buy_user';
$db_pass = 'buy_pass';

$img_name ="none";

$errMsg =[
    'item_name'=>'',
    'price'=>'',
    'detail'=>'',
    'ctg_id'=>'',
    'image'=>''
];

//データベースホストへ接続
$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if($link !== false)
{
   if(isset($_POST['send']) === true)
    {   
        $item_name = $_POST['item_name'];
        $price = $_POST['price'];
        $detail = $_POST['detail'];
        $ctg_id = $_POST['ctg_id'];
        
        //商品名が空の場合
        if($item_name === '')
        {
            $errMsg['item_name']='商品名を入力してください';
        }
        //価格が空の場合
        if($price === '')
        {
            $errMsg['price']='価格を入力してください';
        }
        //詳細が空の場合
        if($detail === '')
        {
            $errMsg['detail']='詳細を入力してください';
        }        
        //画像が選択されている場合
        if($_FILES['image']['error'] !== 4)
        {  
            $tmp_image = $_FILES['image'];
            //エラーがなく、サイズが０ではないか
            if($tmp_image['error'] === 0 && $tmp_image['size'] !== 0)
            {
            //正しくサーバにアップされているかどうか
                if(is_uploaded_file($tmp_image['tmp_name']) === true)
                {
                    //画像情報を取得する    
                    $image_info = getimagesize($tmp_image['tmp_name']);
                    $image_mime = $image_info['mime'];
                    //画像サイズが利用できるサイズ以内かどうか
                    if($tmp_image['size'] > 1048576)
                    {
                        $errMsg['image'] = 'アップロードできる画像のサイズは、1MBまでです';
                    }
                    //画像の形式が利用できるタイプかどうか
                    elseif (preg_match('/^image\/jpeg$/', $image_mime) === 0)
                    {
                        $errMsg['image'] = 'アップロードできる画像の形式は、JPEG形式だけです';    
                    }
                    else
                    {
                    //time:現在時刻をUnixエポック（1970年1月1日00:00:00GMT）からの通算秒として返す(Unixタイムスタンプ)
                        $img_name=time().'jpg';
                    }    
                }
            } 

            $err_flg = true;
            foreach($errMsg as $key => $err_msg)
            {
                if($err_msg !== '')$err_flg = false;
            }
            if($err_flg === true)
            {
                $query = "INSERT INTO item ( "
                   . "  item_name , "
                   . "  price , "
                   . "  detail , " 
                   . "  ctg_id , "     
                   . "  image "
                   . " ) VALUES( "
                   . "'" . mysqli_real_escape_string($link, $item_name)."', "
                   . "'" . mysqli_real_escape_string($link, $price)."', "
                   . "'" . mysqli_real_escape_string($link, $detail)."', "  
                   . "'" . mysqli_real_escape_string($link, $ctg_id)."', "    
                   . "'" . mysqli_real_escape_string($link, $img_name)."'"
                   ." ) ";
                $res = mysqli_query($link, $query);
                
                if($res !== false)
                {
                    //ファイルアップ
                    if($img_name !== 'none')
                    {
                        if(move_uploaded_file($tmp_image['tmp_name'],'./'.$img_name)=== true)
                        {
                            echo '書き込みに成功しました';
                        }
                    }
                    else
                    {
                        echo '成功';
                    }
                } 
                else 
                {
                    echo '書き込みに失敗しました';
                }
            }       
        }   
    }  
}
else
{
echo'データベースの接続に失敗しました';
}
?>

<html>
<head>
<meta charset="utf-8">
<title></title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
        <form method="post" action="" enctype="multipart/form-data">
                商品名 : <input type="text" name="item_name" value=""><?php echo $errMsg['item_name']; ?><br>
                価格 　: <input type="text" name="price" value=""><?php echo $errMsg['price']; ?><br>
                詳細 　: <textarea name="detail"rows="4"cols="40"><?php echo $errMsg['detail']; ?></textarea ><br>
                画像 　: <input type="file" name="image"><?php echo $errMsg['image']; ?><br>
                カテゴリー:<input type="radio" name="ctg_id" value="1"required>野菜
                          <input type="radio" name="ctg_id" value="2">果物
                          <input type="radio" name="ctg_id" value="2">飲料<br>
                <button type="button" onclick="history.back()">戻る</button>
                <input type="submit" name="send" value="登録">
        </form>
</body>
</html>
<?php 
    //一覧表示
    echo '一覧表示<br>';
    $query = "SELECT item_name, price, ctg_id, detail, image FROM item";
    $res = mysqli_query($link, $query);
    $data = [];
    while($row = mysqli_fetch_assoc($res))
    {
        array_push($data, $row);
    } 
    

    foreach($data as $item):?>
    <?php echo'商品名:'.$item['item_name'];?><br>
    <?php echo'価格:\\'.floor($item['price']);?><br>
    <?php echo'詳細:'.$item['detail'];?><br>
    <?php echo'画像:';?>
    <?php if($item['image']!=='none'):?>
        <img src='<?php echo $item['image'];?>'><br>    
    <?php endif;?><br> 
    <?php endforeach;?><br>