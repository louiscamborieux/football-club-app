<?php
session_start();


if (!isset($_SESSION['login']) || $_SESSION["login"] !== true) {
    header("location: identification.php");
    exit;
}

if (isset($_POST['logout'])) {
    header("location: identification.php");  
    session_destroy();
}

require_once "../config.php";
?>

<!DOCTYPE html>
<html>
<head>    
        <title>Matchs</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Connexion à TFC manager">
</head>

<?php
include_once(MENU_LOCATION);
?>

<?php
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
 ?>
 <h1> Matchs </h1>

 <?php 
 $requete = "SELECT Domicile_Exterieur, Dateheure, Score_Equipe, Score_Adversaire, Nom, id_match from matchs, equipe 
 where matchs.id_adversaire = equipe.id_equipe";
 
 //Execution de la requete
 $res = $linkpdo->prepare($requete);

 try {
     $res->execute();

 }
 catch (PDOException $e) {
     die('Erreur' . $e->getMessage());
 }
 $data = $res->fetch();
 if (!$data) {
     echo "<a href='composer.php'>Commencez à composer votre prochain match</a>";
 } else {
    echo "<table>";
     while ($data) {
        $stringDomExt = $data["Domicile_Exterieur"] == "E" ? "exterieur" : "domicile";
        $score = 'À déterminer';
        if ($data[2] != null) {
            $score = $data[2] .'-'.$data[3];
        }
        echo '<td>'.$data[4].'</td>';
        echo "<td>". date("d/m/Y", strtotime($data[1]))."</td>";
        echo "<td> ".$stringDomExt." </td>"; 
        echo '<td>'.  $score. '</td>';
        echo '<td> <a href=supprimer.php?id=' . $data[5] . '&type=m> Supprimer </a>';
        echo '<td> <a href=evaluer.php?id=' . $data[5] . '> Évaluer </a>';
        echo "</tr>";
        $data = $res->fetch();
     }
     echo "</table>";
     echo '<a href="composer.php"> Composer un match </a>';
 }
 ?>

</html>
