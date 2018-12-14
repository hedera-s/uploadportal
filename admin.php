<?php
/**********************************************************************************************/	
					/************************************************/
					/************* Session fortführen ***************/
					/************************************************/
					
					session_name("uploadportal");
					session_start();
/**********************************************************************************************/	
					/****************************************************/
					/*************** Seitenzugriffschutz ****************/
					/****************************************************/
					
					if(!isset($_SESSION['usr_id']) OR $_SESSION['usr_id']!== "1"){
						//Session löschen
						session_destroy();
						//Umleiten auf index.php
						header("Location: index.php");
						exit;
						
					}
/**********************************************************************************************/	
					/********************************/
					/******** CONFIGURATION *********/
					/********************************/
					
					
					
					/********* INCLUDES *************/
					
					require_once("include/config.inc.php");
					require_once("include/db.inc.php");
					require_once("include/form.inc.php");
					require_once("include/functions.inc.php");
					
					/************DB-Verbindung*************/
					//1DB: Verbindung
					$pdo = dbConnect();
					
					
/**********************************************************************************************/	
					/*******************************************/
					/******** Variablen Initializieren *********/
					/*******************************************/
					
					$errorUsername	= NULL;
					$errorPassword 	= NULL;
					$errorFirstname = NULL;
					$errorLastname 	= NULL;
					$errorEmail 	= NULL;
					$errorCompany 	= NULL;
					
					$username 		= NULL;
					$password 		= NULL;
					$firstname 		= NULL;
					$lastname 		= NULL;
					$email 			= NULL;
					$company 		= NULL;
					

					
					$passwordChange = false;
					
					$dbMessage 		= NULL;
					$dbMessageEdit 	= NULL;
					



