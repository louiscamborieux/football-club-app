<?php
//Connexion application
require_once "../config.php";
require_once "../form.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION["login"] !== true || !isset($_SESSION['expiration']) || time() > $_SESSION['expiration']) {
    session_destroy();
    header("location: identification.php");
    exit;
}


$backlink = 'accueil.php';
if(isset($_SERVER['HTTP_REFERER']) ) {
    $backlink = $_SERVER['HTTP_REFERER'];
}

//verification parametre url
if (empty($_GET['id'])) {
    header("location: joueurs.php");
    exit;
}

//Connexion DB
try{
    $linkpdo =new PDO('mysql:host='.DB_HOST.'; dbname=football_team',DB_USER,DB_PASS);
    // Activation des erreurs PDO
     $linkpdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 } catch(PDOException $e) {
     die('Erreur : ' . $e->getMessage());
 }

 ///Verification de la connexion
if (mysqli_connect_errno()) {
    print("Connect failed: \n" . mysqli_connect_error());
    exit();
}

function secure_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
  $requeteupJoue = "UPDATE joue   
  set NotePersonnelle = ?, Performance = ?
  where id_match = ? and id_joueur = ?";
  $upJoue = $linkpdo->prepare($requeteupJoue);

function updatenotes(int $id_m, int $id_j, int $perf, string $note) {
    if ($perf < 0 || $perf > 5 ) {
        return;
    }

    $note = secure_input($note);

    try {
        global $upJoue;
        $upJoue->bindParam(3,$id_m);
        $upJoue->bindParam(4,$id_j);
        $upJoue->bindParam(1,$note);
        $upJoue->bindParam(2,$perf);
        $upJoue->execute(); 
    }
    catch (PDOException $e) {
        echo $e->getMessage();
    }
}





?>
<!DOCTYPE html>
<html>
<head>    
        <title>Evaluer</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Evaluer vos joueurs">
</head>
<body>
<?php
include_once(MENU_LOCATION);



if (isset($_POST['submit'])) {
    $tokencheck = false;
    $tokenerror = "Erreur, TOKEN invalide<br/>";
    if(!empty($_SESSION['token']) AND(isset($_POST['token'])) AND ($_POST['token'] == $_SESSION['token'])) {
        if (time() < $_SESSION['expiration']) {
            $tokencheck = true;
        }
        else {
            $tokenerror = "Erreur, TOKEN d'accès expiré";
        }
    }

    if (!$tokencheck) {
        echo $tokenerror;
        echo $_POST['token']."<br/>";
        echo $_SESSION['token'];
        exit;
    }

    if (!isset($_POST["scoreadv"]) || $_POST["scoreadv"] <0 || !isset($_POST["scoreequipe"]) || $_POST["scoreequipe"] <0) {
        echo "erreurs scores mal renseignés";
        exit;
    }


    $requete = "UPDATE matchs set score_equipe = ? , score_adversaire = ? where id_match = ?";
    $res = $linkpdo->prepare($requete);
    $res->bindParam(1, $_POST["scoreequipe"]);
    $res->bindParam(2, $_POST["scoreadv"]);
    $res->bindParam(3, $_GET["id"]);
    try {
        $res->execute();
    }
    catch (PDOException $e) {
        echo $e->getMessage();
    }

    foreach ($_POST["id"] as $id) {
        if (isset($_POST["performance"][$id]) && isset($_POST["noteperso"][$id])) {
            updatenotes($_GET["id"], $id, $_POST["performance"][$id], $_POST["noteperso"][$id]);
        }
    }
}

$requete = "SELECT * from matchs, equipe where id_match = ? 
and matchs.id_adversaire = equipe.id_equipe";


//Execution de la requete
$res = $linkpdo->prepare($requete);
$res->bindParam(1, $_GET['id']);
$res->execute();


if (!($infomatch = $res->fetch())) {
    echo "Erreur, match introuvable";
    echo '<a href=' . $backlink . '><button type="button">Retour</button></a>';
    exit;
}


?>


