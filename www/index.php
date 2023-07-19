<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Palo Alto Networks - Firewall Mapper</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <style>
    .orange {
        background-color: orange;
        color: white;
    }
</style>

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin - v2.4.1
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="http://localhost/index.php" class="logo d-flex align-items-center">
        <img src="http://localhost/assets/img/logopaloalto.png" alt="Palo Alto Networks">
        Firewall Mapper
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->




    
    
    

  </header><!-- End Header -->
 

  <?php
  $DBFiles = glob($target_dir . "*.csv");
  ini_set('display_errors', 0);
  error_reporting(E_ALL & ~E_NOTICE);
  ?>

  

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Comparison of NGFW models and features</h1>
      <nav>

      </nav>
    </div><!-- End Page Title -->


    <section class="section">
  
          <div class="card">
            <div class="card-body">
              

              <!-- Multi Columns Form -->
              <form class="row g-3" form action="" method="post" enctype="multipart/form-data">
    
                <div class="col-md-12">
                  <label for="file" class="form-label">Please upload TSF file (.tgz) to start </label><br>
                  <input type="file" name="file" id="file">
                  </select>  
                  <br><br>
                  <label for="throughput_text">(Optional) Estimated Threat Prevention Throughput (Gbps):</label>
                  <input type="text" id="throughput" name="throughput" value="0">
                  <br><br>
                  <label for="panosv_text">PANOS Version:</label>
                  <select name="panosversion">
                  <?php
                    if (empty($DBFiles)) {
                      
                    echo '<option value="a">DB/csv file not found</option>';

                
                  } else {
                    for ($i = 0; $i < count($DBFiles); $i++) {
                      $nombreArchivo = basename($DBFiles[$i]);
                      echo '<option value="' . $nombreArchivo . '">' . $nombreArchivo . '</option>';
                    }
                  }

                  ?>
                  </select>

                </div>

                
                <div class="text-center">
                  <button type="submit" name="submit" class="btn btn-primary">Compare</button>

                </div>
              </form><!-- End Multi Columns Form -->

            </div>
          </div>

     
 

    </section>

  

  <?php
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["file"]["name"]);
$uploadOk = 1;
$desc_completada=0;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

function eliminarDirectorio($directorio) {
  if (!is_dir($directorio)) {
      // Verificar si el directorio existe
      return;
  }

  $archivos = array_diff(scandir($directorio), array('.', '..'));
  foreach ($archivos as $archivo) {
      if (is_dir("$directorio/$archivo")) {
          // Eliminar subcarpeta de forma recursiva
          eliminarDirectorio("$directorio/$archivo");
      } else {
          // Eliminar archivo
          unlink("$directorio/$archivo");
      }
  }

  // Eliminar la carpeta vacía
  rmdir($directorio);
}


