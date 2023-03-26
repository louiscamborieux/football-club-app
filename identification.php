<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
        <meta name="description" content="Connexion à TFC manager">
        <title>Connexion</title>
    
    
    </head>
    <body id="connexion-background">
    <?php



        require_once "../config.php";
        session_start();

        if (isset($_SESSION['login']) && $_SESSION["login"] === true) {
            header("location: accueil.php");
            exit;
        }

        if (isset($_POST['user']) && isset($_POST['pwd'])) {
            if ($_POST['user'] === APP_LOGIN && password_verify(($_POST['pwd'].APP_SALT),APP_HASHED_PASS) ) {
                $_SESSION['login'] = true;
                 //Creation du token de session
                $token = openssl_random_pseudo_bytes(64);
                $token = bin2hex($token);
                $_SESSION['token'] = $token;
                $_SESSION['expiration'] = time() +3600;
                header("location: accueil.php");
            }
            else {
                $_SESSION['login'] = false;
                echo "Identifiant ou mot de passe incorrect";
            }
            //header("location: identification.php");
        }
        


    ?>
        
            
        <div class="conex_box">
            <form method="post" class="form_L">
                <h1>Connexion</h1>
                <div class="container2">
                    <label for ="user" id="utilisateur_text"><b>Nom d'utilisateur</b></label>
                    <input type="text" name="user" required class="container1" id="utilisateur_input">
                    <label for="pwd" id="mdp_text"><b>Mot de passe</b></label>
                    <input type="password" name="pwd" required class= "container1" id="mdp_input">
                </div>

                
    
                <button type="submit" class="container1" id="button1">Se connecter</button>
    
                
            </form>
        </div>


    </body>

</html>