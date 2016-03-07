<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic Page Needs
  ================================================== -->
  <meta charset="utf-8">
  <!--[if IE]><meta http-equiv="x-ua-compatible" content="IE=9" /><![endif]-->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Default Theme</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons
  ================================================== -->
  <link rel="shortcut icon" href="/images/default_theme/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="/images/default_theme/apple-touch-icon.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/images/default_theme/apple-touch-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/images/default_theme/apple-touch-icon-114x114.png">

  <link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900,100italic,300italic,400italic,700italic,900italic' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,700,300,600,800,400' rel='stylesheet' type='text/css'>

  <block type="css" sequence="outputFilter" consolidate="false"></block>
  <block type="js" sequence="outputFilter" group="head" consolidate="false"></block>

  {css('default_theme/bootstrap.min.css')}
  {css('fraym/font-awesome.min.css')}
  {css('default_theme/owl.carousel.css')}
  {css('default_theme/owl.theme.css')}
  {css('default_theme/style.css')}
  {css('default_theme/responsive.css')}

  {js('default_theme/modernizr.custom.js', 'head')}

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>

<!-- Navigation
==========================================-->
<nav id="tf-menu" class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.html">Spirit8</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <block type="content">
        <view id="nav" description="{_('Add menu here.')}" renderElement="false">
          <placeholder>
            <![CDATA[
            <ul class="nav navbar-nav navbar-right">
              <li><a href="#tf-home" class="page-scroll">Home</a></li>
              <li><a href="#tf-about" class="page-scroll">About</a></li>
              <li><a href="#tf-team" class="page-scroll">Team</a></li>
              <li><a href="#tf-services" class="page-scroll">Services</a></li>
              <li><a href="#tf-works" class="page-scroll">Portfolio</a></li>
              <li><a href="#tf-testimonials" class="page-scroll">Testimonials</a></li>
              <li><a href="#tf-contact" class="page-scroll">Contact</a></li>
            </ul>
            ]]>
          </placeholder>
        </view>
      </block>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<!-- Home Page
==========================================-->

<block type="content">
  <view id="tf-home-wrapper" renderElement="false" description="{_('Add welcome hero element here.')}" editStyle="min-height: 400px;" actionBarStyle="top: 185px; padding: 0; position: absolute; width: 100%;">
    <placeholder>
      <![CDATA[
      <div id="tf-home" class="text-center">
        <div class="overlay">
          <div class="content">
            <h1>Welcome on <strong><span class="color">your website</span></strong></h1>
            <p class="lead">Lorem ipsum dolor sit amet <strong>ipsum dolor sit</strong> ipsum dolor sit <strong>ipsum dolor sit</strong></p>
            <a href="#tf-about" class="fa fa-angle-down page-scroll"></a>
          </div>
        </div>
      </div>
      ]]>
    </placeholder>
  </view>
</block>


<!-- About Us Page
==========================================-->
<block type="content">
  <view id="tf-about-wrapper" renderElement="false" description="{_('Add about text element here.')}">
    <placeholder>
      <![CDATA[
      <div id="tf-about">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <img src="/images/default_theme/02.png" class="img-responsive">
            </div>
            <div class="col-md-6">
              <div class="about-text">
                <div class="section-title">
                  <h4>About us</h4>
                  <h2>Some words <strong>about us</strong></h2>
                  <hr>
                  <div class="clearfix"></div>
                </div>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.</p>
                <ul>
                  <li>
                    <span class="fa fa-dot-circle-o"></span>
                    <strong>Lorem ipsum</strong> - <em>Lorem ipsum dolor sit amet</em>
                  </li>
                  <li>
                    <span class="fa fa-dot-circle-o"></span>
                    <strong>Lorem ipsum</strong> - <em>Lorem ipsum dolor sit amet</em>
                  </li>
                  <li>
                    <span class="fa fa-dot-circle-o"></span>
                    <strong>Lorem ipsum</strong> - <em>Lorem ipsum dolor sit amet</em>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      ]]>
    </placeholder>
  </view>
