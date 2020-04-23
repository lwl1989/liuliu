<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>TTPush 數位縣民優惠詳情</title>
    <meta property="al:ios:url" content=""/>
    <meta property="al:ios:app_store_id" content="1153458067"/>
    <meta property="al:ios:app_name" content="TTPush 踢一下"/>
    <meta property="og:title" content=""/>
    <meta property="og:description" content=""/>
    <meta property="og:image" content=""/>
    <meta property="og:url" content='{{$dynamic_link}}'/>
    <!-- stylesheets -->
    <link rel="stylesheet" href="/css/vpreferential_details.css" type="text/css">
    <link rel="stylesheet" href="/css/swiper.min.css">
</head>

<body>
<!-- page start -->
<div class="page">
    <!-- 照片區 start -->
    <div id="tabsC">
        <div id="S1" style="display:inline;">

            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach ($lists['cover'] as $list)
                        <div class="swiper-slide"><img src="{{ $list['url'] }}"></div>
                    @endforeach
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>

        </div>
    </div>
    <!-- 照片區 end -->


    <!-- 數位縣民優惠名稱區 start -->
    <div class="scenic_spots">
        {{$lists['title']}}
    </div>
    <!-- 數位縣民優惠名稱區 end -->


    <!-- 內容區 start -->
    <div class="main">
	    <?php echo $lists['desc'];?>
    </div>
    <!-- 內容區 end -->

</div>
<!-- page end -->

</body>

<script src="/js/swiper-4.5.3.min.js"></script>
<!-- Initialize Swiper -->
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

    new Swiper('.swiper-container', {
        pagination: {
            el: '.swiper-pagination',
            dynamicBullets: true,
        },
    });
</script>

</html>
