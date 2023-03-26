<!DOCTYPE html>

<html>

    <head>
        <title>Composer un match</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Gestion des joueurs du club">
    </head>
    <body>


<?php 

//Connexion application
require_once "../config.php";
require_once "../form.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION["login"] !== true) {
    header("location: identification.php");
    exit;
}

if (isset($_POST['logout'])) {
    header("location: identification.php");  
    session_destroy();
}

$backlink = 'accueil.php';
if(isset($_SERVER['HTTP_REFERER']) ) {
    $backlink = $_SERVER['HTTP_REFERER'];
}

?>

<?php
//Connexion DB
try{
    $linkpdo =new PDO('mysql:host='.DB_HOST.'; dbname='.DB_NAME,DB_USER,DB_PASS);
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

$domicileExt = ['D' => 'Domicile', 'E' => 'Exterieur'];
$infoselect = "Selectionnez un joueur";
$today = date('Y-m-d\TH:m',time()) ;
$datemax = date('Y-m-d\TH:m',strtotime(date('Y-m-d').' + 10 years')) ;
$requis = array('domext','datetime','Equipe');
$requisJoueurs = array('Gardien', 'J1', 'J2', 'J3', 'J4', 'J5', 'J6', 'J7', 'J8', 'J9', 'J10');

$Remplaçants = array('R1', 'R2', 'R3', 'R4', 'R5');


//Recuperation joueurs actifs

$requeteActifs = "SELECT id_joueur, Nom, Prenom, Taille, Poids,Poste_Prefere from joueur where Statut = 'Actif'";
$actifs = [];

$res = $linkpdo->prepare($requeteActifs);



try {
    $res->execute();
}

catch (PDOException $e) {
    echo "Erreur DB : " . $e->getMessage();
}



while ($data = $res->fetch()) {
    $actifs[$data[0]] = $data[2]." ".$data[1]. " ".$data[3]."m ".$data[4]."kgs (".$data[5].")";
}



//Recuperation equipes

$requeteEquipes = "Select * from equipe";
$equipes = [];



$res = $linkpdo->prepare($requeteEquipes);
$res->execute();



while ($data = $res->fetch()) {
    $equipes[$data[0]] = $data[1];
}


?>
<?php
if (isset($_POST['submit'])) {
    $erreursformulaire = array();
    $tokencheck = false;
    $tokenerror = "Token invalide";

    if (!empty($_SESSION['token']) AND(!empty($_POST['token'])) and ($_POST['token'] == $_SESSION['token'])) {
        if (time() < $_SESSION['expiration']) {
            $tokencheck = true;
        } else {
            $tokenerror = "Erreur, TOKEN d'accès expiré";
        }
    }

    $champsvide = array();
    foreach ($requis as $champ) {
        if (empty($_POST[$champ])) {
            array_push($champsvide, $champ);
        }
    }

    $checkrempli = empty($champsvide);

    if (!$checkrempli) {
        $donneesmanquantes = "champs obligatoires non remplis : ";
        foreach ($champsvide as $champ) {
            $donneesmanquantes = $donneesmanquantes . " " . $champ;
        }
        array_push($erreursformulaire, $donneesmanquantes);
    }

    //Verification de l'equipe adverse
    if (empty($_POST['Equipe']) || !in_array($_POST['Equipe'],array_keys($equipes)))
    {
        array_push($erreursformulaire, "Equipe renseignée inexistante");
    }

    //Verification domicile exterieur

    if (empty($_POST['domext']) || !in_array($_POST['domext'],array_keys($domicileExt)))

    {
        array_push($erreursformulaire, "Erreur champ Domicile/Exterieur");
    }



    //Verification date/heure du match

    if (false === strtotime($_POST['datetime'])) { 
        array_push($erreursformulaire, "Format date invalide");
    } else {
        if ( strtotime($_POST['datetime']) > strtotime($datemax)) {
            array_push($erreursformulaire, "La date est trop avancée.");
        }
        if ( strtotime($_POST['datetime']) < strtotime($today)) {
            array_push($erreursformulaire,"On ne peut planifier un match anterieur à aujourd'hui");
        }
    }

    function secure_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
      }

    $checkjoueurs = true;
    $JoueursUniques = [];

    foreach ($requisJoueurs as $champ) {
        if (empty($_POST[$champ])) {
            echo "Tous les joueurs n'ont pas étés renseignés.";
            $checkjoueurs = false;
            break;
        } elseif (!in_array($_POST[$champ], array_keys($actifs))) {
            echo "Joueur". $_POST[$champ]." non présent ou non disponible";
            $checkjoueurs = false;
            break;
        }

        elseif (in_array($_POST[$champ], $JoueursUniques)) {
            echo "Joueur ".$actifs[$_POST[$champ]]." sélectionné plusieurs fois";
            $checkjoueurs = false;
            break;
        }

        else {
            array_push($JoueursUniques,$_POST[$champ]);
        }

    }

    foreach ($Remplaçants as $champ) {
        if (!empty($_POST[$champ])) {
            if (!in_array($_POST[$champ], array_keys($actifs))) {
                echo "Joueur". $_POST[$champ]." non présent ou non disponible";
                $checkjoueurs = false;
                break;
            }

            elseif (in_array($_POST[$champ], $JoueursUniques)) {
                echo "Joueur ".$actifs[$_POST[$champ]]." sélectionné plusieurs fois";
                $checkjoueurs = false;
                break;
            }

            else {
                array_push($JoueursUniques,$_POST[$champ]);
            }
        }
    }



    if (empty($erreursformulaire) AND $checkjoueurs AND $tokencheck) {
        //Ajout du match







        

        

    }

    elseif (!empty($erreursformulaire)) {
        echo "Erreurs lors du remplissage du formulaire : <br/>";
        foreach($erreursformulaire as $erreur) {
            echo $erreur.'<br/>';
        }
    }

    else {
        echo $tokenerror;
    }

    
    echo  "<pre>".print_r($_POST,TRUE)."</pre>";
    
} ?>
<?php 
include(MENU_LOCATION);
?>
<h1> Composer match </h1>

