<?php
/////////////////////////////////////
//Réorganiser la fonction affichage//
/////////////////////////////////////
//////////////////////
//Classes & Methodes//
//////////////////////

//adaptation pour ce local
date_default_timezone_set('Europe/Paris');

//Style orienté objet when extending mysqli class
class foo_mysqli extends mysqli {
    public function __construct($host, $user, $pass, $db) {
        parent::__construct($host, $user, $pass, $db);

        if (mysqli_connect_error()) {
            die('Erreur de connexion (' . mysqli_connect_errno() . ') '
                    . mysqli_connect_error());
        }
    }
}

//$db = new foo_mysqli('localhost', 'my_user', 'my_password', 'my_db');

//echo 'Succès... ' . $db->host_info . "\n";

//$resu = $db->query($sql) ;		
//while($vali = $resu->fetch_array()){

//$db->close();

//check base heures
$sql = "
CREATE TABLE  `heures`.`heures` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`matin_dbt` VARCHAR( 255 ) NOT NULL ,
`matin_fin` VARCHAR( 255 ) NOT NULL ,
`ap_dbt` VARCHAR( 255 ) NOT NULL ,
`ap_fin` VARCHAR( 255 ) NOT NULL ,
`date` VARCHAR( 255 ) NOT NULL ,
INDEX (  `date` )
) ";


