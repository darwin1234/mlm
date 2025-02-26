<?php 

require __DIR__ . '/wp-blog-header.php';

$query = $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}bmlm_gtree_nodes WHERE parent = %d AND child > 0",
   143
);
$children = $wpdb->get_results($query);

foreach ($children as $child) {
  //echo "<br>";
  // echo $child->child;
   $test = bmlm_get_sponsor_childrens($child->child );
   $test = bmlm_get_sposnors_miscellaneous($test);
   echo "<pre>";
   var_dump($test);
   echo "</pre>";
}

function bmlm_get_sponsor_childrens( $sponsor_user_id ) {
    global $wpdb;
    $wpdb_obj     = $wpdb;

    //wp_die($sponsor_user_id);
    $current_user = array( 'id' => strval( $sponsor_user_id ) );
    $query = $wpdb_obj->prepare(
        "SELECT DISTINCT(child) as id 
         FROM {$wpdb_obj->prefix}bmlm_gtree_nodes 
         WHERE parent = %d AND child > 0",
        intval( $sponsor_user_id )
    );
    
    $users        = $wpdb_obj->get_results( $query, ARRAY_A );

    if ( ! empty( $users ) ) {
        array_push( $users, $current_user );
    } else {
        $users = array( $current_user );
    }
    return $users;
}

function bmlm_get_sposnors_miscellaneous( $user_ids ) {
    $primary_data = bmlm_get_sposnors_primary( $user_ids );

    $primary_data = json_decode( wp_json_encode( $primary_data ), true );

    /*foreach ( $primary_data as $key => $user ) {
        $profile_image                           = md5( strtolower( trim( $user['user_email'] ) ) );
        $member_count                            = $this->bmlm_sponsor_get_downline_member_count( $user['ID'] );
        $primary_data[ $key ]['downline_member'] = $member_count;
        $primary_data[ $key ]['refferal_id']     = $this->bmlm_get_sponsor_user_id( $primary_data[ $key ]['refferal_id'] );
        $primary_data[ $key ]['profileUrl']      = $this->is_admin ? admin_url( 'admin.php?page=bmlm_sponsors&section=sponsor-general&action=manage&sponsor_id=' . $user['ID'] ) : '';
        $primary_data[ $key ]['imageUrl']        = ( $user['status'] ) ? 'https://www.gravatar.com/avatar/' . $profile_image . '?s=58' : BMLM_PLUGIN_URL . 'assets/images/ban-user.png';
        
    }*/

    return $primary_data;
}
 function bmlm_get_sposnors_primary( $user_ids ) {
    $mapped_ids = wp_list_pluck( $user_ids, 'id' );

    $roles = apply_filters( 'bmlm_modify_roles', array( 'administrator', 'bmlm_sponsor' ) );
    $args  = array(
        'role__in'    => $roles,
        'order'       => 'DESC',
        'orderby'     => 'ID',
        'count_total' => false,
        'include'     => $mapped_ids,
        'fields'      => array( 'ID', 'user_login', 'display_name', 'user_email', 'user_registered' ),
    );

    $user_query = new \WP_User_Query( $args );
    $users      = $user_query->get_results();

 //   $users = $this->network_user->bmlm_validate_network_users( $users, $mapped_ids, $this );

    return $users;
}