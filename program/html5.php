<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>html</title>
	<style>
		body>header{
			border: dotted 3px #c00;
		}
		body>footer{
			border: dotted 3px #c00;
		}

		aside{
			border: dotted 3px #0df;
			padding: 10px;
		}

		aside#sideMenu{
			width: 150px;
			float: left;
		}

		aside#extra{
			width: 300px;
			float: right;
		}

		section#mainContent{
			margin-left: 160px;
			margin-right: 310px;
		}

		nav#mainMenu{
			border: dotted 3px #0df; margin: 10px 0;
			text-align: center
		}

		nav#mainMenu li{
			display: inline-block;
		}
		nav#mainMenu li>a{
			display: block;
			width: 120px;
			background: #ccc;
			padding: 5px 10px
		}
		nav#mainMenu>ul{
			padding: 0; margin: 0;
			display: inline-block;
		}

		article>header>time{
			float: right;
		}
		article>footer>nav>ul{
			margin: 10px 0; padding: 0;
		}
		article>footer>nav>ul>li{
			display: inline-block;
			margin-left: 20px;
		}

		article>figure{
			display: inline-block;
			margin: 5px;
			padding: 10px;
			background: #ccc;
			float: left;
			text-align: center;
			border-radius: 3px;
		}
		article>header>h1{
			margin: 10px;
		}

		article>figure>p{

		}

		article>hr, article>footer{
			clear: both;
		}


	</style>
</head>
<body>

<header>
	<h1>Header</h1>
</header>

<nav id="mainMenu">
	<ul>
		<li><a href="#">Minim</a></li>
		<li><a href="#">Reprehenderit</a></li>
		<li><a href="#">Exercitation</a></li>
		<li><a href="#">Deserunt</a></li>
		<li><a href="#">Qui</a></li>
	</ul>
</nav>



<aside id="sideMenu">
	<nav>
		<ul>
			<li>Sagittis</li>
			<li>Convallis</li>
			<li>Etiam</li>
		</ul>
	</nav>
</aside>

<section id="mainContent">
	<article>
		<header>
			<time datetime="2012-01-24">Today</time>
			<h1><a href="#">My article</a></h1>
		</header>
		<figure>
			<img src="/img/Penguins.jpg" width="160" height="120" alt="Image title" title="Image title" />
			<figcaption>Image caption</figcaption>
		</figure>
		<p>
			Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
		</p>

		<footer>
			<nav>
				<ul>
					<li><a href="#">Read More</a></li>
					<li><a href="#">Comments</a> (0)</li>
				</ul>
			</nav>
		</footer>
		<hr />
	</article>

	<aside id="extra">
		banners
	</aside>

</section>




<!--<div id="container">content</div>-->

<footer>
	&copy; Zelenyuk 2012
</footer>

</body>
</html>