/**********************************************************************************************/


					/**************************************************/
					/************ URL-Parameterverarbeitung ***********/
					/**************************************************/

					if(isset($_GET['action'])){
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: URL-Paranmeter 'action wurde übergeben'<i>(" . basename(__FILE__) . ")</i></p>";	
						$action = cleanString($_GET['action']);
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: action: $action'<i>(" . basename(__FILE__) . ")</i></p>";	
						
						/*******************************/
						/************ LOGOUT ***********/	
						/*******************************/

						if($action=="logout"){
if(DEBUG)					echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Logout wird durchgeführt action: $action'<i>(" . basename(__FILE__) . ")</i></p>";	
							
							/*********** Session löschen *************/
							session_destroy();
							
							/*********** Weiterleiten auf Indexseite *************/
							header("Location: index.php");
							exit;						
						}elseif($action=="edit"){
							if(isset($_GET['userToEdit'])){
								$userToEdit = cleanString($_GET['userToEdit']);
if(DEBUG)						echo "<p class='debug hint'>Line <b>" . __LINE__ . "</b>: URL-Parameter 'userToEdit' wurde übergeben ($userToEdit) <i>(" . basename(__FILE__) . ")</i></p>";
								
								// Daten zum redaktierenden User aus DB auslesen
								$statement = $pdo->prepare("SELECT * FROM users 
													WHERE usr_id = ?");
								$statement->execute(array($userToEdit)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" ); 
								
								// Daten zum Redaktierenden User in Formular schreiben
						
								while($row = $statement->fetch(PDO::FETCH_ASSOC)){
									$usernameEdit 		= $row['usr_username'];
									$firstnameEdit 		= $row['usr_firstname'];
									$lastnameEdit 		= $row['usr_lastname'];
									$emailEdit 			= $row['usr_email'];
									$companyEdit 		= $row['usr_company'];
											
								}



/**********************************************************************************************/

								/**************************************************/
								/***************** USER REDAKTIEREN ***************/
								/**************************************************/

								if(isset($_POST['formsentEditUser'])){
if(DEBUG) 							echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Formular 'Edit User' wurde abgeschickt <i>(" . basename(__FILE__) . ")</i></p>";
									$passwordEdit 	= cleanString($_POST['password']);
									$firstnameEdit 	= cleanString($_POST['firstname']);
									$lastnameEdit 	= cleanString($_POST['lastname']);
									$emailEdit 		= cleanString($_POST['email']);
									$companyEdit 	= cleanString($_POST['company']);	

									if($passwordEdit){
if(DEBUG) 								echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Passwortänderung aktiv<i>(" . basename(__FILE__) . ")</i></p>";			
										//password auf Mindestlänge prüfen
										$errorPassword = checkInputString($passwordEdit, 4);
										if(!$errorPassword){
if(DEBUG) 									echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Das neue Passwort erfüllt die Voarussetzungen<i>(" . basename(__FILE__) . ")</i></p>";											
											$passwordHash = password_hash($passwordEdit, PASSWORD_DEFAULT);
if(DEBUG) 									echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: passwordHash: $passwordHash <i>(" . basename(__FILE__) . ")</i></p>";										
										
										}
									}

									$errorFirstname = checkInputString($firstnameEdit);
									$errorLastname 	= checkInputString($lastnameEdit);
									$errorEmail 	= checkEmail($emailEdit);

									if($errorFirstname || $errorLastname || $errorEmail || $errorPassword){
if(DEBUG) 								echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Das Formular enthält noch Fehler <i>(" . basename(__FILE__) . ")</i></p>";							
									}else{
if(DEBUG) 								echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Das Formular 'Edit User' ist fehletfrei <i>(" . basename(__FILE__) . ")</i></p>";									
										// Neue Daten in DB Speichern
										$sql = "UPDATE users
											SET 
											usr_firstname = :ph_usr_firstname, 
											usr_lastname = :ph_usr_lastname, 
											usr_email = :ph_usr_email, 
											usr_company = :ph_usr_company
											";
										if($passwordEdit){
											$sql .= ", 
											usr_password = :ph_usr_password";
										}

										$sql .= " WHERE usr_id 	= :ph_usr_id";

										$params = array(
											"ph_usr_firstname" 	=> $firstnameEdit,
											"ph_usr_lastname" 	=> $lastnameEdit,
											"ph_usr_email" 		=> $emailEdit,
											"ph_usr_company" 	=> $companyEdit,	
											"ph_usr_id"			=> $userToEdit
										);

										if($passwordEdit){
											$params['ph_usr_password'] = $passwordHash;
										}
if(DEBUG)	echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: $sql <i>(" . basename(__FILE__) . ")</i></p>";	
if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>";					
if(DEBUG)	print_r($params);					
if(DEBUG)	echo "</pre>";

										$statement = $pdo->prepare($sql);
										$statement->execute($params) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>"); 
										$affectedRows = $statement->rowCount();
										$_GET = [];
										if($affectedRows){
if(DEBUG) 									echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: affectedRows: $affectedRows <i>(" . basename(__FILE__) . ")</i></p>";
											$dbMessageEdit = "<h4>Neue Daten zum User wurden erfolgreich gespeichert</h4>";
										}else{
											$dbMessageEdit = "<h4>Es wurde nichts geändert</h4>";
										}

									
			
									} 		

								} // User in DB redaktieren - Ende

							} // User To Edit - Ende
/**********************************************************************************************/

					/********************************************************/
					/**************** Benutzer aus DB löschen  **************/
					/********************************************************/

						}elseif($action == "delete"){
							if(isset($_GET['userToDelete'])){

								$userToDelete = cleanString($_GET['userToDelete']);
if(DEBUG)						echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: URL-Parameter 'userToDelete' wurde übergeben ($userToDelete) <i>(" . basename(__FILE__) . ")</i></p>";
								// Daten zum User auslesen, damit das Ordner mit files vom Server gelöscht wird 
								$statement = $pdo->prepare("SELECT usr_username FROM users WHERE usr_id = ?");
								$statement->execute(array($userToDelete)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" ); 
								$usr_username = $statement->fetch();


								// User aus DB löschen:
								$statement = $pdo->prepare("DELETE FROM users WHERE usr_id = ?");
								$statement->execute( array($userToDelete)
											) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" );
								$deletedUser = $statement->rowCount();

								if($deletedUser){
if(DEBUG)							echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: User wurde erfolgreich gelöscht, deletedUser: $deletedUser <i>(" . basename(__FILE__) . ")</i></p>";
									// Files aus DB löschen:
									$statement = $pdo->prepare("DELETE FROM files WHERE usr_id = ?");
									$statement->execute( array($userToDelete)
												) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" );
									$deletedFiles = $statement->rowCount();
if(DEBUG)							echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Files wurden erfolgreich aus DB gelöscht, deletedFiles: $deletedFiles <i>(" . basename(__FILE__) . ")</i></p>";
									$dbMessageEdit = "<h4>User wurde erfolgreich gelöscht</h4>";

									// Ordner vom Server löschen:
									rmrf('uploadedFiles/'. $userToDelete."_". $usr_username[0]);
								}
							

							} // usertodelete - Ende

						}
					} // URL-Parameterverarbeitung - Ende

										
/**********************************************************************************************/
					/*************************************/
					/******** Neuer User anlegen *********/
					/*************************************/

					if(isset($_POST['formsentNewUser'])){
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Formular wurde abgeschickt<i>(" . basename(__FILE__) . ")</i></p>";		
						

						$username 	= cleanString($_POST['username']);
						$username 	= str_replace(" ", "_", $username);
						$password 	= cleanString($_POST['password']);
						$firstname 	= cleanString($_POST['firstname']);
						$lastname 	= cleanString($_POST['lastname']);
						$email 		= cleanString($_POST['email']);
						$company 	= cleanString($_POST['company']);

if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: username: $username<i>(" . basename(__FILE__) . ")</i></p>";							
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: password: $password<i>(" . basename(__FILE__) . ")</i></p>";							
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: firstname: $firstname<i>(" . basename(__FILE__) . ")</i></p>";							
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: lastname: $lastname<i>(" . basename(__FILE__) . ")</i></p>";							
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: email: $email<i>(" . basename(__FILE__) . ")</i></p>";							
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: company: $company<i>(" . basename(__FILE__) . ")</i></p>";	
					
						//  Werte validieren:
						$errorUsername 	= checkInputString($username, 5, 20);
						$errorPassword 	= checkInputString($password, 4);
						$errorFirstname = checkInputString($firstname);
						$errorLastname 	= checkInputString($lastname);
						$errorEmail 	= checkEmail($email);

						//Passwort verschlüsseln
						if(!$errorPassword){
if(DEBUG)					echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Der Passwort entspricht der Anforderungen <i>(" . basename(__FILE__) . ")</i></p>";	
							$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if(DEBUG)					echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: \$passwordHash: $passwordHash <i>(" . basename(__FILE__) . ")</i></p>";											
						}

						if($errorUsername||$errorPassword||$errorFirstname||$errorLastname||$errorEmail){
							// Fehlerfall
if(DEBUG)					echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Das Formular enthält noch Fehler <i>(" . basename(__FILE__) . ")</i></p>";
							
						}else{
							// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Das Formular ist Fehlerfrei und wird weiterverarbeitet <i>(" . basename(__FILE__) . ")</i></p>";		
							/************************** DB OPERATIONEN ************************/
							/*********** Prüfen, ob Username bereits registriert wurde ********/

							$statement = $pdo->prepare("SELECT COUNT(usr_username) FROM users
														WHERE usr_username = ?");
													
							$statement->execute( array($username)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" );
								
							$usernameExists = $statement->fetchColumn();
if(DEBUG)					echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: \$usernameExists: $usernameExists <i>(" . basename(__FILE__) . ")</i></p>";
							
							if($usernameExists){
								// Fehlerfall:
if(DEBUG)						echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Username ist bereits registriert <i>(" . basename(__FILE__) . ")</i></p>";
								$errorUsername = "Dieser Benutzername ist bereits registriert";

							}else{
								// Erfolgsfall:
if(DEBUG)						echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Benutzername ist noch nicht registriert <i>(" . basename(__FILE__) . ")</i></p>";							
								/************* USER IN DB SPEICHERN************/	

								


								$statement = $pdo->prepare("INSERT INTO users
															(usr_username, usr_password, usr_firstname, usr_lastname, usr_email, usr_company)
															VALUES
															(?,?,?,?,?,?)
															");
								$statement->execute( array(
													$username,
													$passwordHash,
													$firstname,
													$lastname,
													$email,
													$company
													)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" );
						
								$newUserId = $pdo->lastInsertId();
if(DEBUG)						echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: \$newUserId: $newUserId <i>(" . basename(__FILE__) . ")</i></p>";
								if(!$newUserId){
									// Fehlerfall:
									$dbMessage = "<h4 class='error'>Es ist ein Fehler aufgetreten, versuchen Sie es nochmal</h4>";
								}else{
									// Erfolgsfall:
									// Neuer Ordner für User anlegen:
									if(!mkdir("uploadedFiles/".$newUserId."_".$username, 0700)){
										// Fehlerfall:
if(DEBUG)								echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Ordner kann nicht erzeugt werden <i>(" . basename(__FILE__) . ")</i></p>";									  $dbMessage = "<h4 class='error'>Es ist ein Fehler aufgetreten beim Anlegen des Ordners, legen Sie den Ordner selbst an.</h4>";
									}else{
										// Erfolgsfall:
										$dbMessage = "<h4 class='success'>Neuer Benutzer wurde erfolgreich gespeichert</h4>";

											//Formularfelder leeren
											$username 		= NULL;
											$password 		= NULL;
											$firstname 		= NULL;
											$lastname 		= NULL;
											$email 			= NULL;
											$company 		= NULL;
												
									}
									
													
								}								


							}


						}


					}


/**********************************************************************************************/

					/**************************************************/
					/*************** Users aus DB auslesen ************/
					/**************************************************/

					$statement = $pdo->prepare("SELECT * FROM users WHERE usr_role != ?");
				
					// 3. DB: SQL-Statement ausführen und Platzhalter füllen
					$statement->execute(array("admin")) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" ); 
					// 4 DB:  Daten weiterverarbeiten
					// fetchAll liefert zweidimensionales Array zurück, 
					// das ALLE Datensätze beinhaltet
					
					$usersArray = $statement->fetchAll(PDO::FETCH_ASSOC);

					/**************************************************/
					/************* Dateien aus DB auslesen ************/
					/**************************************************/

					foreach($usersArray AS $user){
						$statement = $pdo->prepare("SELECT COUNT(*) FROM files WHERE usr_id = ?");
				
						$statement->execute(array($user['usr_id'])) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" ); 
						$numberOfFiles["$user[usr_id]"] = $statement->fetchColumn();
					}

if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>";					
if(DEBUG)	print_r($numberOfFiles);					
if(DEBUG)	echo "</pre>";					
					if(isset($_GET['action'])){
						$action = cleanString($_GET['action']);
						if($action == "showFiles"){
							if(isset($_GET['filesToShow'])){
								$filesToShow = cleanString($_GET['filesToShow']);
	if(DEBUG)						echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: URL-Parameter 'filesToShow' wurde übergeben ($filesToShow) <i>(" . basename(__FILE__) . ")</i></p>";
								
									/********************************************************/
									/********* Hochgeladene Dateien aus DB auslesen *********/
									/********************************************************/
									
									$statement = $pdo->prepare("SELECT * FROM files WHERE usr_id = ?");
									$statement->execute(array($filesToShow)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" ); 
									
									$filesArray = $statement->fetchAll(PDO::FETCH_ASSOC);
if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> FilesArray <i>(" . basename(__FILE__) . ")</i>:<br>";					
if(DEBUG)	print_r($filesArray);					
if(DEBUG)	echo "</pre>";								


							}
						}
						if($action == "delete"){
							if(isset($_GET['fileToDelete'])){

								$fileToDelete = cleanString($_GET['fileToDelete']);
if(DEBUG)						echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: URL-Parameter 'fileToDelete' wurde übergeben ($fileToDelete) <i>(" . basename(__FILE__) . ")</i></p>";
								// Daten zum File auslesen, damit der File vom Server gelöscht wird 
								$statement = $pdo->prepare("SELECT file_path FROM files WHERE file_id = ?");
								$statement->execute(array($fileToDelete)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" ); 
								$file_path = $statement->fetch();
								if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>";					
if(DEBUG)	print_r($file_path);					
if(DEBUG)	echo "</pre>";


								// File aus DB löschen:
								$statement = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
								$statement->execute( array($fileToDelete)
											) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" );
								$deletedFile = $statement->rowCount();

								if($deletedFile){
									// File vom Server löschen:
									@unlink($file_path[0]);
if(DEBUG)							echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: File wurde erfolgreich gelöscht, deletedFile: $deletedFile <i>(" . basename(__FILE__) . ")</i></p>";
									header("Location: admin.php");
									$dbMessage = "Datei wurde erfolgreich gelöscht";
								}
							

							} 
						}
					}



/**********************************************************************************************/

?>

<!doctype html>
<html>

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Upload-Portal - Admin - Benutzerverwaltung</title>
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<link rel="stylesheet" type="text/css" href="css/debug.css">
	</head>

			<br>
		<header class="loginHeader">
			<p>Hallo <?php echo $_SESSION['usr_firstname']. " ". $_SESSION['usr_lastname']?> | <a href="?action=logout">Logout</a> </p>
			<hr>
		</header>
		<h1>Uploadportal - Benutzerverwaltung</h1>
		<h3 class="info">Neuen Benutzer anlegen</h3>
		
		<?=$dbMessage?>
		<div class="form-wrapper">
			<form action="<?=$_SERVER['SCRIPT_NAME']?>" method="POST">
				<input type="hidden" name="formsentNewUser">
				<span class="error"><?php echo $errorUsername ?></span><br>
				<input type="text" name="username" placeholder="Benutzername" value="<?=$username ?>"><span class="marker">*</span><br>
				<span class="error"><?php echo $errorPassword ?></span><br>
				<input type="text" name="password" placeholder="Passwort" value="<?=$password ?>"><span class="marker">*</span><br>
				<span class="error"><?php echo $errorFirstname ?></span><br>
				<input type="text" name="firstname" placeholder="Vorname" value="<?=$firstname ?>"><span class="marker">*</span><br>
				<span class="error"><?php echo $errorLastname ?></span><br>
				<input type="text" name="lastname" placeholder="Nachname" value="<?=$lastname ?>"><span class="marker">*</span><br>
				<span class="error"><?php echo $errorEmail ?></span><br>
				<input type="text" name="email" placeholder="Email-Adresse" value="<?=$email ?>"><br>
				<span class="error"><?php echo $errorCompany ?></span><br>
				<input type="text" name="company" placeholder="Firma" value="<?=$company ?>"><br><br>
				<input type="submit" value="Speichern">
			</form>
		</div>

		<div class="table-wrapper">
			<table class="users-table">
				<tr>
					<th>ID</th>
					<th>Benutzername</th>
					<th>Vorname</th>
					<th>Nachname</th>
					<th>Email</th>
					<th>Firma</th>
					<th>Dateien</th>
					<th>Aktion</th>
				</tr>

				<?php foreach ($usersArray AS $user): ?>
					<tr>
						<td><?=$user['usr_id'] ?></td>
						<td><?=$user['usr_username'] ?></td>
						<td><?=$user['usr_firstname'] ?></td>
						<td><?=$user['usr_lastname'] ?></td>
						<td><?=$user['usr_email'] ?></td>
						<td><?=$user['usr_company'] ?></td>
						<td><a href="?action=showFiles&filesToShow=<?=$user['usr_id']?>"><?=$numberOfFiles[$user['usr_id']]?></a></td>
						<td><a href="?action=delete&userToDelete=<?=$user['usr_id']?>" class="user-del" onclick="return confirm('Wollen Sie wirklich den Benutzer und seine Files löschen?')">X</a> <a href="?action=edit&userToEdit=<?=$user['usr_id']?>" class="user-edit" >Edit</a></td>
					</tr>
				<?php endforeach ?>
			</table>
			<?php if(isset($_GET['filesToShow'])): ?>
				<div class="files-popup">

					<p class="close"><a href="<?=$_SERVER['SCRIPT_NAME']?>">X</a></p>
					<table class="uploaded">
					<?php foreach ($filesArray AS $key => $file): ?>
						<tr class="uploaded-file">
							<td class="file-name"><div><?=$file['file_name']?></div></td>
							<td><?=$file['file_type']?></td>
							<td><?=$file['file_size']?></td>
							<td><?=$file['file_date']?></td>
							<td><a href="?action=delete&fileToDelete=<?=$file['file_id']?>" onclick="return confirm('Wollen Sie wirklich die Datei löschen?')">X</a></td>
						</tr>
					<?php endforeach ?>
				
					</table>
				</div>
			<?php endif ?>
			<?php if(isset($_GET['userToEdit'])): ?>
				<div class="edit-popup">
					<p class="close"><a href="<?=$_SERVER['SCRIPT_NAME']?>" >X</a></p>
					<h3 class="info">Benutzer redaktieren</h3>
					<form action="" method="POST">
						<input type="hidden" name="formsentEditUser">
						<input type="text" name="username" placeholder="Benutzername" value="<?=$usernameEdit ?>" disabled><span class="marker">*</span><br>
						<span class="error"><?php echo $errorPassword ?></span><br>
						<input type="text" name="password" placeholder="Neues Passwort" value=""><span class="marker">*</span><br>
						<span class="error"><?php echo $errorFirstname ?></span><br>
						<input type="text" name="firstname" placeholder="Vorname" value="<?=$firstnameEdit ?>"><span class="marker">*</span><br>
						<span class="error"><?php echo $errorLastname ?></span><br>
						<input type="text" name="lastname" placeholder="Nachname" value="<?=$lastnameEdit ?>"><span class="marker">*</span><br>
						<span class="error"><?php echo $errorEmail ?></span><br>
						<input type="text" name="email" placeholder="Email-Adresse" value="<?=$emailEdit ?>"><br>
						<span class="error"><?php echo $errorCompany ?></span><br>
						<input type="text" name="company" placeholder="Firma" value="<?=$companyEdit ?>"><br><br>
						<input type="submit" value="Speichern">
					</form>

				</div>
			<?php endif?>

		</div>
</html>
