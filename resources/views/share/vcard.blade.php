<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>TTPush 踢一下-數位縣民</title>
    <meta property="al:ios:url" content=""/>
    <meta property="al:ios:app_store_id" content="1153458067"/>
    <meta property="al:ios:app_name" content="TTPush 踢一下"/>
    <meta property="og:title" content=""/>
    <meta property="og:description" content=""/>
    <meta property="og:image" content=""/>
    <meta property="og:url" content='{{$dynamic_link}}'/>
    <!-- stylesheets -->
    <link rel="stylesheet" href="/css/vcard.css" type="text/css" />
</head>

<body>
<!-- page start -->
<div class="page">

    <!-- 下載資訊 start -->
    <div class="ttpush_info">
        <div class="ttpush_logo">
            <img src="/images/active/share_logo.png">
        </div>

        <div class="topic">
            <div class="ttpush_mtopic">
                TTPush 踢一下
            </div>
            <div class="ttpush_stopic">
                現在就下載，即可領取臺東金幣！
            </div>
        </div>

        <div class="btn_d" id="btn_d" onclick="but_see()">立即安裝</div>
    </div>
    <!-- 下載資訊 end -->



    <!-- 數位縣民 start -->
    <div class="vc_box">
        <div class="vc_title">
            TTPush 數位縣民
        </div>

        <div class="vcqr">

            <!-- 數位縣民小卡 start -->
            <div class="vcard">
                <div class="vc_img"><img src="/images/vcard/img_specialcard_01.png"></div>
                <div class="vc_change"></div>
            </div>
            <!-- 數位縣民小卡 end -->

        </div>

        <div class="name">
            {{$user_name}}
        </div>

        <div class="preferential">
            @if($count > 0)
                <a href="/share/vpreferential">目前可享有{{$count}}個縣民優惠</a>
            @endif
            <div class="appeal">陸續新增中，趕快加入吧！</div>
        </div>

    </div>
    <!-- 數位縣民 end -->

</div>
<!-- page end -->


</body>

<script src="/js/zepto.min.js"></script>
<script>
    $(document).ready(function () {
        var documentWidth = $(window).width();
        if (documentWidth > 638) {
            documentWidth = 638;
        }
        var list = document.all;
        for (var i = 0; i < list.length; i++) {

            var frame = list[i];
            if (frame.style != undefined && (parseInt(frame.style.width) > documentWidth || frame.style.width == undefined)) {
                frame.style.width = '100%'
            }
            if (documentWidth < frame.width || frame.width == undefined) {
                frame.width = '100%'
            }
        }
    });

</script>
<script>
    function but_see()
    {
        window.location.href = "{{$dynamic_link}}";
    }
</script>

</html>
