<?php
/*******************************************************************************************/	
			/********************************/
			/******** CONFIGURATION *********/
			/********************************/
			
			
			
			/********* INCLUDES *************/
			
			require_once("include/config.inc.php");
			require_once("include/db.inc.php");
			require_once("include/form.inc.php");
			require_once("include/functions.inc.php");
			
					
/*******************************************************************************************/
			/*******************************************************/
			/************* Variablen initializieren ****************/
			/*******************************************************/
			
			$urlMessage 	= NULL;
			$loginMessage 	= NULL;

/*******************************************************************************************/
				
			/*******************************************************/
			/************* FORMULARVERARBEITUNG LOGIN **************/
			/*******************************************************/

			if(isset($_POST['formsentLogin'])){
if(DEBUG)		echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Formular wurde abgeschickt<i>(" . basename(__FILE__) . ")</i></p>";
						
				$accountname = cleanString($_POST['accountname']);
				$password = cleanString($_POST['password']);
if(DEBUG)		echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: accountname: $accountname'<i>(" . basename(__FILE__) . ")</i></p>";	
if(DEBUG)		echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: password: $password'<i>(" . basename(__FILE__) . ")</i></p>";								
				// Werte Validierung
				$errorAccountname = checkInputString($accountname, 4, 20);
				$errorPasswort = checkInputString($password, 4);	

				//Abschließende Formularprüfung
				if($errorAccountname || $errorPasswort){
					// Fehlerfall:
					echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Logindaten sind ungültig<i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG)			$loginMessage = "<p class='error'>Logindaten sind ungültig</p>";
				}else{
					// Erfolgsfall:
if(DEBUG) 			echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Formular ist korrekt ausgefüllt. Daten werden nun verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>";	

					/******************* DB-OPERATION *********************/
					//1. Verbindung herstellen
					
					$pdo = dbConnect();
					
					/*********** DATENSATZ ZUM LOGINNAMEN AUSLESEN *********/
					
					//2. SQL-Statement vorbereiten
					$statement = $pdo->prepare("SELECT * FROM users 
												WHERE usr_username = ?
												");
												
					//3. SQL-Statement ausführen
					$statement->execute( array($accountname) ) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>"); 
					
					//4. Datenweiterverarbeiten
					$row = $statement->fetch();
					
					// Prüfen, ob ein Datensatz geliefert wurde
					// Wenn Datensatz geliefert wurde, muss der Accountname stimmen
					if( !$row ){
						// Fehlerfall:
if(DEBUG) 				echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: FEHLER: Accountname $accountname existiert nicht in der DB! <i>(" . basename(__FILE__) . ")</i></p>";
						$loginMessage = "<p class='error'>Logindaten sind ungültig!</p>";

					}else{
						// Erfolgsfall:
if(DEBUG) 				echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Accountname $accountname wurde in der DB gefunden. <i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG) 				echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: $password , $row[usr_password]  . <i>(" . basename(__FILE__) . ")</i></p>";
						
						/*************** Passwort prüfen **************/	
						if(!password_verify($password, $row['usr_password'])){
							// Fehlerfall:
if(DEBUG) 					echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: FEHLER: Passwort stimmt nicht! <i>(" . basename(__FILE__) . ")</i></p>";
							$loginMessage = "<p class='error'>Logindaten sind ungültig!</p>";

						}else{
							// Erfolgsfall:
if(DEBUG) 					echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Passwort stimmt mit Passwort aus DB überein <i>(" . basename(__FILE__) . ")</i></p>";
							
							/***************Session starten und Daten in Session schreiben********/
					
							session_name("uploadportal");
							session_start();
							
							$_SESSION['usr_id'] = $row['usr_id'];
							$_SESSION['usr_firstname'] = $row['usr_firstname'];
							$_SESSION['usr_lastname'] = $row['usr_lastname'];
							$_SESSION['usr_username'] = $row['usr_username'];
							
if(DEBUG) echo "<pre class='debug'>";
if(DEBUG) print_r($_SESSION);
if(DEBUG) echo "</pre>";
							
							/************** Accountstatus Prüfen *************/
									
							$usr_role = $row['usr_role'];
if(DEBUG)					echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: usr_role: $usr_role<i>(" . basename(__FILE__) . ")</i></p>";
							if($usr_role == 'admin'){
if(DEBUG)						echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Weiterleitung auf Adminpanel...(" . basename(__FILE__) . ")</i></p>";
								header("Location: admin.php");
								exit;							

							}elseif($usr_role == 'user'){
if(DEBUG)						echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Weiterleitung auf Userpanel...(" . basename(__FILE__) . ")</i></p>";
								header("Location: upload.php");
								exit;	
							}
									
							



						}								

					}


							
				}



			}
/*******************************************************************************************/
?>

<!doctype html>
<html>

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Upload-Portal - Login</title>
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<link rel="stylesheet" type="text/css" href="css/debug.css">
	</head>

	<body>
		
		<header>

			<div class="login-wrapper">
				<h1>Upload-Portal</h1>
				<h3>Bitte, melden Sie sich an:</h3>
				<form action="<?=$_SERVER['SCRIPT_NAME']?>" method="POST">
					<input type="hidden" name="formsentLogin">	
					<?=$loginMessage ?>
					<span>Benutzername:</span><br>
					<input  type="text" name="accountname" placeholder="Accountname"><br>
					<span>Passwort:</span><br>
					<input  type="password" name="password" placeholder="Passwort"><br><br>
					<input  type="submit" value="Anmelden">
					
						
				</form>
			</div>
			
		</header>
		
	
			


</body>

</html>