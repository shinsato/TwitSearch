<html>
	<body>
		<form method="post">
			<div>Search: <input type="text" name="search"/></div>
			<input type="submit" value="Search!"/>
		</form>
		
		<?php
		if(count($_POST) > 0)
		{
			include('TwitterSearch.php');
			$search = $_POST['search'];

			$tsearch = new TwitterSearch();
			$results = $tsearch->QuickSearch($search);
			echo '<pre>';
			var_dump($results);
			echo '</pre>';
		}
		?>
	</body>
</html>