// obtener valores del post y generar consulta SQL
if (isset($_POST['submit'])){

  $throughput = isset($_POST['throughput']) ? $_POST['throughput'] : 0;
  $panosversion = $_POST['panosversion'];


// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo "<script type='text/javascript'>alert('El fichero no cumple los requisitos (Fichero tipo imagen jpg o png y que no supere 500KB)');</script>";
  
  // if everything is ok, try to upload file
} else {
  $rutaDestino = $target_dir;

  if (move_uploaded_file($_FILES["file"]["tmp_name"], $rutaDestino . $_FILES["file"]["name"])) {
      $rutaArchivoSubido = $rutaDestino . $_FILES["file"]["name"];

          
      try {

        exec("tar -xzf $target_file -C $target_dir");
  
       $desc_completada=1;







    } catch (Exception $e) {
      echo "Error al descomprimir el archivo: " . $e->getMessage();
      echo "<br>";
      $desc_completada=0;
  }

  $running_config = $target_dir . "opt/pancfg/mgmt/saved-configs/.merged-running-config.xml";
  $sdb_interfaces = $target_dir . "tmp/cli/logs/sdb.txt";
  $hw_price = "hardwareprice.txt";
  $clifile = '';
  $edldomain = '';
  $edls=0;
  $archivo = glob($target_dir . "tmp/cli/*.txt");
  if (!empty($archivo)) {
      $clifile = $archivo[0];
  }
  


  $archivo = glob($target_dir . "opt/pancfg/mgmt/devices/localhost.localdomain/*.dbl");
  if (!empty($archivo)) {
      $edldomain = $archivo;
      $edls=$edls+count($archivo);
  }

  $archivo = glob($target_dir . "opt/pancfg/mgmt/devices/localhost.localdomain/*.ubl");
  if (!empty($archivo)) {
      $edlurl = $archivo;
      $edls=$edls+count($archivo);  
    }

  $archivo = glob($target_dir . "opt/pancfg/mgmt/devices/localhost.localdomain/*.ebl");
  if (!empty($archivo)) {
      $edlip = $archivo;
      $edls=$edls+count($archivo);
      }
  

    $ini_vsys=0;
    $fin_vsys=0;
    $ini_system=0;
    $fin_system=0;
    $ini_zone=0;
    $fin_zone=0;
    $ini_dhcp=0;
    $fin_dhcp=0;
    $ini_secrules=0;
    $fin_secrules=0;
    $ini_natrules=0;
    $fin_natrules=0;
    $ini_vrouter=0;
    $fin_vrouter=0;
    $posicionesZone = array();
    $posicionesEndZone = array();
    $posicionesReglasSeg = array();
    $posicionesEndReglasSeg = array();
    $posicionesNat = array();
    $posicionesEndNat = array();
    $posicionesAddress = array();
    $posicionesEndAddress = array();
    $posicionNat=0;
    $posicionVR=0;
    $posicionesEndVR = array();
    $posicionesVR = array();

    $patternvsys = '/<entry name="vsys\d+"(.*?)>/';
    $patternvr = '/<entry name="([^"]+)">/';
    $encontradoVsys = false;
    $encontradoZone = false;
    $encontradoEndZone = false;
    $natrules = 0;
    $Addresses = 0;
    $fqdn = 0;

    $encontradoNat = false;
    $encontradoVR = false;
    $encontradoVR2 = false;
    $encontradoVR3 = false;
    $encontradoVR4 = false;
    $encontradoVR5 = false;
    $encontradoVR_linea=0;
    $encontradoVR2_linea=0;
    
    $entrar = true; //vr
    $entrar2 = false; //vr
    $entrar3 = 0; //vr
    $numeroSesiones=0;

    $patron_mac = '/total\s+ARP\s+entries\s+in\s+table\s+:\s+(\d+)/';
    $mac_table=0;
    $patron_serial = '/serial:\s+(\d+)/i';
    $serial="";
    $patron_sesiones = '/Number of allocated sessions:\s+(\d+)/';
    $patron_GPUsers = '/Total Current Users: (\d+)/';
    $UsuariosGP=0;
    $patron_lic = '/Feature:\s+(.+)/';
    $subscriptions = [];
    $patron_modelo = '/model:\s+(.+)/';
    $modelo="";
    $panorama=0;
    $patron_panorama= '/<panorama-server>([\w.-]+)<\/panorama-server>/';
    $patron_interfaces = '/^.*\.status:.*\'link\': Up.*$/';

    $lines = file($running_config); // Leer el archivo en un array de líneas
    $lineas_clifile = file($clifile); // Leer el archivo en un array de líneas
    $linesSdb = file($sdb_interfaces); // Leer el archivo en un array de líneas
    if (file_exists($hw_price)) {
      $lineshwprice = file($hw_price); // Leer el archivo en un array de líneas
  } else {
    $lineshwprice = ""; 

  }

   
    
    $numEDLDomain=0;
    foreach ($edldomain as $archivo) {
      $lineas_edldomain = file($archivo);
      $numEDLDomain = $numEDLDomain + count($lineas_edldomain);
    }


    
    $numEDLURL=0;
    foreach ($edlurl as $archivo) {
      $lineas_edlurl = file($archivo);
      $numEDLURL = $numEDLURL + count($lineas_edlurl);
    }

    $numEDLIP=0;
    foreach ($edlip as $archivo) {
      $lineas_edlip = file($archivo);
      $numEDLIP = $numEDLIP + count($lineas_edlip);
    }


// recorrer el fichero sdb.txt en busca de las interfaces

$puertosProcesados = array(); // Arreglo para almacenar los números de puerto procesados
$conteoSettingType = array(); // Arreglo multidimensional para almacenar los conteos de cada combinación de setting y type

foreach ($linesSdb as $lineaSdb) {
  if (preg_match($patron_interfaces, $lineaSdb, $coincidencias)) {
      preg_match("/'setting': (\d+Gb)/", $lineaSdb, $matchesSetting);
      $settingValue = $matchesSetting[1];

      preg_match("/'type': (\w+(-\w+)*)/", $lineaSdb, $matchesType);
      $typeValue = $matchesType[1];
      
      if ($typeValue === "Internal") {
        continue; // Saltar a la siguiente iteración si el tipo es "Internal"
      }
      preg_match("/sys\.s(\d+)\.p(\d+)/", $lineaSdb, $matches);
      $numeroS = $matches[1];
      $numeroP = $matches[2];
      $numeroPuerto = "s$numeroS.p$numeroP";

      // Verificar si el número de puerto ya se ha procesado antes
      if (in_array($numeroPuerto, $puertosProcesados)) {
          continue; // Saltar a la siguiente iteración si el número de puerto ya se ha procesado
      }

      // Almacenar el número de puerto en el arreglo de puertos procesados
      $puertosProcesados[] = $numeroPuerto;

      // Incrementar el conteo correspondiente en el arreglo multidimensional
      if (isset($conteoSettingType[$settingValue][$typeValue])) {
          $conteoSettingType[$settingValue][$typeValue]++;
      } else {
          $conteoSettingType[$settingValue][$typeValue] = 1;
      }

  }
}







// recorrer el fichero de CLI
    foreach ($lineas_clifile as $num_linea => $linea_cli) {
    
      if (preg_match($patron_mac, $linea_cli, $matches)) {
        $mac_table = $matches[1];
      }

      if (preg_match($patron_serial, $linea_cli, $matches)) {
        $serial = $matches[1];
      }

      if (preg_match_all($patron_lic, $linea_cli, $matches)) {
        $subscriptions = array_merge($subscriptions, $matches[1]);
    }

    if ($numeroSesiones==0){ 
      if (preg_match($patron_sesiones, $linea_cli, $matches)) {
        $numeroSesiones = $matches[1];
      }
    }

    if (preg_match($patron_GPUsers, $linea_cli, $matches)) {
      $UsuariosGP = $matches[1];
    }

    if ($modelo==""){
      if (preg_match($patron_modelo, $linea_cli, $matches)) {
        $modelo = $matches[1];
      }
    }

    }


    foreach ($lines as $num_linea => $linea) {

     
        if (trim($linea) == "<vsys>") { 
          $ini_vsys = $num_linea;
        }
        if (trim($linea) == "</vsys>") { 
          $fin_vsys = $num_linea;
        }
      


        if ($fin_system==0 ){
          if (trim($linea) == "<deviceconfig>") { // Utilizar trim() para eliminar espacios en blanco al inicio y final de la línea
            $ini_system = $num_linea;
          }
          if (trim($linea) == "</deviceconfig>") { // Utilizar trim() para eliminar espacios en blanco al inicio y final de la línea
            $fin_system = $num_linea;
          }
        }



       //para encontrar las Zonas
        if (!$encontradoVsys && preg_match($patternvsys, $linea)) {
          $encontradoVsys = true;
        } elseif ($encontradoVsys) {
          if (strpos($linea, '<zone>') !== false) {
            $encontradoZone = true;
            $posicionesZone[] = $num_linea;
          } elseif (strpos($linea, '</zone>') !== false) {
            $encontradoEndZone = true;
            $posicionesEndZone[] = $num_linea;
            $encontradoVsys = false; // Reiniciamos la variable de estado para buscar la siguiente combinación
          }
        }


     

        if (trim($linea) == "<dhcp>") { // Utilizar trim() para eliminar espacios en blanco al inicio y final de la línea
          $ini_dhcp = $num_linea;
        }
        if (trim($linea) == "</dhcp>") { // Utilizar trim() para eliminar espacios en blanco al inicio y final de la línea
          $fin_dhcp = $num_linea;
        }
        

        if (trim($linea) == "<security>") { // Utilizar trim() para eliminar espacios en blanco al inicio y final de la línea
          $posicionesReglasSeg[] = $num_linea;

        }
        if (trim($linea) == "</security>") { // Utilizar trim() para eliminar espacios en blanco al inicio y final de la línea
          $posicionesEndReglasSeg[] = $num_linea;
        }


        if (!$encontradoNat && strpos($linea, "<nat>") !== false) {
          
          $posicionNat = $num_linea;
        } elseif ($posicionNat==$num_linea-1 && strpos($linea, "<rules>") !== false) {
          $posicionesNat[] = $posicionNat;
          $encontradoNat = true;
        } else {
          $posicionNat = 0;

        }
        if ($encontradoNat && strpos($linea, "</nat>") !== false) {
          $posicionesEndNat[] = $num_linea;
          $encontradoNat = false;

        }


        //encontrar posiciones para <Address> y </address>
        if (!$encontradoAddress && trim($linea) == "<address>") {
              $posicionAddress = $num_linea;
        } elseif ($posicionAddress==$num_linea-1 && strpos($linea, "<entry name=") !== false) {
          $posicionesAddress[] = $num_linea-1;
          $encontradoAddress = true;
        } else {
          $posicionAddress = 0;
        }

        if ($encontradoAddress && strpos($linea, "</address>") !== false) {
          $posicionesEndAddress[] = $num_linea;
          $encontradoAddress = false;
        }

    //encontrar los virtual routers

            // Buscar la línea con "<devices>"
            if ((!$encontradoVR) && (trim($linea) == "<virtual-router>")) {
            $encontradoVR = true;
            $encontradoVR_linea=$num_linea;

          }
    
  if ( (preg_match('/<entry name="([^"]+)">/', $linea, $matches)  ) && ($encontradoVR_linea==$num_linea-1)) {
    $posicionesVR[] = $encontradoVR_linea;
    $encontradoVR2=true;
 
  } else {
    $encontradoVR = false;
  }

 

 

  // Buscar "</virtual-router>"
  if (($encontradoVR2) && (trim($linea) == "</entry>")) {
    $encontradoVR3 = true;
    $encontradoVR3_linea=$num_linea;
  }

  if ($encontradoVR2 && $encontradoVR3 && ($encontradoVR3_linea==$num_linea-1) && preg_match('/<\/virtual-router>/', $linea)) {
      $posicionesEndVR[] = $num_linea;
      $encontradoVR2 = false;
      $encontradoVR3 = false;
      // echo "<script language='JavaScript'>alert($num_linea);</script>";
    
      
   
  }










        // buscar panorama
        if (preg_match($patron_panorama, $linea, $matches)) {
          $panorama = 1;
        }




    }
    $vsys=0;
    $zonas=0;
    $dhcp=0;
    $dhcprelay=0;
    $secrules=0;
    $vrouters=0;
    $vrt=0;
    $sys=0;
    $hostname="";
    $sizeZoneArray = count($posicionesZone);
   
    $sizeReglasSegArray = count($posicionesReglasSeg);
    $sizeReglasNatArray = count($posicionesNat);
    $sizeAddressArray = count($posicionesAddress);

    $sizeVR = count($posicionesVR);
    $checkVR2 = false;
    $checkVR1 = false;

    $encontradoVR3_linea = 0;
    
   

    foreach ($lines as $num_línea => $línea) {
  
      
        if ($num_línea >= ($ini_vsys - 1) && $num_línea <= ($fin_vsys - 1)) {
          if (preg_match($patternvsys, $línea)) {
            $vsys++;
          }
        }
      



      if ($num_línea >= ($ini_system - 1) && $num_línea <= ($fin_system - 1)) {
        if ((strpos($línea, "<hostname>") !== false) && $fin_system>=1) {
          $etiquetaInicio = "<hostname>";
          $etiquetaFin = "</hostname>";
          $inicio = strpos($línea, $etiquetaInicio) + strlen($etiquetaInicio);
          $fin = strpos($línea, $etiquetaFin);
          $hostname = trim(substr($línea, $inicio, $fin - $inicio));
        }
      }


      for ($i = 0; $i < $sizeZoneArray; $i++) {
        if ($num_línea >= ($posicionesZone[$i] - 1) && $num_línea <= ($posicionesEndZone[$i] - 1)) {
          if (trim($línea) == "</entry>") {  
            $zonas++;
          }
        }
    }

   
      //dhcp hay que revisar para tener el dato de dhcp servers y relays
      if ($num_línea >= ($ini_dhcp - 1) && $num_línea <= ($fin_dhcp - 1)) {
        if (trim($línea) == "</server>") { 
          $dhcp++;
        }elseif (trim($línea) == "</relay>") { 
          $dhcprelay++;
        }
      }

      for ($i = 0; $i < $sizeReglasSegArray; $i++) {
        if ($num_línea >= ($posicionesReglasSeg[$i] - 1) && $num_línea <= ($posicionesEndReglasSeg[$i] - 1)) {
          if (trim($línea) == "</entry>") {  
            $secrules++;
          }
        }
    }

    
    for ($i = 0; $i < $sizeReglasNatArray; $i++) {
      if ($num_línea >= ($posicionesNat[$i] - 1) && $num_línea <= ($posicionesEndNat[$i] - 1)) {
        if (trim($línea) == "</entry>") {  
          $natrules++;
        }
      }
  }
 //buscar entry para los objetos
  for ($i = 0; $i < $sizeAddressArray; $i++) {
    
    if ($num_línea >= ($posicionesAddress[$i] - 1) && $num_línea <= ($posicionesEndAddress[$i] - 1)) {
      if (trim($línea) == "</entry>") {  
        $Addresses++;
      }
      if (strpos($línea, "</fqdn>") !== false){
        $fqdn++;
      }
    }
}







// Virtual Routers

for ($i = 0; $i < $sizeVR; $i++) {
  if ($num_línea >= ($posicionesVR[$i] - 1) && $num_línea <= ($posicionesEndVR[$i] - 1) ) {
    if (trim($línea) == "<ecmp>" ) {
      $vrouters++;

    }
  }
}

/*
for ($i = 0; $i < $sizeVR; $i++) {
  if (($num_línea >= ($posicionesVR[$i] - 1)) && ($num_línea <= ($posicionesEndVR[$i] - 1)) && $entrar2 && $encontradoVR3_linea==$num_línea-1 ) {
    if (preg_match($patternvr, $línea)) {  
      $entrar2 = false;
      $encontradoProtocol = false;
      $encontradoRoutingTable = false;
      $encontradoEcmp = false;

      $vrouters++;


      
    }
  }
}
*/



}

// Borrar contenido carpeta:

eliminarDirectorio($target_dir);
$directorio = $target_dir;

if (!is_dir($directorio)) {
    // Verificar si el directorio no existe
    mkdir($directorio, 0777, true);

}


foreach ($lineshwprice as $lineahwprice) {
  if ($modelo == $lineahwprice) {

  }
}






?>
 
 <div class="pagetitle">
      <h1>Results</h1>
      <nav>

      </nav>
    </div><!-- End Page Title -->
    <section class="section">
          <div class="card">
            <div class="card-body">

<?php
$arrayModelo = array();
$encontradoModelo = false;
// Ruta del archivo CSV
$csvFile = $panosversion;
// Abrir el archivo CSV en modo lectura
$file = fopen($csvFile, 'r');

// Comprobar si se pudo abrir el archivo
if ($file) {
//primer paso para obtener array de la información del modelo actual
while (($line = fgetcsv($file, 0, ';')) !== false) {
  foreach ($line as $index => $column) {
      if ($index === 0) {
          if ($modelo == $column) {
              $encontradoModelo = true;
          }
      }

      if ($encontradoModelo) {
          $arrayModelo[$index] = $column;
      }
  }

  if ($encontradoModelo) {
      break; // Sale del bucle while una vez que se ha encontrado el modelo
  }
}

    // Cerrar el archivo
    fclose($file);
} else {
    echo 'No se pudo abrir el archivo CSV.';
}


    echo '<style>';
    echo 'table {';
    echo '  border-collapse: collapse;';
    echo '  margin: 0 auto;';
    echo '}';
    echo 'table, th, td {';
    echo '  border: 1px solid black;';
    echo '  text-align: center;';
    echo '}';
    echo 'td {';
    echo '  text-align: center;';
    echo '}';
    echo '.red {';
    echo '  background-color: red;';
    echo '  color: white;';
    echo '}';
    echo '.green {';
    echo '  background-color: green;';
    echo '  color: white;';
    echo '}';
    echo '</style>';

   
  
  
   
    echo '<br><br><table>';

    echo '<tr>';
    echo '<th>Firewall</th>';
    echo '<th>TP Throughput</th>';
    //echo '<th>CPS</th>';
    echo '<th>Sessions</th>';
    echo '<th>Sec. Policies</th>';
    echo '<th>NAT Rules</th>';
    echo '<th>Zones</th>';
    echo '<th>Add. Objects</th>';
    echo '<th>FQDN Objects</th>';
    echo '<th>EDL</th>';
    echo '<th>Nº registros EDL IPs</th>';
    echo '<th>Nº registros EDL Dominios</th>';
    echo '<th>Nº registros EDL URL</th>';
    echo '<th>VR</th>';
    echo '<th>Base Vsys</th>';
    echo '<th>Max. Vsys</th>';
    echo '<th>ARP Table</th>';
    echo '<th>DHCP Servers</th>';
    echo '<th>DHCP Relays</th>';
    echo '<th>GP Users</th>';
    echo '<th>Interfaces in use</th>';
    if ($lineshwprice!=""){
      echo '<th>Base HW Price</th>';
    }

    echo '</tr>';

    echo '<tr>';
    echo '<th>' . $hostname .'</th>';
    echo '<th>'. $throughput.'/'.$arrayModelo[1].'</th>';
    //echo '<th>cps</th>';
    echo '<th>' .$numeroSesiones .'/'.$arrayModelo[3]. '</th>';
    echo '<th>' .$secrules . '/'.$arrayModelo[4].'</th>';
    echo '<th>' . $natrules . '/'.$arrayModelo[5].'</th>';
    echo '<th>' .$zonas . '/'.$arrayModelo[6].'</th>';
    echo '<th>'. $Addresses .'/'.$arrayModelo[7].'</th>';
    echo '<th>'. $fqdn .'/'.$arrayModelo[8].'</th>';
    echo '<th>'. $edls .'/'.$arrayModelo[9].'</th>';
    echo '<th>' .$numEDLIP .'/'.$arrayModelo[10]. '</th>';
    echo '<th>' .$numEDLDomain . '/'.$arrayModelo[11].'</th>';
    echo '<th>' .$numEDLURL . '/'.$arrayModelo[12].'</th>';
    echo '<th>' .$vrouters . '/'.$arrayModelo[13].'</th>';
    echo '<th>' . $vsys .'/'.$arrayModelo[14].'</th>';
    echo '<th>' . $vsys .'/'.$arrayModelo[15].'</th>';
    echo '<th>' .$mac_table .'/'.$arrayModelo[16]. '</th>';
    echo '<th>' . $dhcp-$dhcprelay .'/'.$arrayModelo[17]. '</th>';
    echo '<th>' . $dhcprelay . '/'.$arrayModelo[18].'</th>';
    echo '<th>' .$UsuariosGP . '/'.$arrayModelo[19].'</th>';

    echo '<th>';
    // Mostrar los conteos de cada combinación de setting y type
foreach ($conteoSettingType as $setting => $conteoType) {
  foreach ($conteoType as $type => $conteo) {
      echo "$conteo-$type-$setting<br>";
  }
}
echo '</th>';

if ($lineshwprice!=""){
  foreach ($lineshwprice as $lineahwprice) {
    $parts = explode(';', $lineahwprice); // Split the line into two parts based on the semicolon (;)
    $model = trim($parts[0]); // Remove any leading/trailing whitespace from the model part
  
    if ($model === $modelo) {
      $formattedPrice = number_format($parts[1], 0, '.', '.'); // Format the price with dot as thousands separator
      echo '<th>' . $formattedPrice . '$</th>'; // Append dollar sign at the end
      break; // If a match is found, exit the loop
    }
  }
}


    echo '</tr>';
$dhcpservers=$dhcp-$dhcprelay;
$modelorecomendado = array();   
$modelodb = array();



// Abrir el archivo CSV en modo lectura
$file = fopen($csvFile, 'r');

// Comprobar si se pudo abrir el archivo
if ($file) {
//primer paso para obtener array de la información del modelo actual
while (($line = fgetcsv($file, 0, ';')) !== false) {
  foreach ($line as $index => $column) {
      if ($index === 0) {
          if ($modelo == $column) {
              $encontradoModelo = true;
          }
      }

      if ($encontradoModelo) {
          $arrayModelo[$index] = $column;
      }
  }

  if ($encontradoModelo) {
      break; // Sale del bucle while una vez que se ha encontrado el modelo
  }
}






    
$numerolinea=1;
$primerValorRecomendado = null;
  while (($line = fgetcsv($file, 0, ';')) !== false) {
    if (in_array("break", $line)) {
      break; // Detiene la lectura del archivo cuando se encuentra el texto "break" en alguna línea
    }
    echo '<tr>';
    $modelorecomendado[$numerolinea]=1;
    $modelorecomendadovsyslicense[$numerolinea]=0;
    // Dividir la línea en columnas
    foreach ($line as $index => $column) {
      

        // Comparar valores en posiciones específicas
        
        if ($index === 0) {
          echo '<td ><b>' .  $column . '<b></td>';
        $modelodb[$numerolinea]=$column;

        }else if ($index === 1 && floatval(str_replace(',', '.', $column)) >= $throughput) {
          echo '<td class="green">' . $throughput . '/' . $column . '</td>';
        } else if ($index === 1 && floatval(str_replace(',', '.', $column)) < $throughput) {
        echo '<td class="red">' . $throughput . '/' .  $column . '</td>';
        $modelorecomendado[$numerolinea]=0;
      }else if ($index === 2 ) {

      


        }else if ($index === 3 && floatval(str_replace(',', '.', $column)) >= $numeroSesiones) {
            echo '<td class="green">' . $numeroSesiones . '/' . $column . '</td>';
        } else if ($index === 3 && floatval(str_replace(',', '.', $column)) < $numeroSesiones) {
          echo '<td class="red">' . $numeroSesiones . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        }else if ($index === 4 && floatval(str_replace(',', '.', $column)) >= $secrules) {
            echo '<td class="green">' . $secrules . '/' . $column . '</td>';
        } else if ($index === 4 && floatval(str_replace(',', '.', $column)) < $secrules) {
          echo '<td class="red">' . $secrules . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        }else if ($index === 5 && floatval(str_replace(',', '.', $column)) >= $natrules) {
            echo '<td class="green">' . $natrules . '/' . $column . '</td>';
        } else if ($index === 5 && floatval(str_replace(',', '.', $column)) < $natrules) {
          echo '<td class="red">' . $natrules . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 6 && floatval(str_replace(',', '.', $column)) >= $zonas) {
          echo '<td class="green">' . $zonas . '/' . $column . '</td>';
        } else if ($index === 6 && floatval(str_replace(',', '.', $column)) < $zonas) {
          echo '<td class="red">' . $zonas . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 7 && floatval(str_replace(',', '.', $column)) >= $Addresses) {
          echo '<td class="green">' . $Addresses . '/' . $column . '</td>';
        } else if ($index === 7 && floatval(str_replace(',', '.', $column)) < $Addresses) {
          echo '<td class="red">' . $Addresses . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 8 && floatval(str_replace(',', '.', $column)) >= $fqdn) {
          echo '<td class="green">' . $fqdn . '/' . $column . '</td>';
        } else if ($index === 8 && floatval(str_replace(',', '.', $column)) < $fqdn) {
          echo '<td class="red">' . $fqdn . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 9 && floatval(str_replace(',', '.', $column)) >= $edls) {
          echo '<td class="green">' . $edls . '/' . $column . '</td>';
        } else if ($index === 9 && floatval(str_replace(',', '.', $column)) < $edls) {
          echo '<td class="red">' . $edls . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 10 && floatval(str_replace(',', '.', $column)) >= $numEDLIP) {
          echo '<td class="green">' . $numEDLIP . '/' . $column . '</td>';
        } else if ($index === 10 && floatval(str_replace(',', '.', $column)) < $numEDLIP) {
          echo '<td class="red">' . $numEDLIP . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 11 && floatval(str_replace(',', '.', $column)) >= $numEDLDomain) {
          echo '<td class="green">' . $numEDLDomain . '/' . $column . '</td>';
        } else if ($index === 11 && floatval(str_replace(',', '.', $column)) < $numEDLDomain) {
          echo '<td class="red">' . $numEDLDomain . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 12 && floatval(str_replace(',', '.', $column)) >= $numEDLURL) {
          echo '<td class="green">' . $numEDLURL . '/' . $column . '</td>';
        } else if ($index === 12 && floatval(str_replace(',', '.', $column)) < $numEDLURL) {
          echo '<td class="red">' . $numEDLURL . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;

        } else if ($index === 13 && floatval(str_replace(',', '.', $column)) >= $vrouters) {
          echo '<td class="green">' . $vrouters . '/' . $column . '</td>';
        } else if ($index === 13 && floatval(str_replace(',', '.', $column)) < $vrouters) {
          echo '<td class="red">' . $vrouters . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 14 && floatval(str_replace(',', '.', $column)) >= $vsys) {
          echo '<td class="green">' . $vsys . '/' . $column . '</td>';
        } else if ($index === 14 && floatval(str_replace(',', '.', $column)) < $vsys) {
          echo '<td class="orange">' . $vsys . '/' .  $column . '</td>';
          $modelorecomendadovsyslicense[$numerolinea]=1;

        } else if ($index === 15 && floatval(str_replace(',', '.', $column)) >= $vsys) {
          echo '<td class="green">' . $vsys . '/' . $column . '</td>';
        } else if ($index === 15 && floatval(str_replace(',', '.', $column)) < $vsys) {
          echo '<td class="red">' . $vsys . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 16 && floatval(str_replace(',', '.', $column)) >= $mac_table) {
          echo '<td class="green">' . $mac_table . '/' . $column . '</td>';
        } else if ($index === 16 && floatval(str_replace(',', '.', $column)) < $mac_table) {
          echo '<td class="red">' . $mac_table . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 17 && floatval(str_replace(',', '.', $column)) >= $dhcpservers) {
          echo '<td class="green">' . $dhcpservers . '/' . $column . '</td>';
        } else if ($index === 17 && floatval(str_replace(',', '.', $column)) < $dhcpservers) {
          echo '<td class="red">' . $dhcpservers . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 18 && floatval(str_replace(',', '.', $column)) >= $dhcprelay) {
          echo '<td class="green">' . $dhcprelay . '/' . $column . '</td>';
        } else if ($index === 18 && floatval(str_replace(',', '.', $column)) < $dhcprelay) {
          echo '<td class="red">' . $dhcprelay . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else if ($index === 19 && floatval(str_replace(',', '.', $column)) >= $UsuariosGP) {
          echo '<td class="green">' . $UsuariosGP . '/' . $column . '</td>';
        } else if ($index === 19 && floatval(str_replace(',', '.', $column)) < $UsuariosGP) {
          echo '<td class="red">' . $UsuariosGP . '/' .  $column . '</td>';
          $modelorecomendado[$numerolinea]=0;
        } else {
          echo '<td >' .  $column . '</td>';

        }
        

   
    }
  
    echo '<td >Check Manually</td>';
    if ($lineshwprice!=""){
      foreach ($lineshwprice as $lineahwprice) {
        $parts = explode(';', $lineahwprice); // Split the line into two parts based on the semicolon (;)
        $model = trim($parts[0]); // Remove any leading/trailing whitespace from the model part
      
        if ($model === $modelodb[$numerolinea]) {
          $formattedPrice = number_format($parts[1], 0, '.', '.'); // Format the price with dot as thousands separator
          echo '<th>' . $formattedPrice . '$</th>'; // Append dollar sign at the end
          break; // If a match is found, exit the loop
        }
      }
    }
 
    echo '</tr>';
    $numerolinea++;
}

// Verificar si $primerValorRecomendado está vacío
if (empty($primerValorRecomendado)) {
  foreach ($modelorecomendado as $key => $value) {
      if ($value == 1) {
          $primerValorRecomendado = $modelodb[$key];
          $VsysLicense=$modelorecomendadovsyslicense[$key];
          break;
      }
  }
}

    // Cerrar el archivo
    fclose($file);
} else {
    echo 'No se pudo abrir el archivo CSV.';
}



    echo '</table><br><br>';

    

echo '<span style="color: green; font-weight: bold; font-size: larger;">Recommended Model: ' . $primerValorRecomendado . '*</span>';
if ($VsysLicense==1){
echo '<br><span style="color: red; font-weight: bold; font-size: larger;">Note: Vsys extension License Needed!</span>';
}

if ($panorama && $secrules==0){
  echo '<br><br><span style="color: red;">Warning: The firewall is managed by Panorama and no policies are found. NAT and security policies should be checked through Panorama.</span>';
}
echo "<br><br> * The information provided on this website is intended for informational purposes based on the obtained data. However, it is important to note that obtaining the actual recommendation for the equipment to acquire may require reviewing additional specific information. Please consider consulting further resources for a comprehensive decision.";
?>


    </div>
    </div>
</section>
</main>
<?php



  } else {
    echo "<script type='text/javascript'>alert('Ha habido un problema al subir el fichero');</script>";
  }



}






}



