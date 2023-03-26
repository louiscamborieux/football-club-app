

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
?>

<head>    
        <title>Accueil</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Connexion à TFC manager">
</head>

<?php
require_once "../config.php";
include_once(MENU_LOCATION);
?>


<?php 
//Connexion application



if (isset($_POST['logout'])) {
    header("location: identification.php");  
    session_destroy();
}
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
<h1> Joueurs </h1>


<?php 
$requete = "SELECT * from joueur ";

//Execution de la requete
$res = $linkpdo->prepare($requete);
$res->execute();



$data = $res->fetch();
if (!$data) {
    echo "<a href='ajouterjoueur.php'>Ajoutez des joueurs pour commencer</a>";
} else {
    echo '<a href="ajouterjoueur.php" class="button"> Ajouter un joueur </a>';
    //Tableau central
    echo 
    "<table class='tableau-style'>
    
        <thead>
            <tr>
                <th>Photo</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Numéro de licence</th>
                <th>Date de naissance</th>
                <th>Taille</th><th>Poids</th>
                <th>Poste préféré</th>
                <th>Statut</th>
            </tr>
        </thead>
        
        <tbody>";
    while ($data) {
        $urlphoto = "default.png";
        if (is_file(IMG_LOCATION . $data[3])) {
            $urlphoto = $data[3];
        }
        echo '
        <tr>
            <td><img src="' . IMG_LOCATION . $urlphoto . '"> </td>';
        echo 
            "<td>" . $data[1] . "</td>
            <td>" . $data[2] . "</td>
            <td>" . $data[4] . "</td>
            <td>" . date("d/m/Y", strtotime($data[5])) . "</td>
            <td>" . $data[6] . "m</td>" . "</td>
            <td>" . $data[7] . "kgs</td>" . "</td>
            <td>" . $data[8] . "</td>" . "</td>
            <td>" . $data[9] . "</td>";
        echo 
            '<td> <a href=modifier.php?id=' . $data[0] . '> Informations </a>';
        echo 
            '<td> <a href=supprimer.php?id=' . $data[0] . '&type=j> Supprimer </a>';
        echo 
        "</tr>";
        $data = $res->fetch();
    }
    echo 
    "   </tbody>
    </table>";
    
}
?>