</block>

<!-- Team Page
==========================================-->
<div id="tf-team" class="text-center">
  <div class="overlay">
    <div class="container">
      <div class="section-title center">
        <h2>Meet <strong>our team</strong></h2>
        <div class="line">
          <hr>
        </div>
      </div>

      <div id="team" class="owl-carousel owl-theme row">
        <div class="item">
          <div class="thumbnail">
            <img src="/images/default_theme/team/01.jpg" alt="..." class="img-circle team-img">
            <div class="caption">
              <h3>Jenn Gwapa</h3>
              <p>CEO / Founder</p>
              <p>Do not seek to change what has come before. Seek to create that which has not.</p>
            </div>
          </div>
        </div>

        <div class="item">
          <div class="thumbnail">
            <img src="/images/default_theme/team/02.jpg" alt="..." class="img-circle team-img">
            <div class="caption">
              <h3>Jenn Gwapa</h3>
              <p>CEO / Founder</p>
              <p>Do not seek to change what has come before. Seek to create that which has not.</p>
            </div>
          </div>
        </div>

        <div class="item">
          <div class="thumbnail">
            <img src="/images/default_theme/team/03.jpg" alt="..." class="img-circle team-img">
            <div class="caption">
              <h3>Jenn Gwapa</h3>
              <p>CEO / Founder</p>
              <p>Do not seek to change what has come before. Seek to create that which has not.</p>
            </div>
          </div>
        </div>

        <div class="item">
          <div class="thumbnail">
            <img src="/images/default_theme/team/04.jpg" alt="..." class="img-circle team-img">
            <div class="caption">
              <h3>Jenn Gwapa</h3>
              <p>CEO / Founder</p>
              <p>Do not seek to change what has come before. Seek to create that which has not.</p>
            </div>
          </div>
        </div>

        <div class="item">
          <div class="thumbnail">
            <img src="/images/default_theme/team/04.jpg" alt="..." class="img-circle team-img">
            <div class="caption">
              <h3>Jenn Gwapa</h3>
              <p>CEO / Founder</p>
              <p>Do not seek to change what has come before. Seek to create that which has not.</p>
            </div>
          </div>
        </div>

        <div class="item">
          <div class="thumbnail">
            <img src="/images/default_theme/team/01.jpg" alt="..." class="img-circle team-img">
            <div class="caption">
              <h3>Jenn Gwapa</h3>
              <p>CEO / Founder</p>
              <p>Do not seek to change what has come before. Seek to create that which has not.</p>
            </div>
          </div>
        </div>

        <div class="item">
          <div class="thumbnail">
            <img src="/images/default_theme/team/02.jpg" alt="..." class="img-circle team-img">
            <div class="caption">
              <h3>Jenn Gwapa</h3>
              <p>CEO / Founder</p>
              <p>Do not seek to change what has come before. Seek to create that which has not.</p>
            </div>
          </div>
        </div>

        <div class="item">
          <div class="thumbnail">
            <img src="/images/default_theme/team/03.jpg" alt="..." class="img-circle team-img">
            <div class="caption">
              <h3>Jenn Gwapa</h3>
              <p>CEO / Founder</p>
              <p>Do not seek to change what has come before. Seek to create that which has not.</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Services Section
==========================================-->
<div id="tf-services" class="text-center">
  <div class="container">
    <div class="section-title center">
      <h2>Take a look at <strong>our services</strong></h2>
      <div class="line">
        <hr>
      </div>
      <div class="clearfix"></div>
      <small><em>Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</em></small>
    </div>
    <div class="space"></div>
    <div class="row">
      <div class="col-md-3 col-sm-6 service">
        <i class="fa fa-desktop"></i>
        <h4><strong>Web design</strong></h4>
        <p>The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>
      </div>

      <div class="col-md-3 col-sm-6 service">
        <i class="fa fa-mobile"></i>
        <h4><strong>Mobile Apps</strong></h4>
        <p>The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>
      </div>

      <div class="col-md-3 col-sm-6 service">
        <i class="fa fa-camera"></i>
        <h4><strong>Photography</strong></h4>
        <p>The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>
      </div>

      <div class="col-md-3 col-sm-6 service">
        <i class="fa fa-bullhorn"></i>
        <h4><strong>Marketing</strong></h4>
        <p>The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>
      </div>
    </div>
  </div>