class heures{

function affichage($mth_cour,$year_cour){
//Display add Button
$aff .= '<div ></div><center></center>';

//Display table header
$aff .= '
<table id="affichage" align="center">
	<tr>
		<td width="200px">Date</td>	
		<td width="100px">Matin</td>
		<td width="100px">Apr&egrave;s-Midi</td>
		<td width="65px">Total</td>
	</tr>
';
//Setting counter values to default
$n=0;
$y=0;
$z=0;
//Search everything in base order by date from earlyer
$sql = "SELECT * FROM `heures` ORDER BY `date` DESC";
global $db;
$res = $db->query($sql);
//Begining of table loop
while($val = $res->fetch_array()){
	//Splitting the stored date
	list($date_chiffres, $date_lettres) = explode(";", $val['date']); //Splitting number date from letter date
	//Splitting dates 
	$datexpld = explode("/", $date_chiffres);//Y/M/D
	$ltrxpld = explode(" ", $date_lettres);//D M Y
	//making an ID : YYYYMMDD
	$ID = $datexpld[0].$datexpld[1].$datexpld[2];
	//If current line of db = required month & year then display
	if($datexpld[1] == $mth_cour && $datexpld[0] == $year_cour)
	{
		//Splitting stored times one var for min one for hours
		list($mt_db_h, $mt_db_m) = explode(":", $val['matin_dbt']);
		list($mt_fn_h, $mt_fn_m) = explode(":", $val['matin_fin']);
		list($ap_db_h, $ap_db_m) = explode(":", $val['ap_dbt']);
		list($ap_fn_h, $ap_fn_m) = explode(":", $val['ap_fin']);
		//Calculating number of worked time in Minutes
		$matin = (($mt_fn_h*60)+$mt_fn_m)-(($mt_db_h*60)+$mt_db_m); 
		$apres = (($ap_fn_h*60)+$ap_fn_m)-(($ap_db_h*60)+$ap_db_m); 
		//adding morning & afternoon, converting in hours and splitting decimals
		$totpart = explode(".", ($matin + $apres)/60);
		//Converting decimals in minutes using 2 decimals
		$totpart[1] = ceil((substr($totpart[1], 0, 2)*60)/100);
		//Formatting in humanily readable
		$totpart = $totpart[0].'h '.$totpart[1].'m';
		//if negative total, timing is in progress
		if(substr($totpart,0,1) == '-'){
			$totpart = 'En Cours';
			$totalalert = 1;
		}
		//adding this timeline to the total in minutes
		$total = $total + $matin + $apres;
		//Adding line to display var
		$aff .= '
			<tr>
				<td>'.$date_lettres.'</td>
				<td>'.$mt_db_h.'h'.$mt_db_m.' - '.$mt_fn_h.'h'.$mt_fn_m.'</td>
				<td>'.$ap_db_h.'h'.$ap_db_m.' - '.$ap_fn_h.'h'.$ap_fn_m.'</td>
				<td>'.$totpart.'</td>
			</tr>
		';
	}
	//If current line of db != required month & year then store for bottm previous menu
	else{
		//Store a new month only if not the same of the previous one
		if($year_cour == $datexpld[0] && $aff_mnth[$n-1] != $datexpld[1]){ 
			
			$aff_mnth[$n] = $datexpld[1];
			$aff_mnth_lttr[$n] = $ltrxpld[2];
			$n++;
			
		}
		//Store a new year only if not the same of the previous one
		if( $year_cour != $datexpld[0] && $aff_year[$y-1] != $datexpld[0]){ 
			$aff_year[$y] = $datexpld[0];
			$y++;
		}
	}
}//End of table loop
//Splitting total decimals
$total = explode(".", $total/60);
//convert decimals in minutes
$total[1] = ceil((substr($total[1], 0, 2)*60)/100);
//humanily readable
$total = $total[0].'h '.$total[1].'m';
//if negative work in progress
if(isset($totalalert)){
	$total = 'En Cours';
}
//Display Total
$aff .= '<td colspan="3">Total</td><td>'.$total.'</td></table>';
//Year Month Menu
$aff .= '<center>';
$aff .= 'Choisir Ann&eacute;e :<br />';
$y--;
//year loop
while($y > -1 ){
	$aff .= '<a href="javascript:;" onClick="aff_mois(\''.$mth_cour.'\', \''.$aff_year[$y].'\')">'.$aff_year[$y].'</a>&nbsp;';
	$y--;
}
$aff .= "<br />Mois de l'ann&eacute;e ".$year_cour."<br />";
$n--;
//month loop
while($n > -1){
	$aff .= '<a href="javascript:;" onClick="aff_mois(\''.$aff_mnth[$n].'\', \''.$year_cour.'\')">'.$aff_mnth_lttr[$n].'</a>&nbsp;';
	$n--;

}
$aff .= '</center>';
//returning display
return $aff;
}//End function Affichage

function ajout(){
	//getting date
	$date = date("Y/m/d;l j F Y");
	//formatting Date in French
	$date = $this->trad_date($date);
	//getting actual hour
	$heure = date("H:i");
	//Splitting hour not seems to be used
	list($h,$m) = explode(":", $heure);
	//Selecting in db the actual date
	global $db;
	$sql = ("SELECT * FROM `heures` WHERE `date` = '$date'");
	$res = $db->query($sql);
	while($val = $res->fetch_array()){
		//putting present to 1 avoid new insertion
		$present = 1;
		//matin debut exists because found a date so we will test only the others
		if($val['matin_fin']==""){
			$sql = ("UPDATE `heures` SET `matin_fin`='$heure' WHERE `date` = '$date'");
			$db->query($sql);
		}
		else{
			if($val['ap_dbt']==""){
				$sql = ("UPDATE `heures` SET `ap_dbt`='$heure' WHERE `date` = '$date'");
				$db->query($sql);
		
			}
			else{
				$sql = ("UPDATE `heures` SET `ap_fin`='$heure' WHERE `date` = '$date'");
				$db->query($sql);
	
			}
		}
	}
	//there is nothing back on the search for date so will create it
	if($present != 1){
		$sql = ("INSERT INTO `heures` (`date`,`matin_dbt`) VALUES ('$date','$heure')");
		$db->query($sql);
	}
	
}//End function ajout

function trad_date($date){
	//splitting the date
	list($date_chiffres, $date_lettres) = explode(";", $date);
	//splitting the letter version for traduction
	list($jour, $jour_chfr, $mois, $annee) = explode(" ", $date_lettres);
	//switch to traduce the day
	switch ($jour){
		case "Monday" :
			$jour = "Lundi";
		break;
		case "Tuesday" :
			$jour = "Mardi";
		break;
		case "Wednesday" :
			$jour = "Mercredi";
		break;
		case "Thursday" :
			$jour = "Jeudi";
		break;
		case "Friday" :
			$jour = "Vendredi";
		break;
		case "Saturday" :
			$jour = "Samedi";
		break;
		case "Sunday" :
			$jour = "Dimanche";
		break;
	}
	//traducing month
	switch ($mois){
		case "January" :
			$mois = "Janvier";
		break;
		case "February" :
			$mois = "Février";
		break;
		case "March" :
			$mois = "Mars";
		break;
		case "April" :
			$mois = "Avril";
		break;
		case "May" :
			$mois = "Mai";
		break;
		case "June" :
			$mois = "Juin";
		break;
		case "July" :
			$mois = "Juillet";
		break;
		case "August" :
			$mois = "Août";
		break;
		case "September" :
			$mois = "Septembre";
		break;
		case "October" :
			$mois = "Octobre";
		break;
		case "November" :
			$mois = "Novembre";
		break;
		case "December" :
			$mois = "Décembre";
		break;
	}
	//Returning traduced date completed
	return $date_chiffres.";".$jour." ".$jour_chfr." ".$mois." ".$annee;
}//End Function trad_date


}//End Class heures

