<?php
global $wpdb;
global $wp_query;
$impressionTable = $wpdb->prefix.'count_impression';
$paidTable = $wpdb->prefix.'count_paid';

$curpage = ( $_GET['paged'] ) ? $_GET['paged'] : 1;

$args = array(
	'post_type' => 'product',	
	'posts_per_page' => 10, 
	'paged' => $curpage,
	'meta_query' => array(
        array(
           'key' => 'audio_file',
           'compare' => 'EXISTS'
        ),
   	)
);
$the_query = new WP_Query( $args );
 
?>
<div class="wrap"><h2>Sound Impression</h2></div>
<table class="widefat">
	<thead> 
        <tr>
        	<th><strong>#Id</strong></th>
            <th><strong>Post Title</strong></th>
            <th><strong>User Email</strong></th>            
            <th><strong>View Count</strong></th>
            <th><strong>Paid Count</strong></th>
            <th><strong>Unpaid Count</strong></th>
            <th><strong>Pay Now</strong></th>
        </tr>
    </thead>
    <tbody> 
	<?php
    if ( $the_query->have_posts() ) {

        while ( $the_query->have_posts() ) {  $the_query->the_post(); 			
			
			$postID = get_the_ID();
			
			$isViewCount = 0;
						
			$productCategory = wp_get_post_terms($postID, 'product_cat', array("fields" => "all"));
			foreach($productCategory as $productCat) {
				$ab = get_field('count_sound_view', 'product_cat_' . $productCat->term_id,true);
				if($ab[0]==1) {
					$isViewCount = 1;
					break;
				}
				
			}
			
			if($isViewCount==0) {
				continue;	
			}
			
			$author_id = get_post_field( 'post_author', $postID );			
			$authorInfo = get_userdata($author_id);
			
			$viewCount = $wpdb->get_var( "SELECT COUNT(*) FROM ".$impressionTable ." where `post_id`='".$postID."'" );
			$paidCount = $wpdb->get_var( "SELECT COALESCE(SUM(amount),0) FROM ".$paidTable." where `post_id`='".$postID."'");
			$unpaidCount = $viewCount-$paidCount;
			
		?>
            <tr data-id="<?php echo $postID; ?>" data-user="<?php echo $author_id; ?>">
                
                <td>#<?php echo $postID; ?></td>
                
                <td><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo get_the_title(); ?></a></td>  
                 
                <td><?php echo $authorInfo->user_email; ?></td>         
                
                <td><?php echo $viewCount; ?></td>
                
                <td class="paid-count"><?php echo $paidCount; ?></td>
                
                <td class="unpaid-count"><?php echo $unpaidCount; ?></td>
                
                <td class="<?php if($unpaidCount<=0) { echo "disableRow"; } ?>"><input type="number" placeholder="Eg: 100" min="1" <?php if($unpaidCount<=0) { echo "readonly"; } ?>/><span class="dashicons dashicons-yes pay_now"></span></td>
            
            </tr>
        
		<?php 
		}
		?>
		
			</tbody>
		</table> 
		<?php  
		      
        wp_reset_postdata();
    }
    
	echo '<div id="wp_pagination">
			<a class="first page button" href="'.get_pagenum_link(1).'">&laquo;</a>
			<a class="previous page button" href="'.get_pagenum_link(($curpage-1 > 0 ? $curpage-1 : 1)).'">&lsaquo;</a>';
			for($i=1;$i<=$the_query->max_num_pages;$i++)
				echo '<a class="'.($i == $curpage ? 'active ' : '').'page button" href="'.get_pagenum_link($i).'">'.$i.'</a>';
			echo '
			<a class="next page button" href="'.get_pagenum_link(($curpage+1 <= $the_query->max_num_pages ? $curpage+1 : $the_query->max_num_pages)).'">&rsaquo;</a>
			<a class="last page button" href="'.get_pagenum_link($the_query->max_num_pages).'">&raquo;</a>
		</div>';
	?>
</div>