<?php
/**
 * home.php Main Site Page.
 * show the menu and options
 *
 *
 * @author Mariano Luna
 * @copyright Copyright (c) 2011
 */

require_once('config.php');

if($_SESSION['userlevel'] < USER) {
	// Guest, please login.
	header('Location: ' . WWW_ROOT . 'login.php');
	exit();
} 

$_PageTitle = 'Welcome ' . $_SESSION["username"] ; 
$tabArray =  array();
$tabArray['repository'] = '<a href="home.php" class="active">Repository Browser</a>';
$tabArray['scheduler'] = '<a href="scheduler.php" class="active">Scheduler</a>';
$tabArray[99] = '<a href="#" class="active">Logged as: ' . $_SESSION["username"] . '</a>';
$_PageTabs = decoratePageTabs($tabArray, 99);

$root = (isset($_GET['root'])) ? htmlentities($_GET['root']) : '/';
$WSRest = new PestXML(JS_WS_URL);
// Set auth Header
$WSRest->curl_opts[CURLOPT_COOKIE] = $_SESSION["JSCookie"] ;


foreach (explode('/', $root) as $key => $items) {
	$tempArray[] = $items;
	if ($item == '' and $key == 0) {
		$currentPathArray[] = '<a href="home.php">Repository</a>';
	} else {
		$currentPathArray[] = '<a href="home.php?root=' . implode("/", $tempArray) . '">' . ucfirst($items) . '</a>';
	}
}
$currentPath = implode(" &raquo; ", $currentPathArray);

try 
{		    
	$resources = $WSRest->get('resources' . $root);
	//$response = $pest->post('login', $restData);
	
	//$screen .= "\n" . print_r($WSRest->last_response, true);
	$screen = '<ul>';
	foreach ($resources->resourceDescriptor as $contents) {
		switch ($contents['wsType']) {
			case 'folder':
				$screen .= '<li> <img src="'. WWW_ROOT .'images/icon-folder.png" align="absmiddle" ><a href="home.php?root=' . $contents['uriString'] . '">' . $contents->label . '</a></li>';
			break;
			case 'reportUnit';
				$screen .= '<li> <img src="'. WWW_ROOT .'images/icon-edit.gif" align="absmiddle" ><a href="viewReport.php?uri=' . $contents['uriString'] . '">' . $contents->label . '</a></li>';
			break;
			default:
				$screen .= '<li>' . $contents->label . ' (' . $contents['wsType'] . ')</li>';
		}
	    
	}
	$screen .= '</ul>';
} 
catch (Pest_Unauthorized $e) {
	// Check for a 401 (login timed out)	
	$WSRest->curl_opts[CURLOPT_HEADER] = true;
	$restData = array(
	  'j_username' => $_SESSION['username'],
	  'j_password' => $_SESSION['password']
	);
	
    try {		    
		$body = $WSRest->post('login', $restData);
		$response = $WSRest->last_response;
		if ($response['meta']['http_code'] == '200') {
			// Respose code 200 -> All OK
			// Extract the Cookie for further requests.
			preg_match('/^Set-Cookie: (.*?)$/sm', $body, $cookie);
			//Cookie: $Version=0; JSESSIONID=52E79BCEE51381DF32637EC69AD698AE; $Path=/jasperserver
			$_SESSION["JSCookie"] = '$Version=0; ' . str_replace('Path', '$Path', $cookie[1]);
			// Reload this page.
	        header("location: home.php");
	        exit();
		} else {
			header("location: logout.php");
			exit();
		}
	} 
	catch (Exception $e) {
	   	header("location: logout.php");
		exit();
	}
}
catch (Exception $e) 
{
    $screen .=  "Exception: <pre>" .  $e->getMessage() . "</pre>";
}

//$screen .= htmlentities(print_r($resources, true));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo $_SiteConfig['site']['title'] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="robots" content="noindex,nofollow">
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="<?php echo WWW_ROOT; ?>css/blueprint/screen.css" type="text/css" media="screen, projection">
  <link rel="stylesheet" href="<?php echo WWW_ROOT; ?>css/blueprint/print.css" type="text/css" media="print"> 
  <!--[if lt IE 8]>
    <link rel="stylesheet" href="<?php echo WWW_ROOT; ?>css/blueprint/ie.css" type="text/css" media="screen, projection">
  <![endif]-->
  <link rel="stylesheet" href="<?php echo WWW_ROOT; ?>css/blueprint/plugins/fancy-type/screen.css" type="text/css" media="screen, projection" />  
  <link rel="stylesheet" href="<?php echo WWW_ROOT; ?>css/blueprint/plugins/tabs/screen.css" type="text/css" media="screen,projection">
  <link rel="stylesheet" href="<?php echo WWW_ROOT; ?>css/blueprint/plugins/buttons/screen.css" type="text/css" media="screen,projection">
  <link href="<?php echo WWW_ROOT; ?>css/dropdown/themes/default/helper.css" media="screen" rel="stylesheet" type="text/css" media="screen, projection" />
  <link href="<?php echo WWW_ROOT; ?>css/dropdown/dropdown.limited.css" media="screen" rel="stylesheet" type="text/css" />
  <link href="<?php echo WWW_ROOT; ?>css/dropdown/themes/default/default.css" media="screen" rel="stylesheet" type="text/css" />
  <!--[if lt IE 7]>
   <style type="text/css" media="screen">
   body { behavior:url("<?php echo WWW_ROOT; ?>js/csshover.htc"); }
  </style>
  <![endif]-->
  <link href="<?php echo WWW_ROOT; ?>css/style.css" media="screen, projection"  rel="stylesheet" type="text/css" />

</head>
<body >
	<div class="container">
		<div id="header" class="span-24 last">
			<h1><a href="<?php echo WWW_ROOT; ?>" title="Home"><?php echo $_SiteConfig['site']['name'] ?></a></h1>								
		</div> 
        <div id="subheader" class="span-24 last">
          <h3 class="alt"><?php echo $_PageTitle ?></h3>
        </div>
		<div id="mainmenu" class="span-24 last">
			<ul id="nav" class="dropdown dropdown-horizontal">	
	    	<?php echo $_SiteConfig['user_menu'] ?>
	    	</ul>			
		</div> 
		<div id="maincontent" class="span-24 last"> 
			<ul class="tabs">
			<?php echo $_PageTabs; ?>
			</ul> 
    		<h3>Welcome to your repository (Using Rest Web Services)</h3>
			<h5><?php echo $currentPath; ?></h5>
			<?php echo $screen; ?>
   
		</div>
		<div id="footer" class="span-16"> 
			<!-- Footer Links -->
		</div> 
		<div class="alt span-7 last">
			<a href="http://www.jaspersoft.com">Jaspersoft.com</a>
		</div>
</div>
    </body>
</html>
