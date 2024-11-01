<?php 
// Get countries 
global $woocommerce;
$countries_obj   = new WC_Countries();
$countries   = $countries_obj->__get('countries');
$default_country = $countries_obj->get_base_country();
$default_county_states = $countries_obj->get_states( $default_country ); 
// Import data Via csv
if(sanitize_text_field($_POST['submitcsvdata'])){
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
     if(!empty(sanitize_text_field($_FILES['file']['name'])) && in_array(sanitize_text_field($_FILES['file']['type']), $csvMimes)){
        if(is_uploaded_file($_FILES['file']['tmp_name'])){
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            fgetcsv($csvFile);
			$lines = array();
            while(($line = fgetcsv($csvFile)) !== FALSE){
				$lines[] = $line;
            }
			$seralizedArray = serialize($lines);
	        update_option( 'sunarc_country_state', $seralizedArray ); 
            fclose($csvFile);
            wp_redirect(home_url().'/wp-admin/admin.php?page=table-rate');
            echo "Data successfully imported";
        }
		
}
}
?>
<h1>Table Rates Shipping</h1>
<table width="600">
<form action="" method="post" enctype="multipart/form-data">
<tr>
<td width="20%">Import CSV</td>
<td width="80%"><input type="file" name="file" id="file" /></td>
</tr>

<tr>
<td></td>
<td><input type="submit" name="submitcsvdata" /></td>
</tr>

</form>
</table>
<br><br>
<div class="container">
	<form method="post" action="">
  <table id="example" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <td>Country</td>
            <td>Region/State</td>
            <td>Zip/Postal Code</td>
            <td>Weight (and above)</td>
            <td>Shipping Price</td>
        </tr>
    </thead>
    <tbody>
	
	
	
	
	<?php foreach($datas as $data) { ?> 
	 <tr>
          
         <td class="col-sm-4"><?php echo $data[0];?></td>
            <td class="col-sm-4"><?php echo $data[1];?></td>
            <td class="col-sm-3"><?php echo $data[2]; ?></td>
			<td class="col-sm-3"><?php echo $data[3];?></td>
			<td class="col-sm-3"><?php echo $data[4]; ?></td>
           
        </tr>

	<?php }?>
 </tbody>
	</table>
</form>
<br>
<a class="btn btn-lg btn-block " href="admin.php?page=wc-settings&tab=shipping&section=sunarc">Shipping Page</a>
</div>
