<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
    return;