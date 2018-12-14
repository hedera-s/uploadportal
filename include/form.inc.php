<?php
/********************************************************************************************/

			/**
			*
			* Entschärft und bereinigt die Whitespaces
			*
			* @param 	String 	$inputString 	Der zu entscärfende und bereinigente 
			* 
			* @return 	String					Der entschärfte und bereinigte String
			*
			*/

			function cleanString($inputString){
	
				
				// trim() entfernt am Anfang und am Ende eines Strings alle 
				// sog. Whitespaces (Leerzeichen, Tabulatoren, Zeilenumbrüche)
				$inputString= trim($inputString);
				
				// htmlspecialchars() entschärft HTML-Steuerzeichen wie < > & '' ""
				// und ersetzt sie durch &lt;, &gt;, &amp;, &apos; &quot;
				$inputString = htmlspecialchars($inputString);
if(DEBUG_F)		echo "<p class='debugCleanString'><b>Line  " . __LINE__ .  "</b>: Aufruf " . __FUNCTION__ . "($inputString) <i>(" . basename(__FILE__) . ")</i></p>";					
				// bereinigten und entschärften String zurückgeben:
				return $inputString;
				
				
			}
	
/********************************************************************************************/	
/********************************************************************************************/	

			/**
			* Prüft einen String auf Leerstring, Mindest- und maximallänge
			*
			* @param 	String 	$inputString 	Der zu prüfende String
			* @param	[Int	$minLength]		Die erforderliche Mindestlänge
			* @param	[Int	$maxLength]		Die erforderliche Maximallänge
			*
			* @return 	String/NULL				Ein String bei Fehler, ansonsten NULL
			*/
			
			function checkInputString($inputString, $minLength=INPUT_MIN_LENGTH, $maxLength=INPUT_MAX_LENGTH){
if(DEBUG_F) echo "<p class='debugCheckInputString'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "($inputString, [$minLength], [$maxLength]) <i>(" . basename(__FILE__) . ")</i></p>";
				
				$errorMessage = NULL;
				
				//Prüfen auf Leerstring:
				if($inputString === ""){
					$errorMessage = "Dies ist ein Pflichtfeld!";
					
				//Prüfen auf Mindestlänge:
				} elseif(mb_strlen($inputString) < $minLength){
					$errorMessage = "Darf mind. $minLength Zeichen lang sein!";
					
				//Prüfen auf Maximale Länge:
				} elseif(mb_strlen($inputString) > $maxLength){
					$errorMessage = "Darf max. $maxLength Zeichen lang sein!";
				}
								
				return $errorMessage;
				
			}


/********************************************************************************************/	
/********************************************************************************************/	
			
			/**
			* Prüft ob ein übergebener String eine Valide Email-adresse enthält
			*
			* @param 	String 	$inputString 	Der zu prüfende String
			* 
			*
			* @return 	String/NULL				Ein String bei Fehler, ansonsten NULL
			*/

			function checkEmail($inputString){
if(DEBUG_F) 	echo "<p class='debugCheckEmail'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "($inputString) <i>(" . basename(__FILE__) . ")</i></p>";
				
				$errorMessage = NULL;
				
				//Prüfen auf Leerstring:
				if( $inputString && !filter_var($inputString, FILTER_VALIDATE_EMAIL) ){
					$errorMessage = "Dies ist ekeine gültige Email-Adresse";
				}
				
				return $errorMessage;
			
			}

