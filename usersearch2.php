<?php /* Template Name: Writer Search Form */ get_header(); ?> 

<div id="content_box"> 

<div id="content"> 

<div class="post-57 page type-page hentry category-bwaevents"> <div class="entry"> 

<?php 

if  (isset($_POST['search']) && $_POST['search'] == 'go') { 

$where = ''; 



$keywords = array_map('trim', split(',', $_POST['searchKeyword']));



$or = array();



foreach ($keywords as $keyword) {

	$or[] = 'meta_key = "mepr_specialty" AND LOWER(meta_value) = "%'.mysql_escape_string(strtolower(trim($keyword))).'%"'; 

	$or[] = 'meta_key = "mepr_software" AND LOWER(meta_value) LIKE "%'.mysql_escape_string(strtolower(trim($keyword))).'%"';

	$or[] = 'meta_key = "mepr_operating_system" AND LOWER(meta_value) LIKE "%'.mysql_escape_string(strtolower(trim($keyword))).'%"';
	
	$or[] = 'meta_key = "first_name" AND LOWER(meta_value) LIKE "%'.mysql_escape_string(strtolower(trim($keyword))).'%"';

	$or[] = 'meta_key = "last_name" AND LOWER(meta_value) LIKE "%'.mysql_escape_string(strtolower(trim($keyword))).'%"';

	$or[] = 'meta_key = "description" AND LOWER(meta_value) LIKE "%'.mysql_escape_string(strtolower(trim($keyword))).'%"';

	$or[] = 'meta_key = "mepr_summary" AND LOWER(meta_value) LIKE "%'.mysql_escape_string(strtolower(trim($keyword))).'%"';
	
	$or[] = 'meta-key = "mepr_keywords" AND LOWER(meta_value) LIKE "%'.mysql_escape_string(strtolower(trim($keyword))).'%"';

//	$or[] = 'meta_key = "wpum_writer1" AND LOWER(meta_value) LIKE "%'.mysql_escape_string(strtolower(trim($keyword))).'%"';

}



$and = '';

if (isset($_POST['city']) && !empty($_POST['city'])) {

	$and = 'meta_key = "mepr_city" AND (meta_value="'.

			mysql_escape_string(ucwords(strtolower(trim($_POST['city'])))).'" || meta_value="'.

			mysql_escape_string(strtolower(trim($_POST['city']))).'" || meta_value="'.

			mysql_escape_string(strtoupper(trim($_POST['city']))).'")';

}


foreach ($or as $o) {

$where .= (strlen($where) > 0 ? " OR " : "")."(".$o.")";

}

// Always omit inactive users
$where .= ") AND user_id in ( SELECT user_id FROM wp_usermeta WHERE meta_key = 'ms_is_member' and LOWER(meta_value) =1";

if (strlen($and) > 0) {

$where .= ' AND user_id IN ( SELECT user_id FROM wp_usermeta WHERE '.$and.') )';

} else {

$where .= ')';

}



$q = "SELECT * from wp_usermeta WHERE ($where";

//echo $q.'<br />';



$results = $wpdb->get_results($q);

$users = array();

foreach ($results as $res) {

$users[$res->user_id][$res->meta_key] = $res->meta_value;

}



$q = "SELECT * from wp_users WHERE wp_users.ID IN (".implode(",",array_keys($users)).")";



$results = $wpdb->get_results($q);

if (count($results) > 0) {

foreach ($results as $res) {

	$q2 = "SELECT meta_key, meta_value FROM wp_usermeta WHERE meta_key IN ('mepr_specialty', 'mepr_keywords', 'mepr_city', 'mepr_summary', 'last_name', 'first_name') AND user_id = ".$res->ID;

	$res2 = $wpdb->get_results($q2);

	$summary = '';

	$keywords = '';

	$software = '';

	$specialty = '';

	foreach ($res2 as $r2) {

		switch ($r2->meta_key) {

			case 'mepr_summary':

				$summary = $r2->meta_value;

				break;

			case 'mepr_City':

				$city = $r2->meta_value;

				break;

case 'mepr_specialty':

$specialty = $r2->meta_value;

break;

		}

	}



$url = '/author/'.$res->user_nicename;

printf('<div class="user" id="user-%s" style="margin-bottom:15px;clear:left;border-bottom:1px solid #dfdfdf;">

<span class="username"><a href="%s">%s</a></span>

<dl id="usersearch" style="margin:5px 0 0 20px;">

<dt>Summary:</dt><dd>%s</dd>

<dt>Specialty:</dt><dd>%s</dd>

%s

</dl>

<p style="clear:left;padding-left:20px;">View <a href="%s">%s\'s full profile</a></p>

</div>',

$res->ID,

"/main".$url,
// we removed the webroot folder from the /main folder. not sure if we still need these /mains

$res->display_name,

$summary,

$specialty,

(isset($_POST['city']) && !empty($_POST['city']) ? sprintf('<dt>City: </dt><dd>%s</dd>',$city) : ''),

"/main".$url,

$res->display_name);

}

echo '<p style="clear:left; padding-top:45px">Would you like to <button value="search again" onclick="window.location=\'/main/find-a-writer/\'"/>Search Again</button>?</p>';

} else {

echo '<p>Unfortunately, we did not find anyone who matches your search criteria. Perhaps you would like to <button value="search again" onclick="window.location=\'/main/find-a-writer/\'"/>Search Again</button>?</p>'; } } else { ?> <h2>Find a Skilled Writer or Other Publishing Professional</h2><h3>Search the Boulder Writers Alliance Member Database</h3><p>Are you seeking a qualified publishing professional for a job or a project? Whatever you're trying to accomplish in this field, we probably have someone with the skill it takes to produce what you need.</p><p>To search for writers, editors, graphic or instructional designers, trainers, and Web publishers in Boulder or throughout Colorado:</p><ol><li>Enter the keywords that describe the talent you're looking for. For example, enter technical writer, copywriter, or desktop publisher.</li><li>Optionally, choose a city.</li><li>Click button to begin search.</li></ol><p>&nbsp;</p>
 <form action="/main/find-a-writer/" method="post"> <dl id="usersearch"> <dt> <span style="text-decoration: underline;"><strong>City</strong><br /><span class="note">(optional)</span></span> </dt> <dd> <?php $results = $wpdb->get_results("SELECT DISTINCT(meta_value) FROM wp_usermeta WHERE meta_key = 'mepr_city' ORDER BY meta_value ASC"); $select = ''; $cities = array(); foreach ($results as $res) { if ($res->meta_value != '') { $city = ucwords(strtolower($res->meta_value)); $cities[$city] = $city; } } $cities = array_unique($cities); ksort($cities); foreach ($cities as $city) { $select .= sprintf('<option value="%s">%s</option>', $city, $city); } ?> <select name="city"> <option value="">Select...</option> <?php echo $select; ?> </select> </dd> <dt> <span style="text-decoration: underline;"><strong>Keyword Search</strong></span> </dt> <dd> <textarea name="searchKeyword"></textarea> </dd> <dd class="submitbutton"> <input type="hidden" name="search" value="go" /> <input type="submit" value="Begin Specialty Search" /> </dd> </dl> </form> <?php } ?> </div></div> </div> <?php get_sidebar(); ?> </div> <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><?php get_footer(); ?>