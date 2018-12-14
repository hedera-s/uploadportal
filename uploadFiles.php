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
/**********************************************************************************************/						
					
					/********************************/
					/******** FILE UPLOAD ***********/
					/********************************/


					$loadingFiles = '<table class="uploaded">
								'; 
					if(isset($_FILES['file']['name'][0])){ 

if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>";					
if(DEBUG)	print_r($_FILES);					
if(DEBUG)	echo "</pre>";	

						$pdo = dbConnect();

						foreach($_FILES['file']['name'] as $keys => $values){ 
						$sourcePath = $_FILES['file']['tmp_name'][$keys]; 
						$targetPath = 'uploadedFiles/'. $_SESSION['usr_id']."_". $_SESSION['usr_username'] .'/' . $values; 
						$fileSize	= $_FILES['file']['size'][$keys];
						$fileType	= $_FILES['file']['type'][$keys];
							if(move_uploaded_file($sourcePath, $targetPath)){ 
								$filesArray[] = array("name" => $values, "path" => $targetPath, "type" => $fileType, "size" => formatSizeUnits($fileSize));
							


								/*****************************************/
								/******** FILE IN DB SCHREIBEN ***********/
								/*****************************************/					

								$statement = $pdo->prepare("INSERT INTO files  
															(file_path, file_type, file_size, usr_id, file_name, file_date)
															VALUES
															(?, ?, ?, ?, ?, NOW())
															");
								$statement->execute( array(
													$filesArray[$keys]['path'],
													$filesArray[$keys]['type'],
													$filesArray[$keys]['size'],
													$_SESSION['usr_id'],
													$filesArray[$keys]['name']
													)) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" );
						
								$newFileId = $pdo->lastInsertId();
if(DEBUG)						echo "<p class='debug'>Line <b>" . __LINE__ . "</b>: $newFileId <i>(" . basename(__FILE__) . ")</i></p>";	

								

								// file_id zum gerade hochgeladenen File aus DB auslesen
								
								$statement = $pdo->prepare("SELECT file_id FROM files WHERE file_path = ?");
								$statement->execute(array($filesArray[$keys]['path'])) OR DIE( "<p class='debug'>Line <b>" . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>" ); 
								
								$file_id = $statement->fetch();


								$loadingFiles .= '<tr class="uploaded-file">
														<td>'.$values.'</td>
														<td>'.$filesArray[$keys]['type'].'</td>
														<td>'.$filesArray[$keys]['size'].'</td>
														<td><a href="?action=delete&fileToDelete='.$file_id[0].'" onclick="return confirm('."'Wollen Sie wirklich die Datei löschen?'".')">X</a></td>
													</tr>'; 
							}



						} 
					} 
				

					$loadingFiles.= '</table>'; 


					/********  EMAIL GENERIEREN *********/
								
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
								

								echo "<h3>Gerage hochgeladen:</h3><br>".$loadingFiles;

					

/**********************************************************************************************/

	


 ?>

