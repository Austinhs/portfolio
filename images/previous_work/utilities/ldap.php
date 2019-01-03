<?php
/**
* LDAP Util
* @author Preston Alvarado
*/
include '../Warehouse.php';

if (User('USERNAME') != 'focus' && User('USERNAME') !== 'admin') {
	die();
}

$username = $_POST['username'];
$password = $_POST['password'];
?>
<!DOCTYPE html>
<html>
<head>
	<title>LDAP Utility</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->
</head>
<body style="margin-top:20px;">
	<div class="container">
		<div class="well">
			<div>
				<h3 style="margin-top:0;">LDAP Settings</h3>
				<table class="table">
					<tbody>
						<?php
						foreach($_LDAP AS $setting => $value):
						?>
						<tr>
							<td><strong><?php echo ucwords(str_replace('_', ' ', $setting)); ?></strong></td>
							<td><?php echo $value; ?></td>
						</tr>
						<?php
						endforeach;
						?>
					</tbody>
				</table>
			</div>
			<h3>Auth Test</h3>
			<form role="form" method="post">
				<div class="form-group">
					<p class="help-block">This utility can be used to test the LDAP settings..</p>
					<?php
					if(isset($_SESSION['error_message'])) {
						?>
						<div class="alert alert-danger">
							Failed to send email.
							<p><?php echo $_SESSION['error_message']; ?></p>
						</div>
						<?php
					}
					?>
				</div>
<?php
if (isset($_POST['username']) && isset($_POST['password'])) {
?>
				<h4>Output</h4>
				<div class="well well-sm">
<?php
ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
$ldap = ldap_connect($_LDAP['host'], $_LDAP['port']);
if ($ldap === false) {
		echo "LDAP connection failed.<br />";
} else {
	if (isset($_LDAP['protocol'])) {
		echo "Setting protocol version to <strong><i>{$_LDAP['protocol']}</i></strong><br />";
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, $_LDAP['protocol']);
	}

	if ($_LDAP['bind_type'] == 'direct') {
		echo "Binding as ".$username.$_LDAP['suffix']." with password {$password}";
		$bind = ldap_bind($ldap, $username.$_LDAP['suffix'], $password);
		if ($bind) {
			echo '<span class="label label-success">Success</span><br />';
		} else {
			echo '<span class="label label-danger">Failed</span><br />';
			echo '<strong>Error</strong>: '.ldap_errno($ldap).' - '.ldap_error($ldap).'<br />';
		}
	} elseif ($_LDAP['bind_type'] == 'search') {
		if(isset($_LDAP['bindDN'])) {
			echo "Binding as <strong><i>{$_LDAP['bindDN']}</i></strong>... ";
			$bind = ldap_bind($ldap, $_LDAP['bindDN'], $_LDAP['bindPW']);
			if ($bind) {
				echo '<span class="label label-success">Success</span>';
			} else {
				echo '<span class="label label-danger">Failed</span>';
			}
			echo '<br />';
		} else {
			echo("Binding as <strong>anonymous</strong>!<br>");
			$bind = ldap_bind($ldap);
		}

		if ($bind) {
			$filter = "(&(objectClass=inetOrgPerson)(uid=".str_replace("''","'",$_REQUEST['USERNAME'])."))";
			if (isset($_LDAP['filter'])) {
				$filter = str_replace('{username}', $username, $_LDAP['filter']);
			}
			echo "Search Filter: <strong><i>{$filter}</i></strong><br />";
			echo "Executing searching for <strong><i>{$username}</i></strong>... ";
			$search = ldap_search($ldap, $_LDAP['suffix'], $filter);
			if ($search) {
				echo '<span class="label label-success">Success</span><br />';
			} else {
				echo '<span class="label label-danger">Failed</span><br />';
				echo '<strong>Error</strong>: '.ldap_errno($ldap).' - '.ldap_error($ldap).'<br />';
			}
		}

		if ($search) {
			echo "Fetching search results... ";
			$info = ldap_get_entries($ldap, $search);
			$count = 0;
			$userdn = '';
			if ($info) {
				echo '<span class="label label-success">Success</span><br />';
				$count = $info['count'];
				$userdn = $info[0]['dn'];
			} else {
				echo '<span class="label label-danger">Failed</span><br />';
				echo '<strong>Error</strong>: '.ldap_errno($ldap).' - '.ldap_error($ldap).'<br />';
			}
		}

		if ($info['count'] == 0) {
			echo '<span class="label label-danger">Can\'t find LDAP user.</span><br />';
		} else {
			echo "User DN: <strong><i>{$userdn}<i></strong><br />";

			echo "Binding user with password <strong><i>{$password}</i></strong>... ";
			$auth = ldap_bind($ldap, str_replace("\'", "'", $userdn), $password);
			if ($auth === false) {
				echo '<span class="label label-danger">Failed</span><br />';
				echo '<strong>Error</strong>: '.ldap_errno($ldap).' - '.ldap_error($ldap).'<br />';
			} else {
				echo '<span class="label label-success">Success</span><br />';
			}
		}

	} else {
		echo 'Unknown LDAP bind type.';
	}
}

?>
				</div>
<?php
}
?>
				<div class="form-group">
					<label for="username">Username:</label>
					<input type="text" class="form-control" id="username" name="username" value="">
				</div>
				<div class="form-group">
					<label for="password">Password:</label>
					<input type="text" class="form-control" id="password" name="password" value="">
				</div>
				<button type="submit" name="submit" value="test_email" class="btn btn-default">Test</button>
				<br />
			</form>
		</div>
	</div>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
</body>
</html>
<?php
$_SESSION['old_input'] = array();
unset($_SESSION['error_message']);
?>
