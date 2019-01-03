<html>
	<head>
		<title>Portfolio</title>
		<meta charset="utf-8">
		<meta name="description" content="Austin Stanfield Programming Portfolio">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/1.11.8/semantic.min.css"/>
		<link href="https://cdn.quilljs.com/1.3.5/quill.snow.css" rel="stylesheet">
		<link rel="stylesheet" href="app.css" />
		<link rel="icon" type="image/x-icon" href="images/favicon.ico"/>
	</head>
	<body>
		<div id="profileDiv">
			<div id="particles-js">
				<img id="linkedInProfileIcon" src="images/my_linkedin_pic.jpeg" />
				<span id="jobTitle"></span>
			</div>
		</div>

		<div class="ui secondary pointing menu">
			<a id="homeTab" class="ui item menuItem" onClick="">
				Home
			</a>
			<a id="previous_workTab" class="ui item menuItem" onClick="">
				Previous Work
			</a>
			<a id="contactTab" class="ui item menuItem" onClick="">
				Contact
			</a>

			<div class="right menu">
				<a href="https://www.linkedin.com/in/austin-stanfield-297992107/" target="_blank" class="ui item menuItem">
					LinkedIn Profile
				</a>
			</div>
		</div>

		<div id="pageContainer">
			<div class="ui active inverted dimmer loadingDiv">
				<div class="ui text loader">Loading</div>
			</div>
		</div>
	</body>
</html>

<!-- Modal -->
<div id="modal" class="ui modal"></div>

<!-- Start handlebar templates, to be loaded with includeHandlebarTemplates.js -->
<!-- <div handlebar-template="templates/home.html"></div>
<div handlebar-template="templates/contact.html"></div>
<div handlebar-template="templates/previous_work.html"></div> -->
<!--  -->


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/1.11.8/semantic.min.js"></script>
<script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.5/quill.js"></script>
<!-- <script src="assets/includeHandlebarTemplates.js"></script> -->
<script src="assets/tableImageViewer.js"></script>
<script src="assets/particalEffect.js"></script>
<script src="assets/handlebars-v4.0.11.js"></script>

<!-- PHP handlebar -->
<?php
	$templates = [];
	foreach(glob("templates/*") as $template) {
		require_once($template);
		$explode = explode('/', $template);
		$templates[] = $explode[count($explode)-1];
	}
?>

<script>var templates = <?php echo json_encode($templates); ?>;</script>

<script src="app.js"></script>
