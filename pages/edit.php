<?php

    startSession();

    $imgFileName = $_SESSION["img_file_name"];
    $imgFilePath = "../uploads/" . $imgFileName;
    if (!file_exists($imgFilePath)) {
        redirectTo("/upload?error=1");
    }

    use Intervention\Image\ImageManagerStatic as Image;

    $img = Image::make($imgFilePath);
    $img->orientate();    

    $imgExif = $img->exif();
    unset($imgExif["FileName"]);

    if (!empty($imgExif["GPSLatitude"]) && !empty($imgExif["GPSLongitude"]) && !empty($imgExif["GPSLatitudeRef"]) && !empty($imgExif["GPSLongitudeRef"])) {
        $gpsLatitude = exifToGPS($imgExif["GPSLatitude"], $imgExif["GPSLatitudeRef"]);
        $gpsLongitude = exifToGPS($imgExif["GPSLongitude"], $imgExif["GPSLongitudeRef"]);
        $gpsQueryString = $gpsLatitude . "," . $gpsLongitude;
    }

    $summaryData = array();
    if (isset($imgExif["FileSize"]))
        $summaryData["File Size"] = formatBytes($imgExif["FileSize"], 2);
    if (isset($imgExif["MimeType"]))
        $summaryData["File Type"] = $imgExif["MimeType"];
    if (isset($imgExif["ImageWidth"]))
        $summaryData["Resolution"] = $imgExif["ImageWidth"] . " x " . $imgExif["ImageLength"] . " px";
    if (isset($imgExif["DateTime"]))
        $summaryData["Created At"] = date_format(date_create_from_format("Y:m:d H:i:s", $imgExif["DateTime"]), "d.m.Y H:i:s");
    if (isset($imgExif["Make"]) && isset($imgExif["Model"]))
        $summaryData["Device"] = strtoupper($imgExif["Make"] . " " . $imgExif["Model"]);
    if (isset($imgExif["Software"]))
        $summaryData["Software"] = strtoupper($imgExif["Software"]);
    if (isset($imgExif["DigitalZoomRatio"]))
        $summaryData["Zoom Ratio"] = $imgExif["DigitalZoomRatio"];
    if (isset($imgExif["Flash"]))
        $summaryData["Flash"] = ($imgExif["Flash"] ? "Enabled" : "Disabled");

    $imgDataUrl = $img->encode('data-url');