<h1> Evaluer match</h1>

<form method="post">
    <fieldset id="scoreset">
        </label>
        
        <?php 
        if ($infomatch["Domicile_Exterieur"] == "E") {
            echo '<label>'.$infomatch["Nom"];
            echo '<br/><input type="number" class="score id="scoreadv" name ="scoreadv" min=0 value='.$infomatch["Score_Adversaire"]. ' "> </label>';
            echo '<label>'.TEAM_NAME;
            echo '<br/><input type="number" id="scoreequipe" class="score" name ="scoreequipe" min=0 value='.$infomatch["Score_Equipe"].'></label>';
        }
        else {
            echo '<label>'.TEAM_NAME;
            echo '<br/><input type="number" id="scoreequipe" name ="scoreequipe" class="score" min=0 value='.$infomatch["Score_Equipe"].'></label><br><br>';
            echo '<label>'.$infomatch["Nom"];
            echo '<br/><input type="number" id="scoreadv" name ="scoreadv" class="score" min=0 value='.$infomatch["Score_Adversaire"].'></label>';
        }?>
    </fieldset>

<h2> Titulaires </h2>
<table class="tableau-style">
    <thead>
        <th>Photo</th>
        <th>Nom</th>
        <th>Prenom</th>
        <th>Performance</th>
        <th>NotePersonnelle</th>
    </thead>
    <tbody>

<?php 
    $requete = "SELECT joueur.id_joueur, Nom, Prenom, NotePersonnelle, Performance, URLPhoto  from joue, joueur where id_match = ?
    and joue.id_joueur = joueur.id_joueur and Titulaire = ?";
    
    $res = $linkpdo->prepare($requete);
    $res->bindParam(1, $_GET['id']);
    $res->bindValue(2, 1);
    $res->execute();
    
    
    $urlphoto = "default.png"; ?>
    <?php while ($data = $res->fetch()) { ?> 
        <tr>
            <?php $urlphoto = "default.png";
            if (is_file(IMG_LOCATION . $data[5])) {
                $urlphoto = $data[5];
            } ?>
        <td><img src="<?= IMG_LOCATION . $urlphoto ?>"></td>
        <td><?= $data[1] ?></td>
        <td><?= $data[2] ?></td>
        <td>
            <input type=range name="performance[<?= $data[0] ?>]" min=1 max=5 step=1 value="<?= $data[4] ?>"></td>
            <td>
                <textarea name="noteperso[<?= $data[0] ?>]" ><?= $data[3] ?> </textarea> </td>
                <input type="hidden" name="id[<?= $data[0] ?>]" value="<?= $data[0] ?>">
            </tr>
        <?php }
        ?>
    </tbody>
</table>
<?php

$res->bindValue(2, 0);
$res->execute(); ?>
<h2>Remplacants </h2>
<table class="tableau-style">
    <thead>
        <th>Photo</th>
        <th>Nom</th>
        <th>Prenom</th>
        <th>Performance</th>
        <th>NotePersonnelle</th>
    </thead>
    <tbody>
<?php
while ($data = $res->fetch()) {?> 
    <tr>
        <?php $urlphoto = "default.png"; 
    if (is_file(IMG_LOCATION . $data[5])) {
        $urlphoto = $data[5];
    }?>
    <td><img src="<?=IMG_LOCATION.$urlphoto ?>"></td>
    <td><?=$data[1]?></td>
    <td><?=$data[2]?></td>
    <td>
        <input type=range name="performance[<?= $data[0] ?>]" min=1 max=5 step=1 value="<?=$data[4]?>"></td>
        <td><textarea name="noteperso[<?=$data[0]?>]" ><?=$data[3]?> </textarea> </td>
            <input type="hidden" name="id[<?=$data[0]?>]" value="<?=$data[0]?>">
        </tr>
    <?php 

}?>
</tbody>
</table>



<input type="hidden" name="token" value="<?php echo $_SESSION['token']?>">
<input type="submit" value="Enregistrer" name="submit" class="button">
</form>


</body>
</html>
