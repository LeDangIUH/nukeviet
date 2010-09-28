<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 2-1-2010 21:37
 */

if ( ! defined( 'NV_IS_FILE_SETTINGS' ) ) die( 'Stop!!!' );

$id = $nv_Request->get_int( 'id', 'get', 0 );

if ( empty( $id ) )
{
    Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&op=cronjobs" );
    die();
}

$query = "SELECT * FROM `" . NV_CRONJOBS_GLOBALTABLE . "` WHERE `id`=" . $id . " AND `is_sys`=0";
$result = $db->sql_query( $query );
$numrows = $db->sql_numrows( $result );
if ( empty( $numrows ) )
{
    Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&op=cronjobs" );
    die();
}

$row = $db->sql_fetchrow( $result );

$error = "";
if ( $nv_Request->get_int( 'save', 'post' ) == '1' )
{
    $cron_name = filter_text_input( 'cron_name', 'post', '', 1 );
    $run_file = filter_text_input( 'run_file', 'post', '' );
    $run_func = filter_text_input( 'run_func_iavim', 'post', '' );
    $params = filter_text_input( 'params_iavim', 'post', '' );
    $interval = $nv_Request->get_int( 'interval_iavim', 'post', 0 );
    $del = $nv_Request->get_int( 'del', 'post', 0 );
    
    $min = $nv_Request->get_int( 'min', 'post', 0 );
    $hour = $nv_Request->get_int( 'hour', 'post', 0 );
    $day = $nv_Request->get_int( 'day', 'post', 0 );
    $month = $nv_Request->get_int( 'month', 'post', 0 );
    $year = $nv_Request->get_int( 'year', 'post', 0 );
    
    if ( empty( $cron_name ) )
    {
        $error = $lang_module['cron_name_empty'];
    }
    elseif ( ! empty( $run_file ) and ! file_exists( NV_ROOTDIR . '/includes/cronjobs/' . $run_file ) )
    {
        $error = $lang_module['file_not_exist'];
    }
    elseif ( empty( $run_func ) or ! preg_match( $global_config['check_cron'], $run_func ) )
    {
        $error = $lang_module['func_name_invalid'];
    }
    else
    {
        $start_time = mktime( $hour, $min, 0, $month, $day, $year );
        
        if ( ! empty( $params ) )
        {
            $params = explode( ",", $params );
            $params = array_map( "trim", $params );
            $params = implode( ",", $params );
        }
        
        $sql = "UPDATE `" . NV_CRONJOBS_GLOBALTABLE . "` SET `start_time`=" . $start_time . ", `interval`=" . $interval . ", 
			`run_file`=" . $db->dbescape( $run_file ) . ", `run_func`=" . $db->dbescape( $run_func ) . ", `params`=" . $db->dbescape( $params ) . ", `del`=" . $del . ", `" . NV_LANG_INTERFACE . "_cron_name`=" . $db->dbescape( $cron_name ) . " WHERE `id`=" . $id;
        $db->sql_query( $sql );
        Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&op=cronjobs" );
        die();
    }
}
else
{
    $cron_name = $row[NV_LANG_INTERFACE . '_cron_name'];
    $run_file = $row['run_file'];
    $run_func = $row['run_func'];
    $params = ! empty( $row['params'] ) ? implode( ", ", explode( ",", $row['params'] ) ) : "";
    $interval = intval( $row['interval'] );
    $del = intval( $row['del'] );
    list( $min, $hour, $day, $month, $year ) = array_map( "trim", explode( ",", date( "i,G,j,n,Y", $row['start_time'] ) ) );
}

$contents = array();
$contents['is_error'] = ! empty( $error ) ? 1 : 0;
$contents['title'] = ! empty( $error ) ? $error : $lang_module['nv_admin_edit_title'];
$contents['action'] = NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=" . $op . "&amp;id=" . $id;
$contents['cron_name'] = array( 
    $lang_module['cron_name'], $cron_name, 100 
);

$filelist = nv_scandir( NV_ROOTDIR . '/includes/cronjobs', "/^([a-zA-Z0-9\_\.]+)\.php$/" );
$contents['run_file'] = array( 
    $lang_module['run_file'], $lang_module['file_none'], $filelist, $run_file, $lang_module['run_file_info'] 
);
$contents['run_func'] = array( 
    $lang_module['run_func'], $run_func, 255, $lang_module['run_func_info'] 
);
$contents['params'] = array( 
    $lang_module['params'], $params, 255, $lang_module['params_info'] 
);
$contents['start_time'] = $lang_module['start_time'];
$contents['min'] = array( 
    $lang_module['min'], $min 
);
$contents['hour'] = array( 
    $lang_module['hour'], $hour 
);
$contents['day'] = array( 
    $lang_module['day'], $day 
);
$contents['month'] = array( 
    $lang_module['month'], $month 
);
$contents['year'] = array( 
    $lang_module['year'], $year 
);
$contents['interval'] = array( 
    $lang_module['interval'], $interval, 11, $lang_module['min'], $lang_module['interval_info'] 
);
$contents['del'] = array( 
    $lang_module['is_del'], $del 
);

$contents['submit'] = $lang_global['save'];
$contents = call_user_func( "nv_admin_edit_theme", $contents );

$page_title = $lang_global['mod_cronjobs'] . " -> " . $lang_module['nv_admin_edit'];
$set_active_op = "cronjobs";

include ( NV_ROOTDIR . "/includes/header.php" );
echo nv_admin_theme( $contents );
include ( NV_ROOTDIR . "/includes/footer.php" );

?>