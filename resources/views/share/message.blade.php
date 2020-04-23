<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>{{$name}}</title>
    <meta property="al:ios:url" content=""/>
    <meta property="al:ios:app_store_id" content="1153458067"/>
    <meta property="al:ios:app_name" content="TTPush 踢一下"/>
    <meta property="og:title" content="{{$name}}"/>
    <meta property="og:description" content="{{$content}}"/>
    <meta property="og:image"
          content="{{$cover}}"/>
    <meta property="og:url" content='{{$dynamic_link}}'/>

    <!-- stylesheets -->
    <link rel="stylesheet" href="/css/active.css" type="text/css"><!--this page-->
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


    <!-- main start -->
    <div class="main">

        <!-- 封面 開始 -->
        <div class="cover">

            <!-- 圖片封面 開始 -->
            <div class="cover_show">
                <div class="csp" style="background-image:url({{$cover}});"></div>
            </div>
            <!-- 圖片封面 結束 -->

        </div>
        <!-- 封面 結束 -->


        <!-- 標題、日期與分享 開始 -->
        <div class="t_caption"><h1>{{$name}}</h1></div>

        <div class="t_ds">
            <div class="t_date">{{$create_time}}</div>
        </div>
        <!-- 標題、日期與分享 結束 -->


        <!-- 內文 開始 -->
        <div class="t_content">

            <p>
                {{$content}}</p>

        </div>
        <!-- 內文 結束 -->


        <!-- 按扭區 開始 -->
        <div class="btn_box">

            <button class="btn_p" onclick="but_see()">立即查看</button>

        </div>
        <!-- 按扭區 結束 -->

    </div>
    <!-- main end -->

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
    function but_see() {

        window.location.href = "{{$dynamic_link}}";
    }


</script>
</html>
