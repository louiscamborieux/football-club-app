

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
/*
echo  "<pre>".print_r($_GET,TRUE)."</pre>";
*/


if (isset($_POST['logout'])) {
    header("location: identification.php");  
    session_destroy();
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

$requete = "SELECT * from joueur where id_joueur = ?";


//Execution de la requete
$res = $linkpdo->prepare($requete);
$res->bindParam(1, $_GET['id']);
$res->execute();

if (!($data = $res->fetch())) {
    echo "Erreur, joueur introuvable";
    echo '<a href=' . $backlink . '><button type="button">Retour</button></a>';
    exit;
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Modifier un joueur</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Gestion des joueurs du club">
    </head>
    <body>
<?php include_once(MENU_LOCATION); ?>
<h1> Modifier Informations joueur</h1>
<form class="infos" method="post" enctype="multipart/form-data">

<?php 

$postes = ['Attaquant','Gardien','Defenseur','Milieu de terrain'];
$statuts = ['Actif','Blesse','Suspendu','Absent'];


echo '
<fieldset>
<ul class="ul_1">

<li class="li_1">
<label for="Nom" class="pad_form_m">Nom</label>
<input type = "text" name="Nom" id="Nom"  required = "required" value="'.$data[1].'"></input><br/>
</li>
<li class="li_1">
<label for="Prenom" class="pad_form_m">Prénom</label>
<input type = "text" name="Prenom" id="Prenom"  required = "required"  value="'.$data[2].'"></input><br/>
</li>
<li class="li_1">
<label for="numlicence">numéro de licence</label>
<input type = "text" name="numlicence" id="numlicence"  required = "required"maxlength="10"  value="'.$data[4].'"></input><br/>
</li>
<li class="li_1">
<label for ="dateNaissance">Date de naissance</label>
<input type ="date" name="dateNaissance" id="dateNaissance" value="2017-12-22" min="1980-01-01" max="2017-12-22" required = "required"  value='.$data[5].'></input><br/>
</li>
<li class="li_1">
<label for="Poids"> Poids</label> 
<input type ="number" name="Poids" id="Poids"  value="'.$data[7].'" min="0" max="150" step="0" name="Poids" required = "required" ></input><br/>
</li>
<li class="li_1">
<label for="Taille"> Taille</label> 
<input type ="number" name="Taille" id="Taille"  value="'.$data[6].'" min="0" max="3" step=".01" name="Taille" required = "required" ></input><br/>
</li>
<li class="li_1">
<label for="postePref">Poste préféré</label> 
<select name="postePref" id="postePref"  required = "required"  ">
</li>

';

foreach($postes as $poste) {
    echo '<option value="'.$poste.'"';
    if ($poste == $data[8]) {
    echo ' selected' ;
    }
    echo '>' . $poste . '</option>';

}
echo '
<li class="li_1">
</select><br/>
</li>
<li class="li_1">
<label for="status">Status</label> 
<select name="status" id="status" required = "required"   >
</li>
</ul>';
foreach($statuts as $statut) {
    echo '<option value="'.$statut.'"';
    if ($statut == $data[9]) {
    echo ' selected' ;
    }
    echo '>' . $statut . '</option>';
}
echo '
<label for="photo">photo</label>
<input type="file" name="photo" id="photo" accept =".jpg, .png, .gif, .jpeg"></input><br/>

<input type="hidden" name="token" value="'.$_SESSION['token'].'" ><br/>

<input type=submit value="Valider" name="submit" id="button1">
';
?>
<a href='<?php echo $backlink;?>'><button type="button" id="button1">Retour</button></a>

<?php echo '</fieldset>';

$urlphoto = $data[3];
if (!is_file(IMG_LOCATION.$urlphoto)) {
    $urlphoto = 'default.png';
}
echo  "<img src='".IMG_LOCATION.$urlphoto."'>";
 ?>
</form>



<?php
$accept = ["jpg","png","gif","jpeg"];

$requis = array('Nom', 'Prenom', 'numlicence', 'dateNaissance', 'Poids', 'Taille','postePref');
$datemax = date('Y-m-d',strtotime(date('Y-m-d').' - 5 years')) ;
$taillelicense = 10;

function checkimage($nom,$typesAcceptes)
{
    $fichier = IMG_LOCATION . basename($_FILES[$nom]["name"]);
    $typeImage = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
    if (!isset($_POST["submit"])) {
        echo "formulaire non envoye";
        return false;
    }
    if (!in_array($typeImage, $typesAcceptes)) {
        echo "Format incorrect";
        return false;
    }
    switch ($_FILES[$nom]['error']) {
        case UPLOAD_ERR_OK:
            break;
    case UPLOAD_ERR_NO_FILE:
        echo "aucun fichier envoyé";
            return false;
    case UPLOAD_ERR_INI_SIZE:
        echo "fichier trop volumineux";
        return false;
        default:
            echo('Erreur lors de l\'envoi');
        return false;
    }
    $check = getimagesize($_FILES[$nom]["tmp_name"]);
    if ($check == false) {
        echo "image fausse";
        return false;
    }
    if ($_FILES[$nom]["size"] > 2000000) {
        echo "Taille maximum : 2Mo";
        return false;
    }
    
    return  TRUE;     
}


function secure_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

if (isset($_POST['submit'])) {
    $erreursformulaire = array();
    $tokencheck = false;
    $tokenerror = "Token invalide";

    if (!empty($_SESSION['token']) and ($_POST['token'] == $_SESSION['token'])) {
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

    if ($_POST['Taille'] > 3 || $_POST['Taille'] < 0) {
        array_push($erreursformulaire, "La taille doit être comprise entre 0 et 3m");
    }

    if ($_POST['Poids'] < 0) {
        array_push($erreursformulaire, "Le poids doit être positif");
    }

    if (!in_array($_POST['postePref'], $postes)) {
        array_push($erreursformulaire, "Le poste est invalide");
    }

    if (false === strtotime($_POST['dateNaissance'])) {
        array_push($erreursformulaire, "Format date invalide");
    } else {
        if (strtotime($_POST['dateNaissance']) > strtotime($datemax)) {
            array_push($erreursformulaire, "La date est trop avancée, un joueur doit-avoir au moins 5 ans");
        }
        if (strtotime($_POST['dateNaissance']) < strtotime("1980-01-01")) {
            array_push($erreursformulaire, "La date trop reculée, un joueur doit-avoir être né avant le 1/1/1980");
        }
    }

    if (!preg_match('/^[1-9]{1}\d{9}$/', $_POST['numlicence'])) {
        array_push($erreursformulaire, "Format license, utilisez le format standard dix chiffres X XXX XXX XXX");
    }

    $successphoto = false;
    if (!empty($_FILES['photo']['tmp_name'])) {
        echo "check de l'image";
        $photo = true;
        $successphoto = checkimage("photo", $accept);
        if (!$successphoto) {
            array_push($erreursformulaire, "Photo refusée");
        }
    } else {
        $photo = false;
    }

    if (empty($erreursformulaire) and $tokencheck) {
        echo "rekete";
        $requete = "UPDATE joueur 
        set nom = :nom, prenom = :prenom,urlphoto = :urlphoto, numerolicense = :numlicence, datenaissance = :datenaissance,taille = :taille,poids = :poids,poste_prefere = :poste,statut = :statut
        WHERE id_joueur= :id";

        $req = $linkpdo->prepare($requete);
        $req->bindValue(':id', secure_input($data[0]));
        $req->bindValue(':nom', secure_input($_POST['Nom']));
        $req->bindValue(':prenom',secure_input($_POST['Prenom']));
        $req->bindValue(':numlicence',secure_input($_POST['numlicence']));
        $req->bindValue(':datenaissance',($_POST['dateNaissance']));
        $req->bindValue(':taille',secure_input($_POST['Taille']));
        $req->bindValue(':poids',secure_input($_POST['Poids']));
        $req->bindValue(':poste',secure_input($_POST['postePref']));
        $req->bindValue(':statut', secure_input($_POST['status']));

                    


        if (!$photo) {
            $req->bindValue(":urlphoto", $data[3]);
            echo "pas de photo";
        } 
        else {
            echo "image acceptée";
            //Conversion image en format 1/1 150px avant upload
    
            $nom = hash('snefru', basename($_POST["Prenom"]) .$_POST["Nom"]. time()) . ".png"; // generation nom pour eviter deux images du même nom
            $image = imagecreatefromstring(file_get_contents($_FILES["photo"]["tmp_name"])); // recupération des données quelque soit le format
            $width = imagesx($image);
            $height = imagesy($image);
            $dest = imagecreatetruecolor(TAILLE_PHOTO, TAILLE_PHOTO); //image de destination
    
            if ($width > $height) {
                // Calcule le point de rognage en largeur
                $point_coupe = (($width - $height) / 2);
                imagecopyresampled($dest, $image, 0, 0, $point_coupe, 0, TAILLE_PHOTO, TAILLE_PHOTO, $height, $height);
            }
            if ($height >= $width) {
                // Calcule le point de rognage en hauteur
                $point_coupe = (($height - $width) / 2);
                imagecopyresampled($dest, $image, 0, 0, 0, $point_coupe, TAILLE_PHOTO, TAILLE_PHOTO, $width, $width);
            }

            //Upload image convertie
            imagepng($dest, IMG_LOCATION . $nom);
            $req->bindValue(':urlphoto', $nom);

            //Suppressuion de l'ancienne image
            if (is_file(IMG_LOCATION.$data[3])) {
                unlink(IMG_LOCATION . $data[3]);
            }     

        }

            try {
                $req->execute();
            header("location: accueil.php");
            }
            catch (PDOException $e) {
                print "Erreur SQL " .$e->getMessage();
            }
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

}




?>
