<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <?php echo $__env->make('includes.docs.api.head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </head>
    <body>

        <div class="container">
            <div class="row">
                <div class="col-3" id="sidebar">
                    <div class="column-content">
                        <div class="search-header">
                            <img src="/assets/docs/api/img/f2m2_logo.svg" class="logo" alt="Logo" />
                            <input id="search" type="text" placeholder="Search">
                        </div>
                        <ul id="navigation">

                            <li><a href="#introduction">Introduction</a></li>

                            

                            <li>
                                <a href="#Wx">Wx</a>
                                <ul>
									<li><a href="#Wx_login">login</a></li>

									<li><a href="#Wx_getToken">getToken</a></li>
</ul>
                            </li>


                            <li>
                                <a href="#Tags">Tags</a>
                                <ul>
									<li><a href="#Tags_getAll">getAll</a></li>

									<li><a href="#Tags_getList">getList</a></li>
</ul>
                            </li>


                            <li>
                                <a href="#ImageUpload">ImageUpload</a>
                                <ul>
									<li><a href="#ImageUpload_upload">upload</a></li>
</ul>
                            </li>


                        </ul>
                    </div>
                </div>
                <div class="col-9" id="main-content">

                    <div class="column-content">

                        <?php echo $__env->make('includes.docs.api.introduction', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <hr />

                                                

                                                <a href="#" class="waypoint" name="Wx"></a>
                        <h2>Wx</h2>
                        <p></p>

                        
                        <a href="#" class="waypoint" name="Wx_login"></a>
                        <div class="endpoint-header">
                            <ul>
                            <li><h2>GET</h2></li>
                            <li><h3>login</h3></li>
                            <li>api/wx/login</li>
                          </ul>
                        </div>

                        <div>
                          <p class="endpoint-short-desc"></p>
                        </div>
                       <!--  <div class="parameter-header">
                             <p class="endpoint-long-desc"></p>
                        </div> -->
                        <form class="api-explorer-form" uri="api/wx/login" type="GET">
                          <div class="endpoint-paramenters">
                            
                          </div>
                           <div class="generate-response" >
                              <!-- <input type="hidden" name="_method" value="GET"> -->
                              <input type="submit" class="generate-response-btn" value="Generate Example Response">
                          </div>
                        </form>
                        <hr>

                        <a href="#" class="waypoint" name="Wx_getToken"></a>
                        <div class="endpoint-header">
                            <ul>
                            <li><h2>GET</h2></li>
                            <li><h3>getToken</h3></li>
                            <li>api/wx/token</li>
                          </ul>
                        </div>

                        <div>
                          <p class="endpoint-short-desc"></p>
                        </div>
                       <!--  <div class="parameter-header">
                             <p class="endpoint-long-desc"></p>
                        </div> -->
                        <form class="api-explorer-form" uri="api/wx/token" type="GET">
                          <div class="endpoint-paramenters">
                            
                          </div>
                           <div class="generate-response" >
                              <!-- <input type="hidden" name="_method" value="GET"> -->
                              <input type="submit" class="generate-response-btn" value="Generate Example Response">
                          </div>
                        </form>
                        <hr>
                        

                                                <a href="#" class="waypoint" name="Tags"></a>
                        <h2>Tags</h2>
                        <p></p>

                        
                        <a href="#" class="waypoint" name="Tags_getAll"></a>
                        <div class="endpoint-header">
                            <ul>
                            <li><h2>GET</h2></li>
                            <li><h3>getAll</h3></li>
                            <li>api/tags/all</li>
                          </ul>
                        </div>

                        <div>
                          <p class="endpoint-short-desc"></p>
                        </div>
                       <!--  <div class="parameter-header">
                             <p class="endpoint-long-desc"></p>
                        </div> -->
                        <form class="api-explorer-form" uri="api/tags/all" type="GET">
                          <div class="endpoint-paramenters">
                            
                          </div>
                           <div class="generate-response" >
                              <!-- <input type="hidden" name="_method" value="GET"> -->
                              <input type="submit" class="generate-response-btn" value="Generate Example Response">
                          </div>
                        </form>
                        <hr>

                        <a href="#" class="waypoint" name="Tags_getList"></a>
                        <div class="endpoint-header">
                            <ul>
                            <li><h2>GET</h2></li>
                            <li><h3>getList</h3></li>
                            <li>api/user/tags</li>
                          </ul>
                        </div>

                        <div>
                          <p class="endpoint-short-desc"></p>
                        </div>
                       <!--  <div class="parameter-header">
                             <p class="endpoint-long-desc"></p>
                        </div> -->
                        <form class="api-explorer-form" uri="api/user/tags" type="GET">
                          <div class="endpoint-paramenters">
                            
                          </div>
                           <div class="generate-response" >
                              <!-- <input type="hidden" name="_method" value="GET"> -->
                              <input type="submit" class="generate-response-btn" value="Generate Example Response">
                          </div>
                        </form>
                        <hr>
                        

                                                <a href="#" class="waypoint" name="ImageUpload"></a>
                        <h2>ImageUpload</h2>
                        <p></p>

                        
                        <a href="#" class="waypoint" name="ImageUpload_upload"></a>
                        <div class="endpoint-header">
                            <ul>
                            <li><h2>POST</h2></li>
                            <li><h3>upload</h3></li>
                            <li>api/static/user/avatar</li>
                          </ul>
                        </div>

                        <div>
                          <p class="endpoint-short-desc"></p>
                        </div>
                       <!--  <div class="parameter-header">
                             <p class="endpoint-long-desc"></p>
                        </div> -->
                        <form class="api-explorer-form" uri="api/static/user/avatar" type="POST">
                          <div class="endpoint-paramenters">
                            
                          </div>
                           <div class="generate-response" >
                              <!-- <input type="hidden" name="_method" value="POST"> -->
                              <input type="submit" class="generate-response-btn" value="Generate Example Response">
                          </div>
                        </form>
                        <hr>


                    </div>
                </div>
            </div>
        </div>


    </body>
</html>
<?php /**PATH D:\www\resources\views/layouts/docs/api/default.blade.php ENDPATH**/ ?>