<?php
/**
 * Email Util
 * @author Preston Alvarado
 */
include '../Warehouse.php';

if (User('USERNAME') != 'focus' && User('USERNAME') !== 'admin') {
	die();
}


if ( ! function_exists('array_only'))
{
	/**
	* Get a subset of the items from the given array.
	*
	* @param  array  $array
	* @param  array  $keys
	* @return array
	*/
	function array_only($array, $keys)
	{
		return array_intersect_key($array, array_flip((array) $keys));
	}
}

function input_only($keys)
{
	return array_only($_POST, $keys) + array_fill_keys($keys, '');
}

function old_input($name, $default = '')
{
	if(array_key_exists($name, $_SESSION['old_input'])) {
		return $_SESSION['old_input'][$name];
	}
	return $default;
}

function ping($server, $port, $timeout = 3)
{
	$pinged = false;
	if($fp = fsockopen($server, $port, $errcode, $errstr, $timeout)) {
	   $pinged = true;
	}
	fclose($fp);
	return $pinged;
}

$default_body = "This is a test email sent from Focus.\r\n\r\nSite Title: {$FocusTitle}\r\nFocus Version: {$FocusVersion}\r\nDatabase Type: {$DatabaseType}\r\nPHP Version: " . phpversion();

if(! isset($_SESSION['old_input'])) {
	$_SESSION['old_input'] = array();
}

//Process email request
if(isset($_POST['submit']) && $_POST['submit'] == 'test_email') {
	$_SESSION['old_input'] = $_POST;

	$input = input_only($_POST, array('email_to', 'email_from', 'subject', 'body'));

	try {
		Email::send($input['subject'], $input['body'], $input['email_to'], $input['email_to'], $input['email_from'], $_Mail['from_name']);
		$_SESSION['old_input']['sent'] = true; //haha this hack.. oh man do I miss having flash session data...
	} catch(Exception $e) {
		$_SESSION['error_message'] = $e->getMessage();
	}

	header('Location: ' . $_SERVER['HTTP_REFERER']);
	die;
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Focus Email Utility</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css">
  <!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
  <![endif]-->
</head>
<body style="margin-top:20px;">
	<div class="container">
		<div class="well">
		<div>
			<h3 style="margin-top:0;">Email Settings</h3>
			<table class="table">
				<tbody>
					<?php
					foreach($_Mail AS $setting => $value):
						if($setting == 'server') {
							if(ping($_Mail['server'], $_Mail['smtp_port'])) {
								$value = '<span class="label label-success">' . $value . '</span>';
							} else {
								$value = '<span class="label label-danger">' . $value . '</span>';
							}
						}
					?>
					<tr>
						<td><?php echo ucwords(str_replace('_', ' ', $setting)); ?></td>
						<td><?php echo $value; ?></td>
					</tr>
					<?php
					endforeach;
					?>
				</tbody>
			</table>
		</div>
		<h3>Send Email</h3>
		<form role="form" method="post">
			<div class="form-group">
				<p class="help-block">This utility can be used to test the email settings..</p>
				<?php
				if(isset($_SESSION['old_input']['sent']) && $_SESSION['old_input']['sent'] === true) {
					?>
					<div class="alert alert-success">Email has been sent to <em><?php echo old_input('email_to'); ?></em></div>
					<?php
				}
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
			<div class="form-group">
				<label for="email_to">Email to:</label>
			 	<input type="email" class="form-control" id="email_to" name="email_to" value="<?php echo old_input('email_to'); ?>">
			</div>
			<div class="form-group">
				<label for="email_from">Email from:</label>
				<input type="email" class="form-control" id="email_from" name="email_from" value="<?php echo old_input('email_from', $_Mail['from']); ?>">
			</div>
			<div class="form-group">
				<label for="subject">Subject:</label>
				<input type="text" class="form-control" id="subject" name="subject" value="<?php echo old_input('subject', 'Test Email'); ?>">
			</div>
			<div class="form-group">
				<label for="body">Body:</label>
				<textarea class="form-control" id="body" name="body" rows="10"><?php echo old_input('body', $default_body); ?></textarea>
			</div>
			<button type="submit" name="submit" value="test_email" class="btn btn-default">Send Email</button>

		  </form>
		</div>
	</div>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
</body>
</html>
<?php
$_SESSION['old_input'] = array();
unset($_SESSION['error_message']);
?>