///////////////
//Scripts PHP//
///////////////
//Images
$botbg = <<< EOFILE
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
EOFILE;

$hidr = <<< EOFILE
/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAZAABAwERAAIRAQMRAf/EAFQAAQEBAQAAAAAAAAAAAAAAAAADAgYBAQEBAQEAAAAAAAAAAAAAAAABAgUGEAEBAQAAAAAAAAAAAAAAAAAAEiERAQEAAAAAAAAAAAAAAAAAAAAR/9oADAMBAAIRAxEAPwDtnsnBAAAAAZpUTpRK2kRtYiNNIlTSMaI//9k=
EOFILE;

$midbg = <<< EOFILE
/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgABwLUAwERAAIRAQMRAf/EAFoAAQEAAwAAAAAAAAAAAAAAAAAGBAUIAQEAAAAAAAAAAAAAAAAAAAAAEAEAAAQHAQAAAAAAAAAAAAAAAQKyczFxMgMzBQY0EQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwDqmbCIIv1PNtXJKoAtAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAATPtdXW3ZqQbjqfnhkDOB//2Q==
EOFILE;

$topbg = <<< EOFILE
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
EOFILE;

$pdflogo = <<< EOFILE
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
EOFILE;

//Images Prompt
if (isset($_GET['img']))
{
    switch ($_GET['img'])
    {
        case 'botbg' :
        header("Content-type: image/jpg");//Care! no photo prompt if headers top of that
        echo base64_decode($botbg);
        exit();//Just prompt the photo and no more
		
		case 'hidr' :
        header("Content-type: image/jpg");
        echo base64_decode($hidr);
        exit();
		
		case 'midbg' :
        header("Content-type: image/jpg");
        echo base64_decode($midbg);
        exit();
		
		case 'topbg' :
        header("Content-type: image/jpg");
        echo base64_decode($topbg);
        exit();
		
		case 'pdflogo' :
        header("Content-type: image/jpg");
        echo base64_decode($pdflogo);
        exit();
	}
}

/*Connecting to mysql
$nokia=new connexions;
$nokia->user='mpi';
$nokia->pass='mpi';
$nokia->base='MPI';
$nokia->mysql();*/

$db = new foo_mysqli('localhost', 'heures', 'heures', 'heures');

//Triggers from javascript to make work php
//whatever creating heures object
$time = new heures;
//setting up nth and year
$mth_cour = date("m");
$year_cour = date("Y");
switch($_POST['fctn']){
	case "ajt" :
		echo utf8_encode($time->ajout());
		echo utf8_encode($time->affichage($mth_cour, $year_cour));
		exit();	//because ajax request, no display more
	break;
	
	case "mois" :
		$mth_cour = $_POST['mois'];
		$year_cour = $_POST['annee'];
		echo utf8_encode($time->affichage($mth_cour, $year_cour));
		exit();
	break;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Heures - Sogipe</title>
<meta http-equiv="Content-Language" content="French" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
<!--------------------------------------------------------------Javascript------------------------------------------------------------>
<script>
function createXHR() {var request = false;try{request = new ActiveXObject('Msxml2.XMLHTTP');}catch(err2){try{request=new ActiveXObject('Microsoft.XMLHTTP');}catch (err3) {try {request = new XMLHttpRequest();}catch (err1) {request = false;}}	}return request;}

function aff_mois(mois, annee)
{
	var xhr = createXHR();
	
	xhr.onreadystatechange  = function(){if(xhr.readyState  == 4){if(xhr.status  == 200) {
	
	var reponse = xhr.responseText;
	
	document.getElementById("affichage").innerHTML = reponse;

    }else document.getElementById('affichage').innerHTML = "Code d'erreur : " + xhr.status;}}; 
	
	xhr.open("POST", "heures.php", true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");                  
	xhr.send("fctn=mois&mois="+mois+"&annee="+annee);
}

function ajt_heure()
{
	var xhr = createXHR();
	
	xhr.onreadystatechange  = function(){if(xhr.readyState  == 4){if(xhr.status  == 200) {
	
	var reponse = xhr.responseText;
	document.getElementById("affichage").innerHTML = reponse;
	

    }else document.getElementById('affichage').innerHTML = "Code d'erreur : " + xhr.status;}}; 
	
	xhr.open("POST", "heures.php", true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");                  
	xhr.send("fctn=ajt");
}

setInterval("heure();", 1000);

function heure()
{
	Today = new Date;
	document.getElementById('horloge').value = Today.getHours()+':'+Today.getMinutes();
}
</script>
<!-----------------------------Cascaded Style Sheet------------------------------------------------>
<style type="text/css">
* {
    padding: 0;
    margin: 0;
} 

body {
    background: #fff;
	font: .74em "Trebuchet MS" Verdana, Arial, sans-serif;
	line-height: 1.5em; 
}
a {
	color: #3B6EBF;
	text-decoration: none;
}

a:hover {
	text-decoration: underline;
}


#wrap {
margin: 20px auto;
width: 724px;
background: #fff url(heures.php/?img=midbg);
background-repeat: repeat-y;
}

#top { 
background: #fff url(heures.php/?img=topbg);
height: 30px;
}

#content {
padding: 0 40px 0 40px;
}