</div>

<!-- Clients Section
==========================================-->
<div id="tf-clients" class="text-center">
  <div class="overlay">
    <div class="container">

      <div class="section-title center">
        <h2>Some of <strong>our Clients</strong></h2>
        <div class="line">
          <hr>
        </div>
      </div>
      <div id="clients" class="owl-carousel owl-theme">
        <div class="item">
          <img src="/images/default_theme/client/01.png">
        </div>
        <div class="item">
          <img src="/images/default_theme/client/02.png">
        </div>
        <div class="item">
          <img src="/images/default_theme/client/03.png">
        </div>
        <div class="item">
          <img src="/images/default_theme/client/04.png">
        </div>
        <div class="item">
          <img src="/images/default_theme/client/05.png">
        </div>
        <div class="item">
          <img src="/images/default_theme/client/06.png">
        </div>
        <div class="item">
          <img src="/images/default_theme/client/07.png">
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Portfolio Section
==========================================-->
<div id="tf-works">
  <div class="container"> <!-- Container -->
    <div class="section-title text-center center">
      <h2>Take a look at <strong>our services</strong></h2>
      <div class="line">
        <hr>
      </div>
      <div class="clearfix"></div>
      <small><em>Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</em></small>
    </div>
    <div class="space"></div>

    <div class="categories">

      <ul class="cat">
        <li class="pull-left"><h4>Filter by Type:</h4></li>
        <li class="pull-right">
          <ol class="type">
            <li><a href="#" data-filter="*" class="active">All</a></li>
            <li><a href="#" data-filter=".web">Web Design</a></li>
            <li><a href="#" data-filter=".photography">Photography</a></li>
            <li><a href="#" data-filter=".app" >Mobile App</a></li>
            <li><a href="#" data-filter=".branding" >Branding</a></li>
          </ol>
        </li>
      </ul>
      <div class="clearfix"></div>
    </div>

    <div id="lightbox" class="row">

      <div class="col-sm-6 col-md-3 col-lg-3 branding">
        <div class="portfolio-item">
          <div class="hover-bg">
            <a href="#">
              <div class="hover-text">
                <h4>Logo Design</h4>
                <small>Branding</small>
                <div class="clearfix"></div>
                <i class="fa fa-plus"></i>
              </div>
              <img src="/images/default_theme/portfolio/01.jpg" class="img-responsive" alt="...">
            </a>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-3 col-lg-3 photography app">
        <div class="portfolio-item">
          <div class="hover-bg">
            <a href="#">
              <div class="hover-text">
                <h4>Logo Design</h4>
                <small>Branding</small>
                <div class="clearfix"></div>
                <i class="fa fa-plus"></i>
              </div>
              <img src="/images/default_theme/portfolio/02.jpg" class="img-responsive" alt="...">
            </a>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-3 col-lg-3 branding">
        <div class="portfolio-item">
          <div class="hover-bg">
            <a href="#">
              <div class="hover-text">
                <h4>Logo Design</h4>
                <small>Branding</small>
                <div class="clearfix"></div>
                <i class="fa fa-plus"></i>
              </div>
              <img src="/images/default_theme/portfolio/03.jpg" class="img-responsive" alt="...">
            </a>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-3 col-lg-3 branding">
        <div class="portfolio-item">
          <div class="hover-bg">
            <a href="#">
              <div class="hover-text">
                <h4>Logo Design</h4>
                <small>Branding</small>
                <div class="clearfix"></div>
                <i class="fa fa-plus"></i>
              </div>
              <img src="/images/default_theme/portfolio/04.jpg" class="img-responsive" alt="...">
            </a>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-3 col-lg-3 web">
        <div class="portfolio-item">
          <div class="hover-bg">
            <a href="#">
              <div class="hover-text">
                <h4>Logo Design</h4>
                <small>Branding</small>
                <div class="clearfix"></div>
                <i class="fa fa-plus"></i>
              </div>
              <img src="/images/default_theme/portfolio/05.jpg" class="img-responsive" alt="...">
            </a>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-3 col-lg-3 app">
        <div class="portfolio-item">
          <div class="hover-bg">
            <a href="#">
              <div class="hover-text">
                <h4>Logo Design</h4>
                <small>Branding</small>
                <div class="clearfix"></div>
                <i class="fa fa-plus"></i>
              </div>
              <img src="/images/default_theme/portfolio/06.jpg" class="img-responsive" alt="...">
            </a>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-3 col-lg-3 photography web">
        <div class="portfolio-item">
          <div class="hover-bg">
            <a href="#">
              <div class="hover-text">
                <h4>Logo Design</h4>
                <small>Branding</small>
                <div class="clearfix"></div>
                <i class="fa fa-plus"></i>
              </div>
              <img src="/images/default_theme/portfolio/07.jpg" class="img-responsive" alt="...">
            </a>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-3 col-lg-3 web">
        <div class="portfolio-item">
          <div class="hover-bg">
            <a href="#">
              <div class="hover-text">
                <h4>Logo Design</h4>
                <small>Branding</small>
                <div class="clearfix"></div>
                <i class="fa fa-plus"></i>
              </div>
              <img src="/images/default_theme/portfolio/08.jpg" class="img-responsive" alt="...">
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Testimonials Section
==========================================-->
<div id="tf-testimonials" class="text-center">
  <div class="overlay">
    <div class="container">
      <div class="section-title center">
        <h2><strong>Our clients’</strong> testimonials</h2>
        <div class="line">
          <hr>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div id="testimonial" class="owl-carousel owl-theme">
            <div class="item">
              <h5>This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</h5>
              <p><strong>Dean Martin</strong>, CEO Acme Inc.</p>
            </div>

            <div class="item">
              <h5>This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</h5>
              <p><strong>Dean Martin</strong>, CEO Acme Inc.</p>
            </div>

            <div class="item">
              <h5>This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</h5>
              <p><strong>Dean Martin</strong>, CEO Acme Inc.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Contact Section
