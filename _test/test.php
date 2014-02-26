<?php


function wpds_test_copy_file_start() {
    echo 'start copy files';
    WPDuplicate_Site_Admin::copy_file(15,16);

}

wpds_test_copy_file_start();