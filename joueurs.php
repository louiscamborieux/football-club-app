

<?php 
//Connexion application
require_once "../config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION["login"] !== true) {
    header("location: identification.php");
    exit;
}


if (isset($_POST['logout'])) {
    header("location: identification.php");  
    session_destroy();
}
include(MENU_LOCATION);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Gerer les joueurs</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Gestion des joueurs du club">
    </head>
    <body>


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
<h1> Joueurs </h1>


<?php 
$requete = "SELECT * from joueur ";

//Execution de la requete
$res = $linkpdo->prepare($requete);
$res->execute();



if (!$data = $res->fetch()) {
    echo "<a href='ajouterjoueur.php'>Ajoutez des joueurs pour commencer</a>";
} else {
    echo "<table>
<tr>
<th>Photo</th><th>Nom</th><th>Prenom</th><th>Numéro de license</th><th>Date de naissance</th><th>Taille</th><th>Poids</th><th>Poste préféré</th> <th>Status</th>";
    echo "</tr>";
    $urlphoto = "default.png";
        if (is_file(IMG_LOCATION . $data[3])) {
            $urlphoto = $data[3];
        }
        echo '<td><img src="' . IMG_LOCATION . $urlphoto . '"> </td>';
        echo "<td>" . $data[1] . "</td><td>" . $data[2] . "</td><td>" . $data[4] . "</td><td>" . $data[5] . "</td><td>" . $data[6] . "</td>" . "</td><td>" . $data[7] . "</td>" . "</td><td>" . $data[8] . "</td>" . "</td><td>" . $data[9] . "</td>";
        echo '<td> <a href=modifier.php?id=' . $data[0] . '> Informations </a>';
        echo '<td> <a href=supprimer.php?id=' . $data[0] . '> Supprimer </a>';
        echo "</tr>";

    while ($data = $res->fetch()) {
        $urlphoto = "default.png";
        if (is_file(IMG_LOCATION . $data[3])) {
            $urlphoto = $data[3];
        }
        echo '<td><img src="' . IMG_LOCATION . $urlphoto . '"> </td>';
        echo "<td>" . $data[1] . "</td><td>" . $data[2] . "</td><td>" . $data[4] . "</td><td>" . $data[5] . "</td><td>" . $data[6] . "</td>" . "</td><td>" . $data[7] . "</td>" . "</td><td>" . $data[8] . "</td>" . "</td><td>" . $data[9] . "</td>";
        echo '<td> <a href=modifier.php?id=' . $data[0] . '> Informations </a>';
        echo '<td> <a href=supprimer.php?id=' . $data[0] . '> Supprimer </a>';
        echo "</tr>";
    }
    echo "</table>";
    echo '<a href="ajouterjoueur.php"> Ajouter un joueur </a>';
}
?>
