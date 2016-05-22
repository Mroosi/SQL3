<?php


function connect_db(){
	global $connection;
	$host="localhost";
	$user="test";
	$pass="t3st3r123";
	$db="test";
	$connection = mysqli_connect($host, $user, $pass, $db) or die("ei saa ühendust mootoriga- ".mysqli_error());
	mysqli_query($connection, "SET CHARACTER SET UTF8") or die("Ei saanud baasi utf-8-sse - ".mysqli_error($connection));
}

function hangi_loom(){
     global $connection;
     $paring = "SELECT id FROM deaamooL";
     $kyss = mysqli_query($connection, $paring);
     $rida = mysqli_num_rows($kyss);
     if($rida>0){
         for ($i=0; $i <$rida ; $i++) { 
            $vastused[] = mysqli_fetch_assoc($kyss);
         }
         $_SESSION["loomaid"] = $vastused;
     }else{
         $errors= array("Hetkel loomi pole!");
     }
if(!empty($_POST)){
        if(isset($_POST["loomad"])){
            print_r($_POST["loomad"]);
            include_once('views/editvorm.html'); 
    }
   }
}



function kuva_puurid(){
    if(isset($_SESSION["kasutaja"])){
        if($_SESSION["kasutaja"]>0){    
            global $connection;
            $p= mysqli_query($connection, "select distinct(Puur) as Puur from deaamooL order by puur asc");
            $puurid=array();
            while ($r=mysqli_fetch_assoc($p)){
                $l=mysqli_query($connection, "SELECT * FROM deaamooL WHERE  Puur=".mysqli_real_escape_string($connection, $r['Puur']));
                while ($row=mysqli_fetch_assoc($l)) {
                    $puurid[$r['Puur']][]=$row;
                }
              }
            }else{
                 header('Location: http://enos.itcollege.ee/~mroosi/SQL/YL2/loomaaed.php?page=login');
                }
	}else{
                    header('Location: http://enos.itcollege.ee/~mroosi/SQL/YL2/loomaaed.php?page=login');
    }
	include_once('views/puurid.html');
	
}
function logi(){
    $viga = "";
    if(isset($_SESSION["kasutaja"])){
        if($_SESSION["kasutaja"]>0){
       header('Location: http://enos.itcollege.ee/~mroosi/SQL/YL2/loomaaed.php?page=loomad');
    }
    }
    
	if (!isset($_SESSION)) session_start();
    if(!empty($_POST)){
            global $connection;
            $k = htmlspecialchars($_POST["user"]);
            $p = htmlspecialchars($_POST["pass"]);
			$kasutaja = mysqli_real_escape_string($connection, $k);
			$parool = mysqli_real_escape_string($connection, $p);
            $paring = "SELECT UserType FROM deaamooL_Kylastajad WHERE username = '$kasutaja' AND passw = SHA1('$parool') ";
            $kyss = mysqli_query($connection, $paring);
            $rida = mysqli_num_rows($kyss);
            $_SESSION["kasutaja"] = $rida;
            if($_SESSION["kasutaja"]>0){
                for ($i=0; $i <$rida ; $i++) { 
            $vastused[] = mysqli_fetch_assoc($kyss);
            $kasutaja = $vastused[0];
         }
         $_SESSION["kasutajatyyp"] = implode($kasutaja);
               header('Location: http://enos.itcollege.ee/~mroosi/SQL/YL2/loomaaed.php?page=loomad');
                }else{
                    $errors= array("Vale kasutajanimi või parool!");
                }
        }

	include_once('views/login.html');
}

function logout(){
	$_SESSION=array();
	session_destroy();
	header("Location: ?");
}

function lisa(){
    if(isset($_SESSION["kasutajatyyp"])){
        if($_SESSION["kasutajatyyp"] == "admin"){
        if(!empty($_POST)){
            if(isset($_POST["Puur"]) && (!$_POST["Nimi"] == "") && ($_POST["Puur"]>0)){
            global $connection;
            $ln = htmlspecialchars($_POST["Nimi"]);
            $pnr = htmlspecialchars($_POST["Puur"]);
            $loomanimi = mysqli_real_escape_string($connection, $ln);
			$puuriNr = mysqli_real_escape_string($connection, $pnr);
            $lisa = "INSERT INTO deaamooL (`Nimi`, `Puur`, `Liik`) VALUES ('$loomanimi','$puuriNr','Tundmatu.png')";
            $lisamine = mysqli_query($connection, $lisa);
            header('Location: http://enos.itcollege.ee/~mroosi/SQL/YL2/loomaaed.php?page=loomad');
            }else{
                 $errors= array("Vigased väärtused!");
            }
             
        }
    }else{
      header('Location: http://enos.itcollege.ee/~mroosi/SQL/YL2/loomaaed.php?page=loomad');
  }
  }else{
      header('Location: http://enos.itcollege.ee/~mroosi/SQL/YL2/loomaaed.php?page=login');
  }
    
	include_once('views/loomavorm.html');
	
}

function upload($name){
	$allowedExts = array("jpg", "jpeg", "gif", "png");
	$allowedTypes = array("image/gif", "image/jpeg", "image/png","image/pjpeg");
	$extension = end(explode(".", $_FILES[$name]["name"]));

	if ( in_array($_FILES[$name]["type"], $allowedTypes)
		&& ($_FILES[$name]["size"] < 100000)
		&& in_array($extension, $allowedExts)) {
    // fail õiget tüüpi ja suurusega
		if ($_FILES[$name]["error"] > 0) {
			$_SESSION['notices'][]= "Return Code: " . $_FILES[$name]["error"];
			return "";
		} else {
      // vigu ei ole
			if (file_exists("pildid/" . $_FILES[$name]["name"])) {
        // fail olemas ära uuesti lae, tagasta failinimi
				$_SESSION['notices'][]= $_FILES[$name]["name"] . " juba eksisteerib. ";
				return "pildid/" .$_FILES[$name]["name"];
			} else {
        // kõik ok, aseta pilt
				move_uploaded_file($_FILES[$name]["tmp_name"], "pildid/" . $_FILES[$name]["name"]);
				return "pildid/" .$_FILES[$name]["name"];
			}
		}
	} else {
		return "";
	}
}

?>