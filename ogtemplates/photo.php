<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta property="og:site_name" content="<?php echo $sitename; ?>" /> <!-- title with link in the preview -->
    <meta property="og:url" content="<?php echo $twitterlink; ?>">
    <meta property="og:description" content="<?php echo $description; ?>">

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo $title; ?>" />
    <meta name="twitter:image" content="<?php echo $photourl; ?>" />

</head>

<body>
    if you see this you took a turn around the wrong corner.
    i mean really, this page is only meant for bots and not for humans.
    Please proceed to <a href="<?php echo $twitterlink; ?>"><?php echo $twitterlink; ?></a>
</body>

</html>