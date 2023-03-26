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

<!DOCTYPE html>

<html>

    <head>
        <title>Feuille de match</title>
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

$domicileExt = ['D' => 'Domicile', 'E' => 'Exterieur'];
$infoselect = "Selectionnez un joueur";
$today = date('Y-m-d\TH:m',time()) ;
$datemax = date('Y-m-d\TH:m',strtotime(date('Y-m-d').' + 10 years')) ;




include(MENU_LOCATION);
?>
<h1> Feuille de match </h1>
<form method="post" enctype="multipart/form-data" id="form_comp">
    <fieldset>
        <input type=radio  name="domext" checked id="dom" value="D">
        <label for="dom">Domicile</label>
        <input type=radio  name="domext" id="ext" value="E">
        <label for="ext">Exterieur</label>
        <input type="datetime-local" name="datetime"  value="<?=$today ?>" max="<?=$datemax?>">
    <select name="Equipe">
        <?php //Recuperation equipes
        $requeteEquipes = "Select * from equipe order by nom";
        $equipes = [];
        $res = $linkpdo->prepare($requeteEquipes);
        $res->execute();
        while ($data = $res->fetch()) {
        array_push($equipes,$data[0]);
        echo '<option value="'.$data[0].'">'.$data[1].'</option>'; 
        }?>
    </select>
    </fieldset>
<?php
$MAXREMPLACANT = 9;
$MAXTITULAIRES = 11;
//Recuperation joueurs actifs

$requeteActifs = "SELECT id_joueur, Nom, Prenom, Taille, Poids,Poste_Prefere, URLPhoto from joueur where Statut = 'Actif' order by field(Poste_Prefere, 'Gardien', 'Defenseur', 'Milieu de terrain', 'Attaquant')";
$actifs = [];

$res = $linkpdo->prepare($requeteActifs);

try {
    $res->execute();
}