?>
<main>
    <div class="row">
        <div class="col-12 col-md-6 ps-0">
            <div class="card mb-3" style="min-height:500px;">
                <div class="card-header">
                    <h2 class="card-title h5">Uploaded Image</h2>
                </div>
                <div class="card-body display-image">
                    <img src="<?php echo $imgDataUrl; ?>">
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 pe-0">
            <div class="card mb-3" style="min-height:500px;">
                <div class="card-header">
                    <h2 class="card-title h5">Summary</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <tbody>
                            <?php if (empty($summaryData)) echo "<tr><td>The image has no Exif data.</td></tr>"; ?>
                            <?php foreach ($summaryData as $property => $value) { ?>
                                <tr>
                                    <td><?php echo $property; ?></td>
                                    <td><?php echo $value; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <a href="/api/download.php" download="image.<?php echo substr($imgFileName, strpos($imgFileName, ".") + 1);  ?>"><button type="button" class="btn btn-primary mt-2 button-center">Remove Exif Data & Download</button></a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="card mb-3">
            <div class="card-header">
                <h2 class="card-title h5">AI Generated Alternative <span class="ms-2 display-6 blockquote-footer">Powered by OpenAI</span></h2>
            </div>
            <div class="card-body display-image">
                <p id="ai-alt-label" class="text-center d-none">Something went wrong. Please try again.</p>
                <img id="ai-alt-img" src="" class="d-none">
                <div id="ai-alt-loader" class="d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="d-flex justify-content-center align-items-center">
                    <button id="ai-alt-refresh" class="btn btn-primary mt-3" type="button" disabled>Refresh</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <?php if (!empty($imgExif)) { ?>
            <?php if (!empty($gpsQueryString)) { ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h2 class="card-title h5">Location Image Was Taken</h2>
                </div>
                <iframe width="100%" height="500" style="border:0" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" 
                    src="https://www.google.com/maps/embed/v1/place?q=<?php echo $gpsQueryString; ?>&key=<?php echo GOOGLE_MAPS_EMBED_API_KEY; ?>&q=">
                </iframe>
            </div>
            <?php } ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h2 class="card-title h5">All Exif Meta Data</h2>
                </div>
                <div class="card-body table-scrollable">
                    <table class="table table-responsive table-striped table-hover">
                    <tbody>
                        <?php foreach ($imgExif as $property => $value) { ?>
                        <tr>
                            <td><?php echo $property; ?></td>
                            <td>
                            <?php if (is_array($value)) { ?>
                                <table class="table table-sm">
                                <tbody>
                                    <?php foreach ($value as $subproperty => $subvalue) { ?>
                                    <tr>
                                        <td><?php echo $subproperty; ?></td>
                                        <td><?php echo $subvalue; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                </table>
                            <?php } else { ?>
                                <?php echo $value; ?>
                            <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>

    <hr class="col-3 col-md-2 my-5">

    <h1 class="h2">Upload another image</h1>

    <form class="mb-5">
        <div class="form-group drop-zone" id="drop-zone">
            <input type="file" accept=".jpg, .jpeg, .png" class="form-control-file" id="image-upload">
            <div class="text">Drag and drop an image file here or <a href="#" id="browse-button">click to select a file</a></div>
            <img class="thumbnail" src="#" alt="">
            <button type="button" class="btn btn-secondary browse-button" id="upload-button">Upload</button>
            <button type="button" class="btn btn-secondary clear-button" id="clear-button">Cancel</button>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>
    </form>

    <hr class="mt-5 col-3 col-md-2 mb-5">

    <div class="row g-5">
        <div class="col-md-6">
            <h2 class="h3">Why <b>Pixel</b>Wizard?</h2>
            <ul class="icon-list list-check ps-0">
                <li class="d-flex align-items-start mb-1">Edit images quickly and easily</li>
                <li class="d-flex align-items-start mb-1">Protect your online privacy</li>
                <li class="d-flex align-items-start mb-1">View and analyze Exif data</li>
                <li class="d-flex align-items-start mb-1">Customize your images with filters</li>
                <li class="d-flex align-items-start mb-1">Get high-quality results</li>
            </ul>
        </div>

        <div class="col-md-6">
            <h2 class="h4">How Exif data threatens your online privacy?</h3>
                <p>Exif data is invisible information stored in your photos that can reveal details about you and your device, such as GPS location and personal information. To protect your privacy, be aware of the Exif data in your images and remove sensitive information before sharing them online.</p>
                <ul class="icon-list list-direct ps-0">
                    <li class="d-flex align-items-start mb-1"><a href="https://www.kaspersky.com/blog/exif-privacy/13356/" target="_blank">Kaspersky Blog</a></li>
                    <li class="d-flex align-items-start mb-1"><a href="https://udspace.udel.edu/server/api/core/bitstreams/f2a04ac2-e998-49a4-b943-c26659c74fe4/content" target="_blank">Dirty Metadata (University of Delaware)</a></li>
                    <li class="d-flex align-items-start mb-1"><a href="https://www.comparitech.com/blog/vpn-privacy/exif-metadata-privacy/" target="_blank">Worth a thousand data points (Comparitech)</a></li>
                    <li class="d-flex align-items-start mb-1"><a href="https://www.consumerreports.org/privacy/what-can-you-tell-from-photo-exif-data-a2386546443/" target="_blank">Exposes Your Personal Information (Consumer Reports)</a></li>
                </ul>
        </div>
    </div>

</main>