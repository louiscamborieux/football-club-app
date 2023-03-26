<?php 




 


session_start();



if (!isset($_SESSION['login']) || $_SESSION["login"] !== true) {

    header("location: identification.php");

    exit;

}



require_once "../config.php";







if (isset($_POST['logout'])) {

    header("location: identification.php");  

    session_destroy();

}

?>



<!DOCTYPE html>

<html>

    <head>

        <title>Ajouter joueur</title>

        <link rel="stylesheet" href="style.css">

        <meta charset="utf-8">

        <meta name="description" content="Ajouter un joueur">

    </head>

    <body>

        

        <?php

        include_once(MENU_LOCATION);



        //Creation du formulaire

        $accept = ["jpg","png","gif","jpeg"];

        $postes = ['Attaquant','Gardien','Defenseur','Milieu de terrain'];

        $requis = array('Nom', 'Prenom', 'numlicence', 'dateNaissance', 'Poids', 'Taille','postePref');

        $datemax = date('Y-m-d',strtotime(date('Y-m-d').' - 5 years')) ;

        $taillelicense = 10;

        ?>

        <h1> Ajouter un joueur</h1>

        <form enctype="multipart/form-data" method="post" name="AjoutJoueur" class="infos">

            <fieldset>

                <ul class="ul_1">

                <li class="li_1">   

                <label for="Nom" id="utilisateur_text">Nom</label>

                <input type = "text" name="Nom" id="Nom"  required = "required" id="utilisateur_input"></input>

                </li>

                <li class="li_1">

                <label for="Prenom">Prénom</label>

                <input type = "text" name="Prenom" id="Prenom"  required = "required"></input>

                </li>

                <li class="li_1">

                <label for="numlicence">numéro de licence</label><input type = "text" name="numlicence" id="numlicence" required = "required"maxlength="10"></input>

                </li>

                

                <li class="li_1">

                <label for ="dateNaissance">Date de naissance</label><input type ="date" name="dateNaissance" id="dateNaissance" value="2018-01-26" min="1980-01-01" max="2018-01-26" required = "required">

            </input></li>

            

            <li class="li_1">   

            <label for="Poids"> Poids</label> <input type ="number" name="Poids" id="Poids" value= "50" min="0" max="150" step="0" name="Poids" required = "required"></input>

            </li>

            <li class="li_1">

            <label for="Taille" > Taille</label> <input type ="number" name="Taille" id="Taille" value= "" min="0" max="3" step=".01" name="Taille" required = "required"></input>

            </li>

            <li class="li_1">

            <label for="postePref">Poste préféré</label>

            <select name="postePref" id="postePref"  required = "required" >

            </li>

            

            <?php foreach ($postes as $poste) {

                echo "<option value=$poste>$poste</option>";

            } ?>

            <br>

            <li class="li_1">

            <input type="file" name="photo" id="photo" accept =".jpg, .png, .gif, .jpeg"></input>

            </li>

            <li class="li_1">

            <input type="hidden" name="token" value="<?=$_SESSION["token"]?>" >

            </li>

            <li class="li_1">

            <input type=submit value="Enregistrer" name="submit" id="button1">

            </li>

            <!--

            <li class="li_1">

            <input type=reset value="Vider" id="button1">

            </li>

            -->

            <li class="li_1">

            <a href="accueil.php"><button type="button" id="button1">Retour</button></a>

            </li>

            </ul>

            </fieldset></form>        

            <a href="accueil.php">





        

        <?php



        







        //Vérifie si l'image est valide dans $_FILES

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



        if(!empty($_SESSION['token']) AND(isset($_POST['token'])) AND ($_POST['token'] == $_SESSION['token'])) {

            if (time() < $_SESSION['expiration']) {

                $tokencheck = true;

            }

            else {

                $tokenerror = "Erreur, TOKEN d'accès expiré";

            }

        }



    



        $champsvide = array();

        foreach ($requis as $champ) {

            if (empty($_POST[$champ])) {

                array_push($champsvide,$champ);

            }

        }



        $checkrempli = empty($champsvide);

    

        if (!$checkrempli) {

            $donneesmanquantes =  "champs obligatoires non remplis : ";

            foreach($champsvide as $champ) {

                $donneesmanquantes = $donneesmanquantes." ".$champ;

            }

            array_push($erreursformulaire, $donneesmanquantes);

        }



        if ($_POST['Taille'] >3 || $_POST['Taille'] <0 ) {

            array_push($erreursformulaire, "La taille doit être comprise entre 0 et 3m");

        }



        if ($_POST['Poids'] < 0 ) {

            array_push($erreursformulaire, "Le poids doit être positif");

        }



        if (!in_array($_POST['postePref'],$postes)) {

            array_push($erreursformulaire, "Le poste est invalide");

            echo  "<pre>".print_r($_POST,TRUE)."</pre>";

            echo  "<pre>".print_r($postes)."</pre>";

        }



        if (false === strtotime($_POST['dateNaissance'])) { 

            array_push($erreursformulaire, "Format date invalide");

            

        } else {

            if ( strtotime($_POST['dateNaissance']) > strtotime($datemax)) {

                array_push($erreursformulaire, "La date est trop avancée, un joueur doit-avoir au moins 5 ans");

            }

            if ( strtotime($_POST['dateNaissance']) < strtotime("1980-01-01")) {

                array_push($erreursformulaire,"La date trop reculée, un joueur doit-avoir être né avant le 1/1/1980");

            }

        }



        if (!preg_match('/^[1-9]{1}\d{9}$/',$_POST['numlicence'])) {

            array_push($erreursformulaire,"Format license, utilisez le format standard dix chiffres X XXX XXX XXX");

        }



        $successphoto = false;

        if (!empty($_FILES['photo']['tmp_name'])) {

        echo "check de l'image";

            $photo = true;

            $successphoto = checkimage("photo",$accept);

            if (!$successphoto) {

                array_push($erreursformulaire, "Photo refusée");

            }

        }

        else {

            $photo = false;

        }





        if (empty($erreursformulaire) AND $tokencheck) {

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

            else {

                echo "connection successful";

            }

    

            $requete = "INSERT into joueur VALUES(DEFAULT,:nom,:prenom,:urlphoto,:numlicence,:datenaissance,:taille,:poids,:poste,'Actif') ";

    

            //Execution de la requete

            $req = $linkpdo->prepare($requete);

            $req->bindValue(':nom', secure_input($_POST['Nom']));

            $req->bindValue(':prenom',secure_input($_POST['Prenom']));

            $req->bindValue(':numlicence',secure_input($_POST['numlicence']));

            $req->bindValue(':datenaissance',($_POST['dateNaissance']));

            $req->bindValue(':taille',secure_input($_POST['Taille']));

            $req->bindValue(':poids',secure_input($_POST['Poids']));

            $req->bindValue(':poste',secure_input($_POST['postePref']));

    

            /*Code upload de l'image */

    

            if (!$photo) {

                $req->bindValue(":urlphoto", null, PDO::PARAM_NULL);

                echo "pas de photo";

            } 

            else {

                echo "image acceptée";

                //Conversion image en format 1/1 150px avant upload

        

                $nom = hash('snefru', basename($_POST["Prenom"]) .$_POST["Nom"]. time()) . ".png"; // generation nom pour eviter problèmes liés à deux images du même nom

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

    </body>

</html>



