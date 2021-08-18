<template>
<header id="header"><!--header-->
		<div class="header_top">
			<div class="container">
				<div class="row">
					<div class="col-sm-6">
						<div class="contactinfo">
							<ul class="nav nav-pills">
								<li><a href=""><i class="fa fa-phone"></i> +2 95 01 88 821</a></li>
								<li><a href=""><i class="fa fa-envelope"></i> info@domain.com</a></li>
							</ul>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="social-icons pull-right">
							<ul class="nav navbar-nav">
								<li><a href=""><i class="fa fa-facebook"></i></a></li>
								<li><a href=""><i class="fa fa-twitter"></i></a></li>
								<li><a href=""><i class="fa fa-linkedin"></i></a></li>
								<li><a href=""><i class="fa fa-dribbble"></i></a></li>
								<li><a href=""><i class="fa fa-google-plus"></i></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="header-middle"><!--header-middle-->
			<div class="container">
				<div class="row">
					<div class="col-sm-4">
						<div class="logo pull-left">
							<router-link :to="{name:'store'}">
									<img src="img/logo.png" alt="" />
							</router-link>
						</div>
						<div class="btn-group pull-right">
							<!-- <div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle usa" data-toggle="dropdown">
									USA
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li><a href="">Canada</a></li>
									<li><a href="">UK</a></li>
								</ul>
							</div>
							
							<div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle usa" data-toggle="dropdown">
									DOLLAR
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li><a href="">Canadian Dollar</a></li>
									<li><a href="">Pound</a></li>
								</ul>
							</div> -->
						</div>
					</div>
					<div class="col-sm-8">
						<div class="shop-menu pull-right">
							<ul class="nav navbar-nav">
								<li><a href=""><i class="fa fa-user"></i> Cuenta</a></li>
								<!-- <li><a href=""><i class="fa fa-star"></i> Wishlist</a></li> -->
								<li><a href="checkout.html"><i class="fa fa-crosshairs"></i> Pago</a></li>
								<li><a href="login.html"><i class="fa fa-lock"></i> Iniciar sesión</a></li>
								<li><router-link  :to="{name: 'checkout'}">
                        <font-awesome-layers full-width class="fa-2x" >
                        <font-awesome-icon icon="shopping-cart" />
                        <font-awesome-layers-text style="color:white, font-weight:bold" transform="down-2 shrink-9" :value="productsInCart.length" />
                        </font-awesome-layers>
                      </router-link>  </li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div><!--/header-middle-->
	
		<div class="header-bottom"><!--header-bottom-->
			<div class="container">
				<div class="row">
					<div class="col-sm-9">
						<div class="navbar-header">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
						</div>
						<div class="mainmenu pull-left">
							<ul class="nav navbar-nav collapse navbar-collapse">
								<!-- <li><router-link :to="{name:'store'}">Inicio</router-link></li> -->
								<li><router-link :to="{name:'store'}" >Productos</router-link>
                                    <ul role="menu" class="sub-menu">
                                        <li><a href="shop.html">Productos</a></li>
										<li><a href="product-details.html" class="active">Product Details</a></li> 
										<li><a href="checkout.html">Checkout</a></li> 
										<li>
										<router-link  :to="{name: 'checkout'}">
											<font-awesome-layers full-width class="fa-2x" >
											<font-awesome-icon icon="shopping-bag" />
											<font-awesome-layers-text style="color:white" transform="down-2 shrink-9" :value="productsInCart.length" />
											</font-awesome-layers>
										</router-link>  
										</li> 
										<li><a href="login.html">Login</a></li> 
                                    </ul>
                                </li> 
								<li class="dropdown"><a href="#">Blog<i class="fa fa-angle-down"></i></a>
                                    <ul role="menu" class="sub-menu">
                                        <li><a href="blog.html">Blog List</a></li>
										<li><a href="blog-single.html">Blog Single</a></li>
                                    </ul>
                                </li> 
								<li><a href="404.html">404</a></li>
								<li><a href="contact-us.html">Contacto</a></li>
							</ul>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="search_box pull-right">
							<input type="text" placeholder="Search"/>
						</div>
					</div>
				</div>
			</div>
		</div><!--/header-bottom-->
	</header><!--/header-->

</template>

<script>
//  <a class="btn btn-bd-download d-none d-lg-inline-block mb-3 mb-md-0 ml-md-3">
//           <font-awesome-icon icon="shopping-bag" />
//         </a>
        

        // <font-awesome-layers full-width class="fa-2x">
        //   <font-awesome-icon icon="shopping-bag" />
        //   <font-awesome-layers-text style="color:white" transform="down-2 shrink-9" value="1" />
        // </font-awesome-layers>


// <svg class="d-block" width="36" height="36" viewBox="0 0 612 612" xmlns="http://www.w3.org/2000/svg" focusable="false">
//           <title>Bootstrap</title>
//           <path fill="currentColor" d="M510 8a94.3 94.3 0 0 1 94 94v408a94.3 94.3 0 0 1-94 94H102a94.3 94.3 0 0 1-94-94V102a94.3 94.3 0 0 1 94-94h408m0-8H102C45.9 0 0 45.9 0 102v408c0 56.1 45.9 102 102 102h408c56.1 0 102-45.9 102-102V102C612 45.9 566.1 0 510 0z"></path>
//           <path fill="currentColor" d="M196.77 471.5V154.43h124.15c54.27 0 91 31.64 91 79.1 0 33-24.17 63.72-54.71 69.21v1.76c43.07 5.49 70.75 35.82 70.75 78 0 55.81-40 89-107.45 89zm39.55-180.4h63.28c46.8 0 72.29-18.68 72.29-53 0-31.42-21.53-48.78-60-48.78h-75.57zm78.22 145.46c47.68 0 72.73-19.34 72.73-56s-25.93-55.37-76.46-55.37h-74.49v111.4z"></path>
//         </svg>

  import { mapState } from 'vuex'


  export default {
    name: 'eheader',
     computed: {
       ...mapState(['productsInCart'])
     },
  }
</script>

<style>
</style>