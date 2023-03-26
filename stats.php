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

//Recuperation info matchs
$reqnbvictoires = "SELECT Count(*) FROM `matchs`where Score_Equipe>Score_Adversaire";
$reqnbdefaites = "SELECT Count(*) FROM `matchs`where Score_Equipe<Score_Adversaire";
$reqnbnuls = "SELECT Count(*) FROM `matchs`where Score_Equipe=Score_Adversaire and Score_Equipe is not null";

$res = $linkpdo->query($reqnbvictoires);
$reqnbvictoires = $res->fetch()[0];
$res = $linkpdo->query($reqnbdefaites);
$reqnbdefaites = $res->fetch()[0];
$res = $linkpdo->query($reqnbnuls);
$reqnbnuls= $res->fetch()[0];

$nbtotal = $reqnbnuls+$reqnbdefaites+$reqnbvictoires

?>

<!DOCTYPE html>
<html>
<head>    
        <title>Statistiques</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Statistiques">
</head>

<body>
    <?php include_once(MENU_LOCATION);?>

    <h2> Matchs Joués : <?=$nbtotal?></h2>
    <div class = "infostats">
        <span>Victoire : <?=$reqnbvictoires?></span></br>
        <span>Nul : <?=$reqnbnuls?></span></br>
        <span>Défaite : <?=$reqnbdefaites?></span></br>
        <span>Pourcentage de victoires : <?=round($reqnbvictoires/$nbtotal *100,1)?> %</span>
    </div class="infostats">


<?php
$joueurs = array();
//Trouver dernier match
$reqderniermatch = "SELECT id_joueur, max(DateHeure) as datematch, joue.id_match as lastmatch from joue, matchs
where matchs.id_match = joue.id_match and score_adversaire is not null and score_equipe is not null
group by id_joueur
ORDER BY dateheure desc, id_joueur";
$res = $linkpdo->query($reqderniermatch);
while ($data = $res->fetch()) {
    $joueurs [$data[0]] = $data;
    $joueurs[$data[0]]["consecutif"] = 0;
}


$requeteJoueurs = "SELECT joueur.id_joueur,Prenom,joueur.Nom,statut , URLPhoto,Poste_Prefere, 
COUNT(*)as matchsJoues,AVG(joue.Performance) as moyenne, equipe.Nom as equipe 
from joueur, joue, matchs, equipe
WHERE joueur.id_joueur = joue.id_joueur
AND joue.id_match = matchs.id_match
and matchs.id_adversaire = equipe.id_equipe
and matchs.score_equipe is not null
and matchs.score_adversaire is not null
AND joue.Titulaire = ?
GROUP by joueur.id_joueur
ORDER by matchs.DateHeure desc, matchsJoues desc;";

$res = $linkpdo->prepare($requeteJoueurs);
$res->bindValue(1,1);
$res->execute();

while ($data = $res->fetch()) {
    $joueurs[$data[0]] = array_merge($joueurs[$data[0]],$data);
}

$res->bindValue(1,0);
$res->execute();
while ($data = $res->fetch()) {
    if (!isset($joueur[$data[0]]["matchsJoues"])) {
        $joueurs[$data[0]]  = array_merge($joueurs[$data[0]], $data);
        $joueurs[$data[0]]["matchsJoues"]=0;
    }
    $joueurs[$data[0]]["nbremplacant"] = $data["matchsJoues"];
}




$reqconsecutif = "SELECT id_joueur from joue
where id_match = (SELECT id_match from matchs where score_adversaire is not null and score_equipe is not null
and joue.titulaire = 1
ORDER BY dateheure desc
LIMIT 1 OFFSET 
?)";
$res = $linkpdo->prepare($reqconsecutif);
$offset = 0;
$res->bindParam(1,$offset,PDO::PARAM_INT);
try {
    $res->execute();
}
catch (PDOException $e) {
    echo $e->getMessage();
}


if ($data = $res->fetch()) {
    $restants = array();
    while ($data) {
        $joueurs[$data[0]]["consecutif"] += 1;
        array_push($restants, $data[0]);
        $data = $res->fetch();
    }
}
$offset++;
$res->bindParam(1, $offset,PDO::PARAM_INT);



while (!empty($restants) && $data = $res->execute()) {
    $temprestants = array();
    while ($data = $res->fetch()) {
        if (in_array($data[0],$restants)) {
            $joueurs[$data[0]]["consecutif"] += 1;
            array_push($temprestants, $data[0]);
        }
        $data = $res->fetch();
    }
    $offset++;
    $restants = $temprestants;

    $res->bindParam(1,$offset,PDO::PARAM_INT);
}


    ?>
   <table class='tableau-style'>
    <thead>
        <tr>
            <th>Photo</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Statut actuel</th>
            <th>Poste préféré</th>
            <th>Titulaire</th><th>Remplaçant</th>
            <th>Titularisations consécutives</th>
            <th>Performance moyenne</th>
            <th>Dernier match</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        foreach($joueurs as $joueur) {
                $urlphoto = "default.png";

            if (is_file(IMG_LOCATION . $joueur['URLPhoto'])) {
                $urlphoto = $joueur['URLPhoto'];
            }
            if (!isset($joueur["nbremplacant"])) {
                $joueur["nbremplacant"] = 0;
            }
            $dateaffiche = date("d/m/Y H:i", strtotime($joueur["datematch"]));
            echo '
            <tr>
                <td><img src="' . IMG_LOCATION . $urlphoto . '"> </td>';
            echo
                "<td>" . $joueur["Prenom"] . "</td>
            <td>" . $joueur["Nom"] . "</td>
            <td>" . $joueur["statut"] . "</td>
            <td>" . $joueur["Poste_Prefere"] . "</td>
            <td>" . $joueur["matchsJoues"] . "</td>" . "</td>
            <td>" . $joueur["nbremplacant"] . "</td>" . "</td>
            <td>" . $joueur["consecutif"] . "</td>" . "</td>
            <td>" . round($joueur["moyenne"], 3) . "/5</td>" . "</td>
            <td> <a href='evaluer.php?id=".$joueur["lastmatch"]."'>" .$joueur["equipe"]." (".$dateaffiche.")</td>" . "</a></td>";
        }
        ?>
    </tbody>
    </table>

    
</body>