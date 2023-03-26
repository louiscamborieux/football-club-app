<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <title>Supprimer</title>
    
    
    </head>
    <body id="connexion-background"><?php 

session_start();

if (!isset($_SESSION['login']) || $_SESSION["login"] !== true) {
    header("location: identification.php");
    exit;
}

require_once "../config.php";
require_once "../form.php";



if (isset($_POST['logout'])) {
    header("location: identification.php");  
    session_destroy();
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

if (!isset($_GET["type"])) {
    echo 'erreur données ';
    echo '<a href="'.$backlink.'"><button type="button">Retour</button></a>';
    exit;
}


switch ($_GET["type"]) {
    case "m":
        $requete = "SELECT * from matchs where id_match = ?";
        $table = "match";
        $id_field = "id_match";
        $alertestring = "Voulez-vous supprimer ce match, cette action est <strong>irréversible</strong> ? ";
        $requeteEntite = 'DELETE from matchs where '.$id_field.' = ?';
        break;
    case "j":
        $requete = "SELECT * from joueur where id_joueur = ?";
        $table = "joueur";
        $id_field = "id_joueur";
        $requeteEntite = 'DELETE from joueur where '.$id_field.' = ?';
        break;
    default:
        echo "Erreur table";
        exit;
}

//Vérification de l'existence du joueur
$res = $linkpdo->prepare($requete);
$res->bindParam(1, $_GET['id']);
$res->execute();

if (!($data = $res->fetch())) {
    echo "Erreur, données introuvables";
    echo '<a href="'.$backlink.'"><button type="button">Retour</button></a>';
    exit;
}

if (isset($_POST['delete'])) {

    //Verification du token
    $tokencheck = false;
        $tokenerror = "Token invalide";

        if(!empty($_SESSION['token']) AND ($_POST['token'] == $_SESSION['token'])) {
            if (time() < $_SESSION['expiration']) {
                $tokencheck = true;
            }
            else {
                $tokenerror = 'Erreur, TOKEN d\'accès expiré
                 <form method="post"> 
    <input type="submit" name="logout" value="Se Reconnecter"> 
    </form>';
            }
        }

    $requeteAssoc = 'DELETE from joue where '.$id_field.' = ?';
    

    $res = $linkpdo->prepare($requeteAssoc);
    $res->bindParam(1, $_GET['id']);

    //Suprresion de l'image du serveur si elle existe 
    

    if (!$tokencheck) {
        echo $tokenerror;
        exit;
    }

    if (is_file(IMG_LOCATION.$data[3])) {
        unlink(IMG_LOCATION . $data[3]);
    }

    if ($res->execute()) {
        $res = $linkpdo->prepare($requeteEntite);
        $res->bindParam(1, $_GET['id']);
        $res->execute();
    }
    else {
        echo "erreur suppression données matchs.";
    }

    header("location: accueil.php");  
}


?>



<div class="conex_box">
<form method="post" class="form_L"> 
<h1>Supprimer</h1>
 <div class="container2">
     <p> 
<?php 
switch ($_GET["type"]) {
    case "m":
        echo "Voulez-vous supprimer ce match, cette action est <strong>irréversible</strong> ? ";
        break;
    case "j":
            echo "Souhaitez vous vraiment <strong>Supprimer le joueur ". $data[1]." ".$data[2]."</strong>   ainsi que toutes ses informations associées ? Cette action est <strong>irreversible.</strong>";
        break;
    default:
        echo "Erreur table";
        exit;
}?>
</p>
 <button type="submit" name="delete" class="container1"  id="button1">Supprimer</button>
 <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" >
 <a href="<?php echo $backlink;?>"><button type="button" id="button1">Retour</button></a>
</div>

</form>
</div>



    </body>

</html>

