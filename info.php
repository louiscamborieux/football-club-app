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


//verification parametre url
if (empty($_GET['id'])) {
    header("location: accueil.php");
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

$requeteavg = "SELECT nom, joueur.prenom, AVG(Performance) as moyenne from joue,joueur 
where joueur.id_joueur = ?
and joueur.id_joueur = joue.id_joueur";

//Execution de la requete
$res = $linkpdo->prepare($requeteavg);
$res->bindParam(1, $_GET['id']);
$res->execute();

if (!($data = $res->fetch()) ) {
    echo "Erreur, joueur introuvable";
    echo '<a href="accueil.php"><button type="button">Retour</button></a>';
    exit;
}


$nomjoueur = $data["nom"] . " " . $data["prenom"];
$moyenne = round($data["moyenne"], 2);

?>


<!DOCTYPE html>
<html>
    <head>
        <title><?=$nomjoueur ?></title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Informations sur un joueur">
    </head>
    <body>


<h1><?=$nomjoueur?></h1>

<?php if (empty($data["moyenne"])) {
    echo "<h2>Aucun match joué</h2>";
    exit;
}
?>

<div>
    <p>Note moyenne <?=$moyenne?></p>
</div>

    <table class=".tableau-style">
        <thead>
            <th>Notes</th>
            <th>Performance</th>
            <th>Match</th>
        </thead>
        <tbody>


    <?php 
    $requetenotes = "SELECT NotePersonnelle, Performance, Dateheure, joue.id_match from joue, matchs 
    where id_joueur = ?
    and joue.id_match = matchs.id_match 
    and performance is not null;
    and matchs.score_adversaire is not null
    and matchs.score_equipe is not null";

    $res = $linkpdo->prepare($requetenotes);
    $res->bindParam(1, $_GET['id']);
    $res->execute();
    while ($data = $res->fetch()) {
        echo "<tr class ='notematch'>";
        echo "<td> " . $data["NotePersonnelle"] . " </td>";
        echo "<td>" . $data["Performance"] . "/5 </td>";
        echo "<td> <a href='evaluer.php?id=".$data["id_match"]."'>" . $data["Dateheure"] . " </a></td>";
        echo "</tr>";
    }
    ?>
        </tbody>
    </table>
    