==========================================-->
<div id="tf-contact" class="text-center">
  <div class="container">

    <div class="row">
      <div class="col-md-8 col-md-offset-2">

        <div class="section-title center">
          <h2>Feel free to <strong>contact us</strong></h2>
          <div class="line">
            <hr>
          </div>
          <div class="clearfix"></div>
          <small><em>Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</em></small>
        </div>

        <form>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="exampleInputEmail1">Message</label>
            <textarea class="form-control" rows="3"></textarea>
          </div>

          <button type="submit" class="btn tf-btn btn-default">Submit</button>
        </form>

      </div>
    </div>

  </div>
</div>

<nav id="footer">
  <div class="container">
    <div class="pull-left fnav">
      <p>COPYRIGHT © {date('Y')}.</p>
    </div>
    <div class="pull-right fnav">
      <ul class="footer-social">
        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
        <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
        <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
      </ul>
    </div>
  </div>
</nav>

<block type="js" sequence="outputFilter" consolidate="false"></block>

{js('fraym/libs/jquery.min.js', 'default', 'jquery')}
{js('default_theme/bootstrap.min.js')}
{js('default_theme/SmoothScroll.js')}
{js('default_theme/jquery.isotope.js')}
{js('default_theme/owl.carousel.js')}
{js('default_theme/main.js')}

</body>
</html>