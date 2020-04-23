<!DOCTYPE html>
<html lang="<?php echo e(config('app.locale')); ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="keywords" content="TTPush,踢一下">
    <meta name="description" content="TTPush管理平台TTPush APP的管理後台" />
    <title>TTPush-管理平台</title>
    <link rel="stylesheet" href="<?php echo e(mix('css/admin.css')); ?>">
</head>

<body>
<div id="app">
    <admin-component>
    </admin-component>
</div>
<script src="<?php echo e(mix('js/manifest.js')); ?>"></script>
<script src="<?php echo e(mix('js/vue.js')); ?>"></script>
<script src="<?php echo e(mix('js/element-ui.js')); ?>"></script>
<script src="<?php echo e(mix('js/admin.js')); ?>"></script>
</body>
</html>
