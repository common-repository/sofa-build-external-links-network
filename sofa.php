<?php
/*
Plugin Name: SofA
Plugin URI: http://sofa-for-people.com
Description: Set of Articles - Build the biggest external links network together with your new friends from the industry. SofA is the biggest network where you can share your content with different people around the world. Very simple. You can write posts which contain links to your page and add to SofA repository. Someone else will publish your post on then Blog and will give you external link to your page. SofA will find right person for you. Based on your tags and tags from another people we merge people in smaller groups. Every one in the group can read your post. When someone decide to publish your work we will remove your article from SofA repository to protect users against duplicate content issue.
Version: The Plugin's Version Number, e.g.: 0.1.3
Author: Michal Nawrocki
License: A "Slug" license name e.g. GPL2
*/
include_once 'functions.php';

include_once 'lib/HttpClient.class.php';
define('SOFA_VERSION', '0.1.3');
define('SOFA_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('SOFA_API_URL', 'sofa-for-people.com');
define('APP_NAME', '');
define('SOFA_API_PORT', 80);



add_action( 'admin_menu', 'my_plugin_menu' );
add_action('wp_ajax_generateAccount_action', 'generateAccount_action_callback');
add_action('wp_ajax_getArticles_action', 'getArticles_action_callback');
add_action('wp_ajax_markAsLowQuality_action', 'markAsLowQuality_action_callback');
add_action('wp_ajax_publishArticle_action', 'publishArticle_action_callback');
add_action('wp_ajax_addArticle_action', 'addArticle_action_callback');
wp_register_style( 'maincss', SOFA_PLUGIN_URL.'css/main.css' );
wp_register_style( 'jqueryJQplot', SOFA_PLUGIN_URL.'css/jquery.jqplot.min.css' );
wp_register_script( 'jqueryJQplot-script', SOFA_PLUGIN_URL.'lib/jquery.jqplot.min.js');
wp_register_script( 'jqueryJQplot-plugin1', SOFA_PLUGIN_URL.'lib/plugins/jqplot.donutRenderer.min.js');
wp_register_script( 'jqueryJQplot-plugin2', SOFA_PLUGIN_URL.'lib/plugins/jqplot.pieRenderer.min.js');


function my_plugin_menu() {

	add_menu_page( 'Manage your settings','SofA', 'manage_options', 'SofA-top-level-handle', 'SofA_index');
	add_submenu_page( 'SofA-top-level-handle', 'Your tags', 'Your tags', 'manage_options', 'SofA-submenu-handle-spam', 'SofA_your_tags');
	add_submenu_page( 'SofA-top-level-handle', 'Your credentials', 'Credentials', 'manage_options', 'SofA-submenu-handle-credentials', 'SofA_credentials');
	
}

function SofA_index() {
		wp_enqueue_style('maincss');
		wp_enqueue_style('jqueryJQplot');
		wp_enqueue_script('jquery');
		wp_enqueue_script('jqueryJQplot-script');
		wp_enqueue_script('jqueryJQplot-plugin1');
		wp_enqueue_script('jqueryJQplot-plugin2');
		
		
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
?>
<div class="wrap">
<div id="icon-options-general" class="icon32">
	<br>
	</div>
	<h2>SofA</h2>
	<h3>Set of Articles - Build the biggest external links network together with your new friends from the industry</h3>
	<p>You can write. You can share - posts, articles or recomandations. SofA will connect you with different SofA users having similar content and will allow you to turn into links with them.</p>
	
	<div id="msgContainer"><?php
	$client = new HttpClient(SOFA_API_URL, SOFA_API_PORT);
	$client -> setDebug(false);
	if (!$client -> post(APP_NAME.'/getMessageForUser.xml', array('version' =>SOFA_VERSION,  'ownerKey' => get_option("SOFA_API_key"),'language'=>get_bloginfo('language') ))) {
		die('An error occurred: ' . $client -> getError());
	}
	$pageContents = $client -> getContent();
	echo $pageContents;
	?></div>
	<div id="msgContainerSmall">
	</div>
	
	<h3>SofA try to find the best articles for you <br>
		We use your tags popularity to find the best one: 
		 <?php
		global $wpdb;
		$sql = "select t.name, t.slug ,count(tt.taxonomy) as popularity, r.object_id from ".$wpdb->prefix."terms t left join ".$wpdb->prefix."term_taxonomy tt on t.term_id=tt.term_id left join ".$wpdb->prefix."term_relationships r on t.term_id=r.term_taxonomy_id where t.name!='Uncategorized' group by t.name order by popularity desc limit 12;";
		$rows = $wpdb -> get_results($sql);

		foreach ($rows as $obj) {
			echo $obj -> name . ($obj -> popularity > 1 ? "<sup>" . $obj -> popularity . "</sup>, " : ", ");
		}
  ?>
  	</h3>
  	<div class="articleGridcontainer #nav-menu-header">
	<label>Your search query</label>	
	<input type="text" name="searchQuery" id="searchQuery" value="<?php echo $_GET['queryString']?>"/>
	<button id="searchQueryButton" class="button button-primary button-large">Search</button>
	<button id="addSofaArticleButton" class="button  button-large button-green ">Add your Sofa Article</button>
	<div id="testJ" style="display: none"></div>
	<div id="article-details-schema">
		<div class="top-bar">
			<img class="close-tab" src='<?php echo SOFA_PLUGIN_URL?>/img/close-icon.png'>
		<h2 class="show-b">Publish Sofa Article on your blog</h2>
				<h2 class="show-s">Share your Sofa Article</h2>
		</div>
			<div style="margin: 20px;">
				<p class="show-b">Add this article to your blog and create an external link for your new friend. <br>When you click publish button SofA remove this article form repository to prevent duplicate content and publish this article on your blog.<br>
					You can change this article before you add if you wish but keep links to your friend page.
					
				</p>
				<p class="show-s">Add your article to Sofa repository.<br>Your new friends are waiting for Your article and are ready to populate your work on their blogs to build external link for You.<br>
				
				<br><strong>The best practices when you write article.</strong>
				<br>- Write First Person Point of View.
				<br>- Add links to your Blog.
				<br>- If your article will be interesting, someone will import your article on their blog very quickly.
				<br>- You can write recomendation of your post. 
				<br>- Describe your blog and say people why is so good. Someone will publish your work very quickly and give you external link.
				<br><strong style="color:red">- Be careful when you try inject java script. If you try we will be happy to ban you.</strong>
				<br>- You can add many posts to SofA repository but you have to change content first. We don't like the same content.
				<br>- Your blog is in local language, don't worry you can add article in your native language.			
				</p>
				<h4>Title</h4>
				<input type="text" name="articleTitle" class="articleTitle" style="width: 100%"/>
				<h4>Content</h4>
				<textarea class="articleBody" rows="10"></textarea>
				<div class="show-b">
				<h4 >Tags</h4><p>use "," to separete tags</p>
				<input type="text" name="articleTags" class="articleTags" style="width: 100%"/>
				</div>
					<div class="button-area">
						<button class="button lowQualityButton button-primary button-large button-red show-b">Mark as low quality</button>
						<button class="button publishArticleButton button-large button-green show-b">Publish Sofa Article on your blog</button>
						<button class="button shareArticleButton button-large button-green show-s">Share your Sofa Article </button>
					</div>
			</div>
	</div>
	<script>
		jQuery(document).ready(function(){
			
			getArticles('<?php echo $_GET['queryString']?>');
			jQuery("#searchQueryButton").click(function(){
				jQuery("#articleGrid").html("<img src='<?php echo SOFA_PLUGIN_URL?>/img/ajax-loader.gif' class='img-loader'/>");
				getArticles(jQuery("#searchQuery").val());
			})
			
			jQuery("#addSofaArticleButton").click(function(){
				
				showArticle();
			});
			
			
			
			
		});
		function showArticle(articleId,title,body){
			jQuery("body").append("<div class='article-details' ><div class='article-details-window'><div class='article-details-window-fixed'></div></div></div>");
			jQuery('.article-details-window-fixed').html(jQuery('#article-details-schema').html());
			jQuery(".show-b").hide();
			jQuery(".show-s").hide();
			jQuery(".articleTitle").val("");
			jQuery(".articleBody").val("");
			if(articleId!=null){
				jQuery(".show-b").show();
				jQuery('.article-details-window-fixed').find(".articleTitle").val(title);
				jQuery('.article-details-window-fixed').find(".articleBody").val(body);
				jQuery(".lowQualityButton").click(function(){
					var data = {
						action : 'markAsLowQuality_action',
						articleId : articleId
						};
					jQuery.post(ajaxurl, data, function(response) {
					var xmlDoc = jQuery(response);
					if (xmlDoc.find("succes").text()=="true") {
							alert(xmlDoc.find("description").text());
						 	jQuery(".article-details").remove();
							 getArticles(jQuery("#searchQuery").val());
						} else {
							alert(xmlDoc.find("description").text());
						}
					},"xml");
				});
				jQuery(".publishArticleButton").click(function(){
						var data = {
						action : 'publishArticle_action',
						articleId : articleId,
						title: jQuery('.article-details-window-fixed').find(".articleTitle").val(),
						body: jQuery('.article-details-window-fixed').find(".articleBody").val(),
						tags: jQuery('.article-details-window-fixed').find(".articleTags").val(),
						};
					jQuery.post(ajaxurl, data, function(response) {
					var xmlDoc = jQuery(response);
					if (xmlDoc.find("succes").text()=="true") {
							alert(xmlDoc.find("description").text());
						 	jQuery(".article-details").remove();
							 getArticles(jQuery("#searchQuery").val());
						} else {
							alert(xmlDoc.find("description").text());
						}
					},"xml");
				});
						 
				
			}else {
				jQuery(".show-s").show();
				
				jQuery(".shareArticleButton").click(function(){
					var text = jQuery('.article-details-window-fixed').find(".articleBody").val();
					jQuery("#testJ").html(text);
					var data = {
						action : 'addArticle_action',
						title: jQuery('.article-details-window-fixed').find(".articleTitle").val(),
						body: text
						};
					jQuery.post(ajaxurl, data, function(response) {
					var xmlDoc = jQuery(response);
					if (xmlDoc.find("succes").text()=="true") {
							alert(xmlDoc.find("description").text());
						 	jQuery(".article-details").remove();
						 	location.reload();
							 getArticles(jQuery("#searchQuery").val());
						} else {
							alert(xmlDoc.find("description").text());
						}
					},"xml");
				});
				
			}
			jQuery(".close-tab").click(function(){
				 jQuery(".article-details").remove();
			});
		}
		
		
		function getArticles(queryString){
			
			var data = {
				action : 'getArticles_action',
				queryString : queryString				
			};
			jQuery.post(ajaxurl, data, function(response) {
				var xmlDoc = jQuery(response);
				jQuery("#articleGrid").html("");
					if (xmlDoc.find("type").text()=="articles") {
						var i=0;
					 xmlDoc.find("articles").each(function() {
						var id = jQuery(this).find('id').text();
						var title = jQuery(this).find('title').text();
						var body = jQuery(this).find('body').text();
						
						jQuery("#articleGrid").append("<div data-article-id="+id+" class='articleSofa "+(i % 2 == 0 ? "darker" : "")+"'><h3 class='title'>"+title+"</h3><p class='body'>"+body+"</p></div>");
						i++;
						});
						jQuery(".articleSofa").click(function(){
							showArticle(jQuery(this).data("article-id"),jQuery(this).find(".title").html(),jQuery(this).find(".body").html());
						});
				} else {
					 if(jQuery("#msgContainer").text().length <= 0){
						jQuery("#msgContainerSmall").html("<div class='sofamsgred'>"+xmlDoc.find("description").text()+"</div>");
					 }
				}
			},"xml");
			
		}
	</script>
	
	
	
	</div>
	
		<div id="articleGrid">
				<img src='<?php echo SOFA_PLUGIN_URL?>/img/ajax-loader.gif' class='img-loader'/>	
		</div>
	 


	</div>

	<?php

	}
	
	function SofA_credentials() {
	echo '<div class="wrap">';
	if ( !current_user_can( 'manage_options' ) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	wp_enqueue_script('jquery');
	wp_enqueue_style('maincss');
	?>
	<div id="icon-options-general" class="icon32">
	<br>
	</div>
	<h2>Manage your account</h2>
	<h3>To use SofA you need to activated an account. To do it step as follow</h3>
	<ul class="steps">
		<li >1. Generate your API key on <a href="http://www.sofa-for-people.com/generateAPIforSofA.htm">www.sofa-for-people.com</a></li>
		<li class="big">2. Save Your API key here</li>
		<li>3. You can enjoy Your new functionality</li>
		
	</ul>
	<div class="form" >
		<form id="generateAccountForm">
		<table class="form-table">
	<tbody>
		<tr class="form-field form-required">
		<th scope="row"><label for="email_adress">Your API key</label></th>
		<td><input type="text" value="<?php echo get_option("SOFA_API_key")?>" id="APIKey" name="APIKey" required placeholder="Enter your API key"></td>
	</tr>
	</tbody></table>
	
	<p class="submit">
		<input id="saveChanges" class="button-primary" type="button" value="Save Changes" name="submit">
	</p>
	<p>SofA is in Bata version now. If you have any problems or sugestions send email to: michael@sofa-for-people.com</p>
	
		</form>
	</div>
	<script>
		jQuery('#saveChanges').click(function() {

			var data = {
				action : 'generateAccount_action',
				APIKey : jQuery("#APIKey").val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				var xmlDoc = jQuery(jQuery.parseXML(response));
				if (xmlDoc.find("succes").text()=="true") {
					alert("Your settings are saved");
					} else {
						alert("Your settings are saved");
				}
			});

		});

		function getnerateAccountFeedback(data) {

			alert(data);
		}

	</script>
	<?php

	echo '</div>';
	}
	function SofA_your_tags() {
	if ( !current_user_can( 'manage_options' ) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	wp_enqueue_style('jqueryJQplot');
	wp_enqueue_script('jquery');
		?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32">
	<br>
	</div>
	<h2>Your tags</h2>
	<h3>SofA check your tags  popularity and use this information to find the best articles for you</h3>
	<?php
	echo '<div id="tagsChart"></div>';
	?>
	
	 <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script type="text/javascript">
		google.load('visualization', '1', {
			packages : ['corechart']
		});
    </script>
    <script type="text/javascript">
      		function drawVisualization() {
        // Create and populate the data table.
        var data = google.visualization.arrayToDataTable([['tag name','score'],
         <?php
		global $wpdb;
		$sql = "select t.name, t.slug ,count(tt.taxonomy) as popularity, r.object_id from ".$wpdb->prefix."terms t left join ".$wpdb->prefix."term_taxonomy tt on t.term_id=tt.term_id left join ".$wpdb->prefix."term_relationships r on t.term_id=r.term_taxonomy_id where t.name!='Uncategorized' group by t.name order by popularity desc limit 12;";
		$rows = $wpdb -> get_results($sql);

		foreach ($rows as $obj) {
			echo "[";
			echo "'" . $obj -> name . "'," . $obj -> popularity;
			echo "],";
		}
  ?>
	]);

	// Create and draw the visualization.
	new google.visualization.PieChart(document.getElementById('visualization')).draw(data, {
		title : "Your page profile based on your tages"
	});
	}

	google.setOnLoadCallback(drawVisualization);
    </script>
     <div id="visualization" style="width: 600px; height: 500px;"></div>
	
	
	<?php
	echo '</div>';
	}
	

	function markAsLowQuality_action_callback() {
		$client = new HttpClient(SOFA_API_URL,SOFA_API_PORT);
		$client->setDebug(false);
	
		$array = array(
				'ownerKey'=>get_option("SOFA_API_key"),
				'articleId'=>$_POST['articleId']);
			
	if (!$client->post(APP_NAME.'/markAsLowQuality.xml',$array)) {
	die('An error occurred: '.$client->getError());
	}
	$pageContents = $client->getContent();
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ) );
	echo $pageContents;
	die();
	}
	
	function addArticle_action_callback() {
		$client = new HttpClient(SOFA_API_URL,SOFA_API_PORT);
		$client->setDebug(false);
		global $wpdb;
		$sql = "select t.name, t.slug ,count(tt.taxonomy) as popularity, r.object_id from ".$wpdb->prefix."terms t left join ".$wpdb->prefix."term_taxonomy tt on t.term_id=tt.term_id left join ".$wpdb->prefix."term_relationships r on t.term_id=r.term_taxonomy_id where t.name!='Uncategorized' group by t.name order by popularity desc limit 12;";
		$rows = $wpdb -> get_results($sql);
		$tags= "";
		foreach ($rows as $obj) {
				$tags.= "'" . $obj -> name . "'=>" . $obj -> popularity."^";
			
		}
		$array = array(
				'ownerKey'=>get_option("SOFA_API_key"),
				'title'=>$_POST['title'],
				'body'=>$_POST['body'],
				'tags'=>$tags);
			
	if (!$client->post(APP_NAME.'/addArticle.xml',$array)) {
	die('An error occurred: '.$client->getError());
	}
	$pageContents = $client->getContent();
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ) );
	echo $pageContents;
	die();
	}

	function publishArticle_action_callback() {
		$client = new HttpClient(SOFA_API_URL,SOFA_API_PORT);
		$client->setDebug(false);
	
		$array = array(
				'ownerKey'=>get_option("SOFA_API_key"),
				'articleId'=>$_POST['articleId']);
			
	if (!$client->post(APP_NAME.'/publishArticle.xml',$array)) {
	die('An error occurred: '.$client->getError());
	}
	$pageContents = $client->getContent();
	
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ) );
	$xml = simplexml_load_string($pageContents);
	foreach($xml->xpath('//succes') as $item){
		if($item){
			$post_id = wp_insert_post(
		    array(
			  'comment_status'  => 'closed',
		      'ping_status'   => 'open',
		      'post_author'   => wp_get_current_user()->ID,
		      'post_title'    => $_POST['title'],
		      'post_status'   => 'publish',
		      'post_type'   => 'post',
		      'tags_input'  => $_POST['tags'],
		      'post_content' => $_POST['body']
		    ));
		}
	}
	echo $pageContents;
	die();
	}
	
	function getArticles_action_callback() {

	global $wpdb;
		$sql = "select t.name, t.slug ,count(tt.taxonomy) as popularity, r.object_id from ".$wpdb->prefix."terms t left join ".$wpdb->prefix."term_taxonomy tt on t.term_id=tt.term_id left join ".$wpdb->prefix."term_relationships r on t.term_id=r.term_taxonomy_id where t.name!='Uncategorized' group by t.name order by popularity desc limit 12;";
		$rows = $wpdb -> get_results($sql);
		$tags= "";
		foreach ($rows as $obj) {
				$tags.= "'" . $obj -> name . "'=>" . $obj -> popularity."^";
			
		}

	$client = new HttpClient(SOFA_API_URL,SOFA_API_PORT);
	$client->setDebug(false);
	
	$array = array(
			'ownerKey'=>get_option("SOFA_API_key"),
			'queryString'=>$_POST['queryString'],
			'tags'=>$tags);
			
	if (!$client->post(APP_NAME.'/getArticles.xml',$array)) {
	die('An error occurred: '.$client->getError());
	}
	$pageContents = $client->getContent();
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ) );
	echo $pageContents;
	die();
	}
	
	function generateAccount_action_callback() {
	update_option("SofA_API_key", trim($_POST['APIKey']));

	die(); // this is required to return a proper result
	}
?>