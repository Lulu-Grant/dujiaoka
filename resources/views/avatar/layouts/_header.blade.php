<head>
    <meta charset="utf-8" />
    <title>{{ isset($page_title) ? $page_title : '' }} | {{ dujiaoka_config_get('title') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Keywords" content="{{ dujiaoka_config_get('keywords') }}">
    <meta name="Description" content="{{ dujiaoka_config_get('description') }}">
    @if(\request()->getScheme() == "https")
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @endif
    <link rel="shortcut icon" href="/assets/avatar/images/favicon.ico">
    <link href="/assets/avatar/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/avatar/css/icons.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/avatar/css/base.css" rel="stylesheet" type="text/css">
    <link href="/assets/avatar/css/common.css" rel="stylesheet" type="text/css">
    <link href="/assets/avatar/css/index.css" rel="stylesheet" type="text/css">
    <link href="/assets/avatar/css/avatar.css" rel="stylesheet" type="text/css">
</head>
