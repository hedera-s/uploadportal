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
					
					if(!isset($_SESSION['usr_id'])){
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



/**********************************************************************************************/	
					/**************************************************/
					/************ URL-Parameterverarbeitung ***********/
					/**************************************************/

					if(isset($_GET['action'])){

if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: URL-Paranmeter 'action wurde übergeben'<i>(" . basename(__FILE__) . ")</i></p>";	
						$action = cleanString($_GET['action']);
if(DEBUG)				echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: action: $action'<i>(" . basename(__FILE__) . ")</i></p>";	
						
						/************ LOGOUT ***********/						
						if($action=="logout"){
if(DEBUG)					echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: Logout wird durchgeführt action: $action'<i>(" . basename(__FILE__) . ")</i></p>";	
							
							/*********** Session löschen *************/
							session_destroy();
							
							/*********** Weiterleiten auf Indexseite *************/
							header("Location: index.php");
							exit;
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

								/************************************************/
								/************ File aus DB löschen: **************/
								/************************************************/

								$statement = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
								$statement->execute( array($fileToDelete)
											) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" );
								$deletedFile = $statement->rowCount();

								if($deletedFile){
									// File vom Server löschen:
									@unlink($file_path[0]);
if(DEBUG)							echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: File wurde erfolgreich gelöscht, deletedFile: $deletedFile <i>(" . basename(__FILE__) . ")</i></p>";
									
									$dbMessage = "<h4 class='succ'>Datei wurde erfolgreich gelöscht</h4>";
								}
							

							} 
						}
					}