?>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>


  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>


  <!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

<ul class="sidebar-nav" id="sidebar-nav">
<li class="nav-item">
      <i class="bi bi-grid"></i>
      <span>PANFirewallMapper Version: 1.1</span>
  </li>
  <li class="nav-item">
      <i class="bi bi-grid"></i>
      <span>DB PANos version: <?php echo preg_replace('/^DB-PANFW-v([\d.]+)\.csv$/', '$1', $panosversion); ?></span>
  </li>

  <li class="nav-item">
      <i class="bi bi-grid"></i>
      <span>Hostname: <?php echo $hostname ?></span>
  </li>
  <li class="nav-item">
      <i class="bi bi-grid"></i>
      <span>Model: <?php echo $modelo ?></span>
  </li>
  <li class="nav-item">
      <i class="bi bi-grid"></i>
      <span>Serial: <?php echo $serial ?></span>
  </li>

  <?php



echo '<li class="nav-item"></li>';
echo '<i class="bi bi-grid"> Licenses</i>';
echo '<ul>';
foreach ($subscriptions as $sub) {
  echo '<li class="nav-item">' . $sub . '</li>';
}
echo '</ul>';

//checks
echo '<li class="nav-item"></li>';
echo '<i class="bi bi-grid"> Checks</i>';
echo '<ul>';
// Fichero cli encontrado
echo '<li class="nav-item">';
if (!empty($clifile)) {
  echo '<span style="color: green;"> CLI File found </span>';
} else {
  echo '<span style="color: red;"> CLI File not found </span>';
}
echo '</li>';

