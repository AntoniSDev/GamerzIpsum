<?php
session_start();

$_GET["id"];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    require_once("connect.php");

    $id = strip_tags($_GET['id']);
    $sql = "SELECT * FROM games WHERE game_id = :id";
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch();

    $game = $result["game_name"];


    $sql = "SELECT * FROM screenshots WHERE game = '$game'";
    $query = $db->prepare($sql);
    $query->execute();
    $screen = $query->fetchAll(PDO::FETCH_ASSOC);





    require_once('close.php');

    // header("Location: index.php");
}


if ($_POST) {
    if (
        isset($_POST['game_name']) && isset($_POST['game_date']) && isset($_POST['game_desc']) && isset($_POST['cate_name'])
    ) {

        if (!empty($_FILES["image"]["name"])) {
            $allowed = [
                "jpg"  => "image/jpg",
                "jpeg" => "image/jpeg",
                "png"  => "image/png"
            ];

            $file2 = $_FILES["image"]["tmp_name"];
            $filename2 = $_FILES["image"]["name"];
            $filetype2 = $_FILES["image"]["type"];
            $filesize2 = $_FILES["image"]["size"];
            $extension2 = strtolower(pathinfo($filename2, PATHINFO_EXTENSION));
            if (!array_key_exists($extension2, $allowed) || !in_array($filetype2, $allowed) || $filesize2 > 4096 * 4096) {
                die("File jaquette problem");
            }

            $newname2 = md5(uniqid()) . "." . $extension2;
            $newfilename2 = "./uploads/$newname2";

            if (!move_uploaded_file($file2, $newfilename2)) {
                die("Failed to upload Jaquette file");
            }
            require('connect.php');
            $sql = "UPDATE games SET game_cover = :game_cover WHERE game_id=$id";
            $query = $db->prepare($sql);
            $query->bindValue(':game_cover', $newfilename2);
            $query->execute();
        }



        require('connect.php');
        $game_name = strip_tags($_POST['game_name']);
        $game_date = $_POST['game_date'];
        $game_desc = $_POST['game_desc'];
        $cate_name = $_POST['cate_name'];
        $sql = "UPDATE games SET game_name = :game_name, game_date = :game_date, game_desc = :game_desc, cate_name = :cate_name WHERE game_id=$id";
        $query = $db->prepare($sql);
        $query->bindValue(':game_name', $game_name);
        $query->bindValue(':game_date', $game_date);
        $query->bindValue(':game_desc', $game_desc);
        $query->bindValue(':cate_name', $cate_name);


        $query->execute();





        $uploadsDir = "uploads/";
        $allowedFileType = array('jpg', 'png', 'jpeg');
        foreach ($_FILES['fileUpload']['name'] as $id => $val) {
            // Get files upload path
            $fileName        = $_FILES['fileUpload']['name'][$id];
            $tempLocation    = $_FILES['fileUpload']['tmp_name'][$id];
            $game = $_POST['game_name'];
            $targetFilePath  = $uploadsDir . $fileName;
            $fileType        = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $uploadOk = 1;
            if (in_array($fileType, $allowedFileType)) {
                if (move_uploaded_file($tempLocation, $targetFilePath)) {
                    $sqlVal = "('" . $targetFilePath . "', '" . $game . "')";
                } else {
                    $response = array(
                        "status" => "alert-danger",
                        "message" => "File coud not be uploaded."
                    );
                }
            }
            if (!empty($sqlVal)) {
                $insert = $db->query("INSERT INTO screenshots (images, game) VALUES $sqlVal");
                if ($insert) {
                    $response = array(
                        "status" => "alert-success",
                        "message" => "Files successfully uploaded."
                    );
                } else {
                    $response = array(
                        "status" => "alert-danger",
                        "message" => "Files coudn't be uploaded due to database error."
                    );
                }
            }
        }
    }
    require("close.php");

    $_SESSION["edit"]["toast"] = 1;

    header("Location: backoffice.php");
}

// else {
//     header("Location: index.php");
// }
$title = "Modifier" . " " . $result["game_name"];
include("headerAdd.php");
?>


<div class="container container-fluid d-flex justify-content-center align-items-center">
    <div class="col-8">
        <h1 class="text-center">Modifier un jeu</h1>
        <div class="container-fluid col-xl-4 cl-md-8 col-xs-12">
        <a class="btn btn-primary" href="index.php">Accueil</a>
        <a class="btn btn-primary" href='backoffice.php'>Backoffice</a>
        </div>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="game_name">Nom du jeu</label><input name="game_name" value="<?= $result["game_name"] ?>" type="text" class="form-control" required>
                <label for="game_date">Date de sortie</label>
                <input type="date" name="game_date" value="<?= $result["game_date"] ?>" class="form-control" required>
                <label for="game_desc">Description</label><textarea name="game_desc" type="text" class="form-control" required><?= $result["game_desc"] ?></textarea>

                <div class="custom-file">
                    <input type="file" name="fileUpload[]" class="custom-file-input" id="chooseFile" multiple>
                    <label class="custom-file-label" for="chooseFile">Upload</label>
                </div>
                <div class="container imgGallery">

                </div>
                <div class="container col-12 screens border">
                    <?php
                    foreach ($screen as $screen) {
                    ?>
                        <a href="imagedel.php?id=<?= $screen["id"] ?>">
                            <img style="width: 200px; height:auto;" onclick="imagedel.php" src="<?= $screen["images"] ?>">
                        </a>
                    <?php
                    }
                    ?>

                </div>

                <div class="form-group">
                    <label for="jaquette">Jaquette</label>
                    <input type="file" name="image" id="jaquette" class="form-control-file">
                </div>
                <div class="form-group">
                    <label for="cate_name">Catégorie</label>
                    <select name="cate_name" required>
                        <option value="RPG" <?php if ($result["cate_name"] == "RPG") echo 'selected'; ?>>RPG</option>
                        <option value="FPS" <?php if ($result["cate_name"] == "FPS") echo 'selected'; ?>>FPS</option>
                        <option value="MMO" <?php if ($result["cate_name"] == "MMO") echo 'selected'; ?>>MMO</option>
                        <option value="Strategie" <?php if ($result["cate_name"] == "Strategie") echo 'selected'; ?>>Stratégie</option>
                        <option value="Simulation" <?php if ($result["cate_name"] == "Simulation") echo 'selected'; ?>>Simulation</option>
                        <option value="Survival Horror" <?php if ($result["cate_name"] == "Survival Horror") echo 'selected'; ?>>Survival Horror</option>

                    </select>
                </div>
            </div>

            <input type="submit" value="Modifier" class="sub">
        </form>
    </div>
</div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script>
    $(function() {
        // Multiple images preview with JavaScript
        var multiImgPreview = function(input, imgPreviewPlaceholder) {
            if (input.files) {
                var filesAmount = input.files.length;
                for (i = 0; i < filesAmount; i++) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        $($.parseHTML('<img>')).attr('src', event.target.result).appendTo(imgPreviewPlaceholder);
                    }
                    reader.readAsDataURL(input.files[i]);
                }
            }
        };
        $('#chooseFile').on('change', function() {
            multiImgPreview(this, 'div.imgGallery');
        });
    });
</script>