catch (PDOException $e) {
    echo "Erreur DB : " . $e->getMessage();
    exit;
}
?>

        <!--
        <div  id="container_form">
        <img src="<?=IMG_LOCATION.$urlphoto ?>" class="el_pad_comp">f
        
        <label class="el_pad_comp"> Selectionné
        <input type=checkbox name="selection[<?=$data[0]?>] id=selection[<?=$data[0]?>]" class="el_pad_comp"> </label>
        <label class="el_pad_comp"> Titulaire
        <input type=radio name="titulaire[<?=$data[0]?>]" checked value="titulaire" class="el_pad_comp"> </label>
        <label class="el_pad_comp"> Remplaçant
        <input type=radio name="titulaire[<?=$data[0]?>]" value="remplacant" class="el_pad_comp"> </label>
            <span><?=$data[2].' '.$data[1]?></span>
            <span><?=$data[3].'m '.$data[4].'kgs'?></span>
            <span><?=$data[5]?></span>
        </div>
        -->

        <script>
            function pop_up(url){
                window.open(url,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1076,height=768,directories=no,location=no') 
                }
        </script>
        
        <table  id="tableau_style">
        <thead>
        <th>Photo</th>
        <th>Selection</th>
        <th>Rôle</th>
        <th>Prénom</th>
        <th>Nom</th>
        <th>Taille</th>
        <th>Poids</th>
        <th>Poste</th>
        <th>Notes</th>
        </thead>
        
        <tbody>
        <?php

        while ($data = $res->fetch()) {
            array_push($actifs, $data[0]);?>
                <?php $urlphoto = "default.png"; 
                if (is_file(IMG_LOCATION . $data["URLPhoto"])) {
                    $urlphoto = $data["URLPhoto"];
                }?>
        <tr>
        <td>
        <img src="<?=IMG_LOCATION.$urlphoto ?>" class="el_pad_comp">
        </td>
        <td>
        <label class="el_pad_comp"> Selectionné
        <input type=checkbox name="selection[<?=$data[0]?>] id=selection[<?=$data[0]?>]" class="el_pad_comp"> </label>
        </td>
        <td>
        <label class="el_pad_comp"> Titulaire
        <input type=radio name="titulaire[<?=$data[0]?>]" checked value="titulaire" class="el_pad_comp"> </label>
        <label class="el_pad_comp"> Remplaçant
        <input type=radio name="titulaire[<?=$data[0]?>]" value="remplacant" class="el_pad_comp"> </label>
        </td>
            <td>
            <span><?=$data[2]?></span>
            </td>
            <td>
            <span><?=$data[1]?></span>
            </td>
            <td>
            <span><?=$data[3].'m'?></span>
            </td>
            <td>
            <span><?=$data[4].'kgs'?></span>
            </td>
            <td>
            <span><?=$data[5]?></span>
            </td>
            <td>
                <?php $url = "info.php?id=".$data[0];?>
            <span><a href="#" onclick="pop_up('<?=$url?>');">Notes </a></span>
            </td>
        </tr>
        
        
        <?php
}
?>      
        </tbody>
        </table>

    
    <input type="hidden" name="token" value=<?=$_SESSION['token']?>>
    <input type="submit" name="submit" value="Valider" id="button1">
</form>


<?php
if (isset($_POST['submit'])) {
    $erreursformulaire = array();
    $tokencheck = false;
    $tokenerror = "Token invalide";

    if (!empty($_SESSION['token']) and (!empty($_POST['token'])) and ($_POST['token'] == $_SESSION['token'])) {
        if (time() < $_SESSION['expiration']) {
            $tokencheck = true;
        } else {
            $tokenerror = "Erreur, TOKEN d'accès expiré";
        }
    }






    //Verification de l'equipe adverse
    if (empty($_POST['Equipe']) || !in_array($_POST['Equipe'], $equipes)) {
        array_push($erreursformulaire, "Equipe renseignée inexistante");
        echo  "<pre>".print_r($_POST,TRUE)."</pre>";
        echo  "<pre>".print_r($equipes,TRUE)."</pre>";
    }

    //Verification domicile exterieur

    if (empty($_POST['domext']) || !in_array($_POST['domext'], array_keys($domicileExt))) {
        array_push($erreursformulaire, "Erreur champ Domicile/Exterieur");
    }



    //Verification date/heure du match

    if (false === strtotime($_POST['datetime'])) {
        array_push($erreursformulaire, "Format date invalide");
    } else {
        if (strtotime($_POST['datetime']) > strtotime($datemax)) {
            array_push($erreursformulaire, "La date est trop avancée.");
        }
    }

    function secure_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $JoueursUniques = [];

    //Verification joueurs selectionnes
    if (!isset($_POST['selection'])) {
        array_push($erreursformulaire, "Selection vide");
    }

     //Verification joueurs titulaires
     if (!isset($_POST['titulaire'])) {
        array_push($erreursformulaire, "Selection de titulaires vide");
    }
    

    if (empty($erreursformulaire) and $tokencheck) {
        $titulaires = array();
        $remplacants = array();
        $nbtitulaire = 0;
        $nbremplacant = 0;
        $selection = new ArrayObject(array_keys($_POST["selection"]));
        $it = $selection->getIterator();
        while ($it->valid() && $nbtitulaire <= $MAXTITULAIRES && $nbremplacant <=$MAXREMPLACANT) {
            if (array_key_exists($it->current(),$_POST["titulaire"])) {
               if ($_POST["titulaire"][$it->current()] == "titulaire") {
                array_push($titulaires,$it->current());
                $nbtitulaire++;
               }
               else {
                array_push($remplacants,$it->current());
                $nbremplacant++;
               }
            }
            $it->next();
        }
        /*
        echo  "<pre>".print_r($titulaires,TRUE)."</pre>";
        echo  "<pre>".print_r($remplacants,TRUE)."</pre>";
        */

        if ($nbtitulaire != $MAXTITULAIRES) {
            echo "Nombre de titulaires enregistres incorrect";
            exit;
        }

        if ($nbremplacant > $MAXREMPLACANT) {
            echo "Trop de remplacants enregistres";
            exit;
        }


        //Ajout du match
        
        $requeteAMatch = "INSERT INTO matchs (id_match,Domicile_Exterieur,dateheure,id_adversaire) VALUES (DEFAULT,:domext,:dateh,:adv)";
        $req = $linkpdo->prepare($requeteAMatch);
        $req->bindValue(':domext', secure_input($_POST['domext']));
        $req->bindValue(':dateh', $_POST['datetime']);
        $req->bindValue(':adv', secure_input($_POST['Equipe']));

        try {
            $req->execute();
        } catch (PDOException $e) {
            print "Erreur Ajout du match " . $e->getMessage();
            exit;
        }



        //Ajout de la participation des joueurs
        $requeteAJoueur = "INSERT into joue (id_match,id_joueur,Titulaire) VALUES (:id_match,:id_joueur,:tit)";
        $req = $linkpdo->prepare($requeteAJoueur);
        $req->bindValue(':id_match', $linkpdo->lastInsertId());
        $req->bindValue(':tit', 1);

        // Enregistrement des titulaires
        foreach($titulaires as $joueur) {
            $req->bindValue(":id_joueur", $joueur);
            $req->execute();
        }

        $req->bindValue(':tit', 0);
        //Enregistrement des remplacants
        foreach($remplacants as $joueur) {
            $req->bindValue(":id_joueur", $joueur);
            $req->execute();
        }

    }

    elseif (!empty($erreursformulaire)) {
        echo "Erreurs lors du remplissage du formulaire : <br/>";
        foreach ($erreursformulaire as $erreur) {
            echo $erreur . '<br/>';
        }
    } else {
        echo $tokenerror;
    }
}

    /*
    echo  "<pre>"'champs vides'.print_r($champsvide,TRUE)."</pre>";
    */
 


?>


<?php




?>
<a href="<?php echo $backlink;?>"><button type="button" id="button1">Retour</button></a>