#bottom {

background: #fff url(heures.php/?img=botbg);
height: 30px;
}


.header {
height: 100px;
background: #85ACF7 url(heures.php/?img=hidr);
}

.header h1 { padding-left: 17px; padding-top: 22px; font-size: 22px; color: #FFF; }
.header h1 a { font-size: 22px; color: #FFF; text-decoration: none;}
.header h2 { padding-left: 17px; padding-top: 0px; font-size: 17px; color: #FFF; }

.breadcrumbs { 
    background: #F6F9FB; 
	border-bottom: 1px solid #E1E1E1; 
	padding: 5px;
}
	
.middle {
	float: left;
	width: 79%;
	margin: 0 10px;
	padding: 1% 1%;
	text-align: justify;
}

.right {
	float: left;
	width: 10%;
	margin: 0 10px;
	padding: 1% 1%;
	
}

.right ul {
	padding: 20px 0 15px 20px;
	margin:0;
}

.right li {
	margin-bottom:5px;
	list-style-type: square;
	color: #3B6EBF;
}

.middle h2 { color: #3B6EBF; font-size: 16px; margin-bottom: 10px; margin-top: 10px;}
.right h2 { color: #3B6EBF; font-size: 14px; margin-top: 15px;}


#clear {
	display: block;
	clear: both;
	width: 100%;
	height:1px;
	overflow:hidden;
}

#footer {
	text-align: center;
	color: #666;
}
/*----------------------------------------Perso-------------------------*/
#affichage td{
border-collapse:collapse;
border-bottom: 1px dotted blue;
border-right: 1px dotted blue;
}
</style>
</head>
<body>
<div id="wrap">

<div id="top"></div>

<div id="content">

<div class="header">
<h1><a href="#">Heures</a></h1>
<h2>Comptabilisation des heures</h2>
</div>

<div class="breadcrumbs">
<a href="#">Accueil</a> &middot; Vous &ecirc;tes ici
</div>

<div class="middle">
			
<h2>Tableau des heures</h2>
<?php echo '<span id="affichage">'.$time->affichage($mth_cour, $year_cour).'</span>'; ?>
 		
</div>
		
<div class="right">

<h2>Menu</h2>

		
<ul>
<li><input type="button" id="horloge" onClick="ajt_heure();" value="Heure !" /></li>
<li><a href="heures_pdf.php"><img src="heures.php/?img=pdflogo" alt="pdflogo" width="40" height="31" border="0" /></a></li>
<!--<li><a href="http://www.minimalistic-design.net">Minimalistic Design</a></li><li><a href="http://www.oswd.org">Open Source Web Design</a></li><li><a href="http://www.opendesigns.org">Open Designs</a></li><li><a href="http://www.openwebdesign.org">Open Web Design</a></li><li><a href="http://www.iollo.com">Iollo's review highway</a></li><li><a href="http://www.historyexplorer.net">History Timelines</a></li><li><a href="http://www.quakerparrot.info">Quaker Parrot</a></li><li><a href="http://www.moneybookersclub.com">Moneybookers Club</a></li>-->
</ul>
		
</div>

<div id="clear"></div>

</div>

<div id="bottom"></div>

</div>

<div id="footer">
&copy; Tous droits réservés
</div>

</body>
</html>
