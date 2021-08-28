<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission5-1</title>
</head>
<body>
    
    <?php
    
    $dsn='データベース名';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    $inputname="";
    $inputcomment="";
    $editnumber=-1;
    
    $sql = "CREATE TABLE IF NOT EXISTS data"
    ." ("
    . "id INT(10),"
    . "name char(32),"
    . "comment TEXT,"
    . "date TEXT,"
    . "password TEXT"
    .");";
    $stmt = $pdo->query($sql);
    
    
        //動作がうまくいかなかった場合の削除用
        //下の二行のコメントアウトを外して実行、もう一度コメントアウトして実行という流れで１からにできる
        //$sql = 'DROP TABLE data';
        //$stmt = $pdo->query($sql);

     //データ数のカウント
     $stmt = $pdo->prepare("SELECT COUNT(*) FROM data");
     $stmt->execute();
     $count = $stmt->fetchColumn();

     
     //編集
     if(!empty($_POST["edit"])){
          
          $edit = $_POST["edit"];
          
          //編集番号がデータの数より多い場合は何もしない、少ない場合のみ実行
          if($count >= $edit){

             
              if(empty($_POST["pw"])){
                  
                  echo "パスワードの入力は必須です";
                  exit;
                  
              }else{
                 //パスワードの一致を確認
                 $editnumber=$edit;
                 $pw=$_POST["pw"];
                 $id = $edit ;  
                 $sql = 'SELECT * FROM data WHERE id=:id ';
                 $stmt = $pdo->prepare($sql);                  
                 $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
                 $stmt->execute();                            
                 $results = $stmt->fetchAll(); 
                 
                 foreach ($results as $row){
                   if($row['password']==""){
                     
                       echo "パスワードが設定されていません";
                       exit;
                     
                   }else if($pw!=$row['password']){
                       
                       
                       echo "パスワードが異なっています";
                       exit;
                       
                   }else{
                       
                       break;
                       
                       
                   }
                 }
                 
                 $sql = 'SELECT * FROM data WHERE id=:id ';
                 $stmt = $pdo->prepare($sql);                  
                 $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
                 $stmt->execute();                            
                 $results = $stmt->fetchAll(); 
                 
                 foreach ($results as $row){
                 
                 $inputname=$row['name'];
                 $inputcomment=$row['comment'];
                 
                 }
                 
              }
              
              
          }
          
     }
     
     
     
     //削除
     if(!empty($_POST["delete"])){
              
 
                  
        $delete = $_POST["delete"];
                  
        if(!empty($_POST["name"])&&!empty($_POST["comment"])){
                     
                 $name = $_POST["name"];
                 $comment = $_POST["comment"];       

        }
                    

          //削除番号が投稿数より大きい場合
          if($count < $delete){
              
             if(empty($_POST["name"])||empty($_POST["comment"])){
                 
                 echo "新規投稿の場合は名前とコメントを入力してください";
                 exit;
                 
             }else{
                 
                $date=date( "Y/m/d  H:i:s" );
                 
                if(!empty($_POST["pass"])){
             
                   $sql = $pdo -> prepare("INSERT INTO data (name, comment,date, password) VALUES (:name, :comment, :date,:password)");
                   $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                   $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                   $pass=$_POST["pass"];
                   $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                   $sql -> bindParam(':password', $pass, PDO::PARAM_STR);
                   $sql -> execute();
              
                }else{
             
                  $sql = $pdo -> prepare("INSERT INTO data (name, comment,date, password) VALUES (:name, :comment, :date,:password)");
                  $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                  $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                  $pass="";
                  $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                  $sql -> bindParam(':password', $pass, PDO::PARAM_STR);
                  $sql -> execute();
               } 
              

              
          }
     
       }else{
           
                 //削除番号とそこのパスワードが一致するかをチェック
                 $password=$_POST["password"];
                 $id = $delete ; // 
                 $sql = 'SELECT * FROM data WHERE id=:id ';
                 $stmt = $pdo->prepare($sql);                  
                 $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
                 $stmt->execute();                            
                 $results = $stmt->fetchAll(); 
                 
                 foreach ($results as $row){
                   if($row['password']==""){
                     
                       echo "パスワードが設定されていません";
                       exit;
                     
                   }else if($password!=$row['password']){
                       
                       
                       echo "パスワードが異なっています";
                       exit;
                       
                   }else{
                       
                       break;
                       
                       
                   }
                 }
           
          //削除する
          $sql = 'delete from data where id=:id';
          $stmt = $pdo->prepare($sql);
          $stmt->bindParam(':id', $id, PDO::PARAM_INT);
          $stmt->execute();
          
          
          //残ったもののidを編集
          $sql = "SELECT * FROM data WHERE id >= '" . $delete . "'";
          $stmt = $pdo->query($sql);
          $results = $stmt->fetchAll();
          $stmt->execute(); 
            
            foreach ($results as $row){
                $id=$row['id'];
                $newid=$id-1;
                //echo $newid."<br>";
                $sql = "UPDATE data SET id=:id WHERE id = '" . $id . "'";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $newid, PDO::PARAM_INT);
                $stmt->execute();
          
            }
            //exit;
       }
       
     }
     
     
     
         //新規作成、編集
         if(!empty($_POST["name"])&&!empty($_POST["comment"])&&empty($_POST["edit"])&&empty($_POST["delete"])&&($_POST["editnumber"])==-1) {
        
            $id=$count+1;
            $name =$_POST["name"];
            $comment =$_POST["comment"];
            $date=date( "Y/m/d  H:i:s" );
     

            
            if(!empty($_POST["pass"])){
                
              $sql = $pdo -> prepare("INSERT INTO data (id,name, comment,date, password) VALUES (:id,:name, :comment, :date,:password)");
              $sql -> bindParam(':id', $id, PDO::PARAM_INT);
              $sql -> bindParam(':name', $name, PDO::PARAM_STR);
              $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
              $pass=$_POST["pass"];
              $sql -> bindParam(':date', $date, PDO::PARAM_STR);
              $sql -> bindParam(':password', $pass, PDO::PARAM_STR);
              $sql -> execute();
           
            }else{
                
              $sql = $pdo -> prepare("INSERT INTO data (id,name, comment,date, password) VALUES (:id,:name, :comment, :date,:password)");
              $sql -> bindParam(':id', $id, PDO::PARAM_INT);
              $sql -> bindParam(':name', $name, PDO::PARAM_STR);
              $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
              $pass="";
              $sql -> bindParam(':date', $date, PDO::PARAM_STR);
              $sql -> bindParam(':password', $pass, PDO::PARAM_STR);
              $sql -> execute();
                
                
            }
            


    }else if(!empty($_POST["name"])&&!empty($_POST["comment"])&&empty($_POST["edit"])&&empty($_POST["delete"])&&($_POST["editnumber"])!=-1) {
        
                 //上書きを行う
                $name =$_POST["name"];
                $comment =$_POST["comment"];
                $date=date( "Y/m/d  H:i:s" );
                $id=$_POST["editnumber"];
                
                
                 $sql = 'UPDATE data SET name=:name,comment=:comment,date=:date WHERE id=:id';
                 $stmt = $pdo->prepare($sql);
                 $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                 $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                 $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                 $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                 $stmt->execute();
        
    }
     
     ?>
     
 <form action="" method="post">
        新規投稿<br>
        パスワードをかけなくても投稿は可能ですが、編集・削除はできません<br>
        <input type="text" name="name" value="<?php print $inputname;?>" style="width:100px"><br>
        <input type="text" name="comment" value="<?php print $inputcomment;?>" style="width:250px;height:150px;"><br>
        <input type="text" name="pass" placeholder="パスワード"><br>
        <input type="submit" name="submit"><br><br>
        投稿を削除したい場合は下のフォームに削除したい番号とパスワードを入れてください<br>
        <input type="number" name="delete" placeholder="削除したい番号">
        <input type="submit" name="submit" value="削除"><br>
        <input type="text" name="password" placeholder="パスワード"><br><br>
        編集する場合は下のフォームで番号とパスワードを入力した後、<br>上の新規投稿と同じフォームに名前とコメントを書きなおしてください<br>
        ただし上のフォームではパスワードの入力は必要ありません<br>
        <input type="number" name="edit" placeholder="編集したい番号">
        <input type="submit" name="submit" value="編集"><br>
        <input type="text" name="pw" placeholder="パスワード"><br><br>
        <input type="hidden" name="editnumber" value="<?php print $editnumber;?>">
        パスワードのミスなどで投稿がみられなくなった場合はブラウザの戻る矢印を押してください<br>
        ------------------------------------------------------------------------------------------------------<br><br>
</form>
     
     
    <?php 
     
    //表示
    echo "投稿一覧<br>";
    $sql = 'SELECT * FROM data';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        echo $row['id'].' ';
        echo $row['name'].' ';
        echo $row['comment'].' ';
        echo $row['date'].' '/*.'<br>'.'<br>'*/;
        /*デバッグ用*/echo $row['password'].'<br>'.'<br>';
    }
    
    
    ?>
</body>
</html>