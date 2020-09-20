<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>簡易掲示板</title>
</head>

<body>
    <?php
    $dsn = 'mysql:dbname=*********;host=localhost';
    $user = '**********';
    $password = '*********';
    try {
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    } catch (PDOException $e) {
        echo "接続できていない";
    }
    $sql = "CREATE TABLE IF NOT EXISTS tbdata"
        . " ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "comment TEXT,"
        . "date DATETIME,"
        . "pass TEXT"
        . ");";
    $stmt = $pdo->query($sql);

    $newname = "";
    $newcomment = "";
    $newpass = "";
    $editNum = "";
    //POST送信があった時
    if (!empty($_POST['name']) && !empty($_POST['comment'])) {
        //ここで新規投稿か編集か判断
        if (empty($_POST["edit_n"])) {
            // 以下、新規投稿機能
            $sql = $pdo->prepare("INSERT INTO tbdata (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)");
            $sql->bindParam(':name', $name, PDO::PARAM_STR);
            $sql->bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql->bindParam(':date', $date, PDO::PARAM_STR);
            $sql->bindParam(':pass', $pass, PDO::PARAM_STR);
            //変数に代入
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $pass = $_POST["pass"];
            //日付データ取得
            $date = date("Y/m/d H:i:s");
            $sql->execute();
        } else {
            //編集
            try {
                $sql = "UPDATE tbdata SET name =?, comment =?, pass =? WHERE id=? ";
                $stmt = $pdo->prepare($sql);
                $array = array($_POST["name"], $_POST["comment"], $_POST["pass"], $_POST['edit_n']);
                $stmt->execute($array);
            } catch (Exception $e) {
                $res = $e->getMessage();
            }
        }
    }

    //以下消去機能
    //POST送信があったとき
    if (!empty($_POST['deleteno']) && !empty($_POST['delpass'])) {
        try {
            $deleteno = $_POST['deleteno'];
            $sql = ' SELECT * FROM tbdata WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $array = array($deleteno);
            $stmt->execute($array);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($_POST['delpass'] === $row['pass']) {
                $sql = 'DELETE FROM tbdata WHERE id=?';
                $stmt = $pdo->prepare($sql);
                $array = array($deleteno);
                $stmt->execute($array);
            }
        } catch (Exception $e) {
            $res = $e->getMessage();
        }
    }

    //以下編集ボタンを押したらフォームに元の内容を表示させる処理
    if (!empty($_POST['number']) && !empty($_POST['editpass'])) {
        try {
            $number = $_POST['number'];
            $sql = ' SELECT * FROM tbdata WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $array = array($number);
            $stmt->execute($array);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($_POST['editpass'] === $row['pass']) {
                $editNum = $row['id'];
                $newname = $row['name'];
                $newcomment = $row['comment'];
                $newpass = $row['pass'];
            }
        } catch (Exception $e) {
            $res = $e->getMessage();
        }
    }
    ?>

    <form action="m5-1.php" method="post">
        投稿
        <!--名前の入力フォーム-->
        <input type="text" name="name" placeholder="名前" value="<?php if (!empty($_POST['edit'])) {
                                                                    echo $newname;
                                                                } ?>">
        <!--コメントの入力フォーム-->
        <input type="text" name="comment" placeholder="コメント" value="<?php if (!empty($_POST['edit'])) {
                                                                        echo $newcomment;
                                                                    } ?>">
        <!--パスワードの入力フォーム-->
        <input type="text" name="pass" placeholder="パスワード" value="<?php if (!empty($_POST['edit'])) {
                                                                        echo $newpass;
                                                                    } ?>">
        <input type="hidden" name="edit_n" value="<?php if (!empty($_POST['edit'])) {
                                                        echo $editNum;
                                                    } ?>">
        <input type="submit" name="submit">
        <!--消去の入力フォーム-->
        <br>
        削除
        <input type="text" name="deleteno" placeholder="消去対象番号">
        <input type="text" name="delpass" placeholder="パスワード">
        <input type="submit" name="delete" value="削除">
        <!--編集番号指定用フォーム-->
        <br>
        編集
        <input type="text" name="number" placeholder="編集対象番号">
        <input type="text" name="editpass" placeholder="パスワード">
        <input type="submit" name="edit" value="編集">
    </form>
    【投稿一覧】<br>
    <?php

    $sql = 'SELECT * FROM tbdata';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row) {
        echo $row['id'] . ',';
        echo $row['name'] . ',';
        echo $row['comment'] . ',';
        echo $row['date'] . '<br>';
        echo "<hr>";
    }

    ?>

</body>

</html>