<?php
$images = [];
foreach(glob("images/previous_work/*") as $folderPath) {
	$path = explode('/', $folderPath);
	$folder = $path[count($path)-1];
	foreach(glob("images/previous_work/$folder/*") as $image) {
		$images[$folder][] = $image;
	}
}

?>
<link rel="stylesheet" href="assets/ImageViewer/imageviewer.css" />

<script id="viewImage-template" type="text/x-handlebars-template">
	<div style="background: white; display: block;">
		<h3 style="text-align:center;"><i class="info circle blue icon"></i> Images are able to be zoomed in/out with your scroll wheel!</h3>
	</div>

	<div id="image-gallery" style="width: 100%;">
    <div class="image-container"></div>
	    <img src="assets/ImageViewer/images/left.svg" class="prev"/>
	    <img src="assets/ImageViewer/images/right.svg"  class="next"/>
	    <div class="footer-info">
	        <span class="current"></span>/<span class="total"></span>
	    </div>
	</div>

	<button class="ui fluid button blue closeModal">Close</button>
</script>

<script src="assets/ImageViewer/imageviewer.js"></script>
<script>
var allImages = <?php echo json_encode($images); ?>;
var images    = {};
var curImageIdx,total,wrapper,curSpan,viewer;
</script>