// Fichero config encontrado
echo '<li class="nav-item">';
if (!empty($running_config)) {
  echo '<span style="color: green;"> Config file found </span>';
} else {
  echo '<span style="color: red;"> Config file not found </span>';
}
echo '</li>';

// Fichero Sdb encontrado
echo '<li class="nav-item">';
if (!empty($sdb_interfaces)) {
  echo '<span style="color: green;"> SDB file found </span>';
} else {
  echo '<span style="color: red;"> SDB file not found </span>';
}
echo '</li>';


// Fichero DB encontrado
echo '<li class="nav-item">';
if (!empty($DBFiles)) {
  echo '<span style="color: green;"> DB/csv file  found </span>';
} else {
  echo '<span style="color: red;"> DB/csv file not found </span>';
}
echo '</li>';


// Fichero EDLs encontrado
echo '<li class="nav-item">';
if (!empty($archivo)) {
  echo '<span style="color: green;"> EDL files found </span>';
} else {
  echo '<span style="color: red;"> EDL files not found </span>';
}
echo '</li>';

echo '<li class="nav-item">';
if ($lineshwprice!="") {
  echo '<span style="color: green;"> Hardware price file found </span>';
} else {
  echo '<span style="color: red;"> Hardware price file not found </span>';
}
echo '</li>';


//descompresión ok
echo '<li class="nav-item">';
if ($desc_completada==1) {
  echo '<span style="color: green;"> Unzip OK </span>';
} else {
  echo '<span style="color: red;"> Unzip error </span>';
}
echo '</li>';


//Panorama
echo '<li class="nav-item">';
if ($panorama==1) {
  echo '<span style="color: green;"> Managed by Panorama </span>';
} else {
  echo '<span style="color: red;"> Not managed by Panorama</span>';
}
echo '</li>';
echo '</ul>';


?>



</ul>

</aside><!-- End Sidebar-->
</body>

</html>