<?php




$formMatch = new form("CompoMatch");
$formMatch->setradio("domext",$domicileExt,required:true);
$formMatch->setselect("Equipe",$equipes,"Equipe Adverse","Selectionnez une equipe",true);
$formMatch->setdatetime("datetime", $datemax,label:"Date et Heure", required: true, min: $today, valeur: $today);
$formMatch->setselect("Gardien", $actifs,info:$infoselect,required:true);
$formMatch->setselect("J1",$actifs,"Défenseur #1", info:$infoselect,required:true);
$formMatch->setselect("J2",$actifs,"Défenseur #2", info:$infoselect,required:true);
$formMatch->setselect("J3",$actifs,"Défenseur #3", info:$infoselect,required:true);
$formMatch->setselect("J4",$actifs,"Défenseur #4",info:$infoselect,required:true);
$formMatch->setselect("J5",$actifs,"Milieu #1",info:$infoselect,required:true);
$formMatch->setselect("J6",$actifs,"Milieu #2",info:$infoselect,required:true);
$formMatch->setselect("J7",$actifs,"Milieu #3",info:$infoselect,required:true);
$formMatch->setselect("J8",$actifs,"Attaquant #1",info:$infoselect,required:true);
$formMatch->setselect("J9",$actifs,"Attaquant #2",info:$infoselect,required:true);
$formMatch->setselect("J10",$actifs,"Attaquant #3",info:$infoselect,required:true);
$formMatch->setselect("R1",$actifs,"Remplaçant #1",info:$infoselect);
$formMatch->setselect("R2",$actifs,"Remplaçant #2",info:$infoselect);
$formMatch->setselect("R3",$actifs,"Remplaçant #3",info:$infoselect);
$formMatch->setselect("R4",$actifs,"Remplaçant #4",info:$infoselect);
$formMatch->setselect("R5",$actifs,"Remplaçant #5",info:$infoselect);
$formMatch->sethidden("token", $_SESSION['token']);

$formMatch->getform();
/*
echo  "<pre>".print_r($_FILES,TRUE)."</pre>";
*/
echo  "<pre>".print_r($_POST,TRUE)."</pre>";

?>
<a href="<?php echo $backlink;?>"><button type="button">Retour</button></a>