/**********************************************************************************************/
					/********************************************************/
					/*************** Files One by one senden ****************/
					/********************************************************/

					if(isset($_FILES['uploadFile']['tmp_name'])){
if(DEBUG) 				echo "<p class='debug hint'>Line <b>" . __LINE__ . "</b>: Bildupload one by one aktiv...<i>(" . basename(__FILE__) . ")</i></p>";									$uploadFile = $_FILES['uploadFile'];
if(DEBUG_F)		echo "<pre class='debugImageUpload'>";								
if(DEBUG_F)		print_r($uploadFile);								
if(DEBUG_F)		echo "</pre>";			
						
						//Dateinamen auslesen:
						$fileName 		= $uploadFile['name'];
						
						//ggf. Leerzeichen im Dateinamen durch "_" ersetzen:
						$fileName 		= str_replace(" ", "_", trim($fileName));
						
						//Dateinamen in Kleinbuchstaben umwandeln:
						$fileName 		= strtolower($fileName);
						
						//Dateigröße auslesen:
						$fileSize 		= formatSizeUnits($uploadFile['size']);
						$fileType 		= $uploadFile['type'];
						
						//Temporärer Pfad auf dem Server auslesen:
						$fileTemp 		= $uploadFile['tmp_name'];
						$fileTarget		= 'uploadedFiles/'. $_SESSION['usr_id']."_". $_SESSION['usr_username'] .'/' . $fileName;
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: fileName: $fileName <i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: fileSize: $fileSize <i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: fileTemp: $fileTemp <i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: fileTarget: $fileTarget <i>(" . basename(__FILE__) . ")</i></p>";

						if(!@move_uploaded_file($fileTemp, $fileTarget)){
if(DEBUG) 					echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Es ist ein fehler <i>(" . basename(__FILE__) . ")</i></p>";							
							$dbMessage = "<h4 class='error'>Fehler beim Speichern der Datei auf dem Server</h4>";
						}else{
if(DEBUG) 					echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Das Bild wurde erfolgreich auf dem Server geladen <i>(" . basename(__FILE__) . ")</i></p>";				

							// Prüfen, ob die Datei schon existiert in DB:
							
							$statement = $pdo->prepare("SELECT COUNT(file_path) FROM files
														WHERE file_path = ?");
													
							$statement->execute( array($fileTarget)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" );
							
							$fileExists = $statement->fetchColumn();
							if($fileExists){
								// Fehlerfall
if(DEBUG)						echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: File isr bereits hochgeladen <i>(" . basename(__FILE__) . ")</i></p>";
								$dbMessage = "<h4 class='error'>Diese Datei wurde bereits hochgeladen</h4>";
							}else{
								// Erfolgsfall 	
if(DEBUG)						echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: File ist noch nicht hochgeladen <i>(" . basename(__FILE__) . ")</i></p>";									$statement = $pdo->prepare("INSERT INTO files  
															(file_path, file_type, file_size, usr_id, file_name, file_date)
															VALUES
															(?, ?, ?, ?, ?, NOW())
															");
								
							$statement->execute( array(
													$fileTarget,
													$fileType,
													$fileSize,
													$_SESSION['usr_id'],
													$fileName
													)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>"); 
							
							$lastInsertId = $pdo->lastInsertId();
if(DEBUG) 						echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: lastInsertId: $lastInsertId <i>(" . basename(__FILE__) . ")</i></p>";											if(!$lastInsertId){
									// Fehler:
									$dbMessage = "<h4 class='error'>Fehler beim Speichern der Datei auf dem Server. Vesuchen Sie es später.</h4>";
								}else{
									// Erfolg
									$dbMessage = "<h4 class='succ'>Datei wurde erfolgreich hochgelagen</h4>";

								
								/************************************/
								/********  EMAIL GENERIEREN *********/
								/************************************/
								
								// PHP-Funktion zum Erzeugen und Versenden einer Email:
								// mail(String Empfängeradresse, String Betreff, String Inhalt, String Header)
								
								
								$to = "irene.davydova@gmail.com";
								
								$subject = "Neue Daten wurden geladen";
								
								$header = "FROM: Upload-Portal<donotreply@uploadportal.de>\r\n";
								//optional: Adresse für Antworten-Button
								//$header .= "Reply-To: cutomerservice@meineseite.de\r\n";
								//optional: Email in HTML-Format
								$header .= "MIME-Version: 1.0\r\n";
								$header .= "Content-Type: text/html; charset=utf-8\r\n";
								$header .= "X-Mailer: PHP " . phpversion();
								
								$content = "<h4>Hallo Admin.</h4>
											<p>Der Benutzer $_SESSION[usr_firstname] $_SESSION[usr_lastname] hat Dateien auf Server hochgeladen.</p>
											<p>Viele Grüße<br>
											Ihr www.uploadportal.de</p>";
											
								
								
								/******** BESTÄTIGUNGS EMAIL VERSENDEN *********/
								
								/* 
								Damit die folgende Fehlerabfrage funktioniert, muss in XAMPP
								vorher der Mercury Email-Server konfiguriert und gestartet werden.
								*/
								// Ein @ vor einem Funktionsaufruf unterdrückt die von dieser 
								// Funktion geworfene Fehlermeldung im Frontend

								mail($to, $subject, $content, $header);


								}





							}





						}				


					}


/**********************************************************************************************/
					/********************************************************/
					/********* Hochgeladene Dateien aus DB auslesen *********/
					/********************************************************/

					$statement = $pdo->prepare("SELECT * FROM files WHERE usr_id = ?");
					$statement->execute(array($_SESSION['usr_id'])) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" ); 
					
					$filesArray = $statement->fetchAll(PDO::FETCH_ASSOC);



/**********************************************************************************************/	
?>

<!doctype html>
<html>

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Upload-Portal - Upload</title>
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<link rel="stylesheet" type="text/css" href="css/debug.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
 		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
 		<script>
 		 $(document).ready(function() {
	 		 $('.file_drag_div').on('dragover',function(){
		 		 $(this).addClass('file_drag_div_over');
		 		 return false;
	 		 });

	 		 $('.file_drag_div').on('dragleave',function(){
		 		 $(this).removeClass('file_drag_div_over');
		 		 return false;
	 		 });

	 		 $('.file_drag_div').on('drop',function(e){
		 		e.preventDefault();
		 		$(this).removeClass('file_drag_div_over');
		 		var formdata = new FormData();
		 		var multiple_files = e.originalEvent.dataTransfer.files;
		 		for(var i=0;i<multiple_files.length; i++){
		 			formdata.append('file[]',multiple_files[i]);
		 		}

		 		$.ajax({
			 		url: 'uploadFiles.php',
			 		method: 'post',
			 		data: formdata,
			 		contentType: false,
			 		cache: false,
			 		processData: false,
			 		success:function(result){
			 		$('#result_images').html(result);
			 		}
		 		});
	 		 });
 		 });
 		</script>
	</head>

	<body>
		<header class="loginHeader">
			<p>Hallo <?php echo $_SESSION['usr_firstname']. " ". $_SESSION['usr_lastname']?> | <a href="?action=logout">Logout</a> </p>
		
			<hr>
		</header>
		<div class="upload-wrapper">
			<div class="jumbotron text-center">
				<h1>Dateien hochladen:</h1>
				
			</div>
			 <?=$dbMessage?>
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="file_drag_div">
							Ziehen Sie Ihre Dateien hier
						</div>
					</div>
				</div>
				<form action="" method="POST" enctype="multipart/form-data" class="onebyone">
					<span>oder laden Sie die Dateien Stück für Stück:</span>
					<input type="file" name="uploadFile">
					<input type="submit">

				</form>
				<div class="row">
					<div class="col-md-12" id="result_images"></div>
				</div>
			</div>
			
			<h3>Ihre Dateien</h3>
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
	</body>
</html>
