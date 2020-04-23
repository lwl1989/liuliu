<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>{{$goods_name}}</title>
    <meta property="al:ios:url" content=""/>
    <meta property="al:ios:app_store_id" content="1153458067"/>
    <meta property="al:ios:app_name" content="TTPush 踢一下"/>
    <meta property="og:title" content="{{$goods_name}}"/>
    <meta property="og:description" content="{{$goods_intro}}"/>
    <meta property="og:image"
          content="{{$goods_cover[0]['url']}}"/>
    <meta property="og:url" content='{{$dynamic_link}}'/>
    <!-- stylesheets -->
    <link rel="stylesheet" href="/css/goods_share.css" type="text/css"/>
    <link rel="stylesheet" href="/css/scenic.css" type="text/css">
    <link rel="stylesheet" href="/css/swiper-4.3.5.min.css" type="text/css">
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

        <div class="btn_d" onclick="but_see()">立即安裝</div>
    </div>
    <!-- 下載資訊 end -->

    <!-- 輪播照片 結束 -->
    <!-- 輪播照片 開始 -->
<!-- <div class="container coverflow">
		<div class="bs-example">
			<div id="S1" style="display:inline;">
        	<div class="swiper-container">
    			<div class="swiper-wrapper">
      				@foreach ($goods_cover as $user)
    <div class="swiper-slide"><img src="{{ $user['url'] }}"></div>
					@endforeach

        </div>
        <div class="swiper-pagination"></div>
          <div class="swiper-button-next"></div>
<div class="swiper-button-prev"></div>

      </div>
</div>
</div>
</div>-->
    <!-- 輪播照片 結束 -->
    <div id="tabsC">
        <div id="S1" style="display:inline;">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach ($goods_cover as $user)
                        <div class="swiper-slide"><img style="width: 100%;height: 75%;" src="{{ $user['url'] }}"></div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <div id="S2" style="display:none;">
            <div class="video_show">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/swoknkU08XY" frameborder="0"
                        name="vs" allowfullscreen></iframe>
            </div>
        </div>
    </div>


    <!-- 商品名稱區 開始 -->
    <div class="g_top">
        <div class="g_name"> {{$goods_name}}</div>
        <div class="g_t_bottom">
            <div class="g_o_price">原價 {{ $goods_price }}</div>
            @if($goods_stock>0)
                <div class="g_surplus">尚餘 {{ $goods_stock }} 件</div>
            @else
                <div class="g_surplus">庫存不足</div>
            @endif
        </div>
        <div class="g_t_bottom">
            <div class="g_price">兌換價 {{ $exchange_gold }} 金幣</div>
        </div>

        <!-- 備註區 開始 -->
        <div class="g_content">
            <table class="n_table">
            {{ $goods_remark }}
            <!--<tr>
					<td>1.備註內容備註內容</td>
				</tr>
				<tr>
					<td>2.備註內容備註內容備註內容備註內容備註內容備註內容備註內容備註內容備註內容備註內容備註內容備註內容</td>
				</tr>
				<tr>
					<td>3.備註內容備註內容備註內容備註內容備註內容備註內容備註內容</td>
				</tr>-->
            </table>
        </div>
        <!-- 備註區 結束 -->

    </div>
    <!-- 商品名稱區 結束 -->

    <div class="blank"></div>

    <!-- 商店資訊區 開始 -->
    <div class="g_area">
        <div class="s_data">
            <div class="s_left">
                <div class="s_name">{{ $shop_name }}</div>
                <div class="s_tel">{{ $tel }}</div>
                <div class="s_address">{{ $address }}</div>
            </div>
        </div>
    </div>
    <!-- 商店資訊區 結束 -->

    <div class="blank"></div>

    <!-- 商品描述區 開始 -->
    <div class="g_area">
        <div class="g_a_top">
            <div class="g_content" style="text-align:center;">

                <button class="btn_p" id="btn_p" onclick="but_see()">立即查看</button>

            </div>
        </div>
    </div>
    <!-- 商品描述區 結束 -->


</div>
<!-- page end -->


</body>


<script>
    function but_see() {

        window.location.href = "{{$dynamic_link}}";
    }

</script>
<script src="/js/swiper-4.5.3.min.js"></script>
<script src="/js/zepto.min.js"></script>
<script src="/js/speech.js"></script>

<script src="/js/jquery-1.11.0.min.js"></script>
<script>
    $(function () {
        var event = 'click';
        var lastLang = '';
        var playButtonObj = $('.speech > div[data-lang]');

        if ($.os.phone || $.os.tablet) {
            event = 'tap';
        }

        $('.speech a').on(event, function () {
            var parent = $(this).parent();
            var lang = parent.data('lang');
            var className = parent.attr('class');
            Speech.stop(lastLang);

            if (className === 'speech_item') { //代表播放
                var text = $('#' + lang).html();

                if (lastLang !== lang) {
                    playButtonObj.removeClass('speech_listen').addClass('speech_item');
                }

                if (text) {
                    if (!Speech.play(lang, text)) {
                        Speech.tip('您的環境不支持播放');
                    } else {
                        parent.removeClass('speech_item').addClass('speech_listen');
                    }
                }
            } else {
                if (lastLang !== lang) {
                    playButtonObj.removeClass('speech_item').addClass('speech_listen');
                }

                parent.removeClass('speech_listen').addClass('speech_item');
            }

            lastLang = lang;
        });

        $('#tabsF li').on(event, function () {
            var index = parseInt($(this).index()) + 1;

            $('#tabsC > div').hide();
            $('#S' + index).css('display', 'inline');

            $('#tabsF li').attr('id', '').eq($(this).index()).attr('id', 'current');
        });
    });

    new Swiper('.swiper-container', {
        pagination: {
            el: '.swiper-pagination',
            dynamicBullets: true,
        },
    });
</script>
