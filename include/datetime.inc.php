<?php
/****************************************************************************/
			/**
			*
			* Konvertiert iso-Datum-Zeit in EU-Standart Datum/ Zeit
			*
			* @param 	String 	$datetime 	Das zu konvertierende Datum/Zeit   in Format YYYY-MM-DD hh:mm:ss 
			* 
			* @return 	Array				Array mit Zwei werte: Das EU-Datum und die EU-Zeit
			*
			*/

	
			function isoToEuDateTime($dateTime){
if(DEBUG_F) 	echo "<p class='debug'><b>Line " . __LINE__ . ":</b> Aufruf " . __FUNCTION__ . "($dateTime) <i>(" . basename(__FILE__) . ")</i></p>";				
			
				//mögliche Übernahmewerte
				//2018-05-17 14:17:48 
				//2018-05-17 
				
				//gewünschte Ausgabewerte
				//17.05.2018 // 14:17
				//17.05.2018
				
				// Datum ausschneiden und umformatieren
				$year 	= substr($dateTime, 0, 4);
				$month 	= substr($dateTime, 5, 2);
				$day 	= substr($dateTime, 8, 2);
				
				$euDate = "$day.$month.$year";
				
				//Prüfen, ob $dateTime eine Uhrzein enthält
				if(strlen($dateTime) > 10){
					//Uhrzein ausschneiden (ohne Sekunden)
					$time = substr($dateTime, 11, 5);
				} else {
					$time = NULL;
				}
				
				return array("date"=>$euDate, "time"=>$time);
			
			}
		
/****************************************************************************/

?>