/********************************************************************************************/	
/********************************************************************************************/	
			
			
			/**
			* 
			* Speichert und prüft ein hochgeladenes Bild auf MIME-Type, Datei- und Bildgröße
			* 
			* 
			* @param 	Array 	$uploadedImage		Die Bildinformationen aus $_FILES
			* @param	[ Int	$maxWidth 	]		Die Maximalerlaubte Bildbreite in px
			* @param	[ Int	$maxHeight 	]		Die Maximalerlaubte Bildhöhe in px
			* @param	[ Int	$maxSize 	]		Die Maximalerlaubte Bildgröße in Byte
			* @param	[String	$uploadPath ]		Das speicherverzeichnis auf dem Server
			* @param	[Array	$allowedMimeTypes ]	Whitelist der erlaubten MIME-Types
			*
			*
			* @return 	Array 	{String/NULL		Fehlermeldung im Fehlerfall, ansonsten NULL
			*					String				Der Server-Pfad zum gespeucherten Bild }
			*/

			
			function imageUpload($uploadedImage, 
								 $maxWidth			= IMAGE_MAX_WIDTH, 
								 $maxHeight			= IMAGE_MAX_HEIGHT, 
								 $maxSize			= IMAGE_MAX_SIZE,
								 $uploadPath		= IMAGE_UPLOAD_PATH,
								 $allowedMimeTypes 	= IMAGE_ALLOWED_MIMETYPES
								 
								 ){
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "() <i>(" . basename(__FILE__) . ")</i></p>";		
				/*
				Das Array $_FILES['avatar'] bzw. $uploadedImage enthält:
				Den Dateinamen [name]
				Den generierten (also ungeprüften) MIME-Type [type]
				Den temporären Pfad auf dem Server [tmp_name]
				Die Dateigröße in Bytes [size]
				*/
if(DEBUG_F)		echo "<pre class='debugImageUpload'>";								
if(DEBUG_F)		print_r($uploadedImage);								
if(DEBUG_F)		echo "</pre>";			

				/************* Bildinformationen Sammeln **************/
				
				//Dateinamen auslesen:
				$fileName 		= $uploadedImage['name'];
				
				//ggf. Leerzeichen im Dateinamen durch "_" ersetzen:
				$fileName 		= str_replace(" ", "_", trim($fileName));
				
				//Dateinamen in Kleinbuchstaben umwandeln:
				$fileName 		= strtolower($fileName);
				
				//Dateigröße auslesen:
				$fileSize 		= $uploadedImage['size'];
				
				//Temporärer Pfad auf dem Server auslesen:
				$fileTemp 		= $uploadedImage['tmp_name'];
				
				//zufälligen Dateiname generieren:
				$randomPrefix 	= rand(1,999999) . str_shuffle("abcdefghijklmnopqrstuvwxyz") . time();
				$fileTarget		= $uploadPath . $randomPrefix . $fileName;

if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: fileName: $fileName <i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: fileSize: $fileSize <i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: fileTemp: $fileTemp <i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: fileTarget: $fileTarget <i>(" . basename(__FILE__) . ")</i></p>";
				
				// Genauere Informationen zum Bild auslesen:
				$imageData 		= @getimagesize($fileTemp);
				/*
				Die Funktion getimagesize() liefert bei gültigen Bildern ein Array zurück:
				Die Bildbreite in PX [0]
				Die Bildhöhe in PX [1]
				Einen für die HTML-Ausgabe vorbereiteten String für das IMG-Tag
				(width="480" height="532") [3]
				Die Anzahl der Bits pro Kanal ['bits']
				Die Anzahl der Farbkanäle (somit auch das Farbmodell: RGB=3, CMYK=4) ['channels']
				!!! Den echten(!) MIME-Type ['mime']
				*/
				
if(DEBUG_F)		echo "<pre class='debugImageUpload'>";								
if(DEBUG_F)		print_r($imageData);								
if(DEBUG_F)		echo "</pre>";			
				
				$imageWidth 	= $imageData[0];
				$imageHeight 	= $imageData[1];
				$imageMimeType 	= $imageData['mime'];
				
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: imageWidth: $imageWidth px <i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: imageHeight: $imageHeight px<i>(" . basename(__FILE__) . ")</i></p>";
if(DEBUG_F) 	echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: imageMimeType: $imageMimeType <i>(" . basename(__FILE__) . ")</i></p>";


				/********************* Bild prüfen ********************/
				
				//MIME-Type prüfen
				//Whitelist mit erlaubten Bildtypen
				//$allowedMimeTypes = array("image/jpg", "image/jpeg", "image/gif", "image/png");
				
				if(!in_array($imageMimeType, $allowedMimeTypes)){
					$errorMessage 	= "Dies ist kein gültiger Bildtyp!";
					
				//Maximal erlaubte Bildbreite:
				} elseif($imageWidth > $maxWidth) {
					$errorMessage 	= "Die Bildbreite darf maximal $maxWidth Pixel betragen!";
					
				//Maximal erlaubte Bildhöhe:
				} elseif($imageHeight > $maxHeight) {
					$errorMessage 	= "Die Bildhöhe darf maximal $maxHeight Pixel betragen!";
					
				//Maximal erlaubte Bildsize:	
				}elseif($fileSize > $maxSize){
					$errorMessage 	= "Die Dateigröße darf maximal ". round($maxSize/1024,2) ." KB betragen!";
						
				//Wen es keine Fehler gab:
				}else{
					$errorMessage 	= NULL;
				}
				
				//Abschließende Bildprüfung
				if(!$errorMessage){
if(DEBUG_F) 		echo "<p class='debugImageUpload ok'><b>Line " . __LINE__ . "</b>: Die Bildprüfung ergab keine Fehler <i>(" . basename(__FILE__) . ")</i></p>";	

					// BILD SPEICHERN
					
					// Bild an seinen endgültigen Speicherort verschieben
					if(@move_uploaded_file($fileTemp, $fileTarget)){
						//Erfolgsfall
if(DEBUG_F) 			echo "<p class='debugImageUpload ok'><b>Line " . __LINE__ . "</b>: Das Bild wurde erfolgreich unter '$fileTarget' gespeichert <i>(" . basename(__FILE__) . ")</i></p>";	
						
					}else{
						//Fehlerfall
if(DEBUG_F) 			echo "<p class='debugImageUpload ok'><b>Line " . __LINE__ . "</b>: Fehler beim Speichern Datei auf dem Server <i>(" . basename(__FILE__) . ")</i></p>";	
						$errorMessage = "Fehler beim Speichern der Datei auf dem Server";
						
					} // Bild auf Server speichen Ende
					
				} // Bildprüfungen Ende
				
				/*************** Fehlermeldung und *********************/
				/*************** Bildpfad zurückgeben ******************/
				
				return array("imageError" => $errorMessage, "imagePath" => $fileTarget);
			} 


/********************************************************************************************/	
/********************************************************************************************/	
			

?>