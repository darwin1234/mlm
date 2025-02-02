<?php
    if ( ! defined( 'ABSPATH' ) ) exit; 
    define('GHLCONNECTPRO_AUTH_URL',"https://app.ibsofts.com/crm-connect/");
    define('GHLCONNECTPRO_AUTH_END_POINT','https://marketplace.gohighlevel.com/oauth/chooselocation');
    //for Get Contact Data
    define('GHLCONNECTPRO_CONTACT_DATA_API',"https://services.leadconnectorhq.com/contacts/upsert");
    define('GHLCONNECTPRO_CONTACT_DATA_VERSION','2021-07-28');
    
    
    // Add Contact Tags
    define('GHLCONNECTPRO_ADD_CONTACT_TAGS_API',"https://services.leadconnectorhq.com/contacts/");
    define('GHLCONNECTPRO_ADD_CONTACT_TAGS_VERSION','2021-07-28');
    
    
    // Add Contact to Campaign
    define('GHLCONNECTPRO_ADD_CONTACT_TO_CAMPAIGN_API',"https://services.leadconnectorhq.com/contacts/");
    define('GHLCONNECTPRO_ADD_CONTACT_TO_CAMPAIGN_VERSION','2021-07-28');
    
    //Add Contact to Workflow
    define('GHLCONNECTPRO_ADD_CONTACT_TO_WORKFLOW_API',"https://services.leadconnectorhq.com/contacts/");
    define('GHLCONNECTPRO_ADD_CONTACT_TO_WORKFLOW_VERSION','2021-07-28');
    
    //ghl-get-campaigns.php
    define('GHLCONNECTPRO_GET_CAMPAIGNS_API',"https://services.leadconnectorhq.com/campaigns/?locationId=");
    define('GHLCONNECTPRO_GET_CAMPAIGNS_VERSION','2021-04-15');
    
    //ghl-get-tags.php
    define('GHLCONNECTPRO_GET_TAGS_API',"https://services.leadconnectorhq.com/locations/");
    define('GHLCONNECTPRO_GET_TAGS_VERSION','2021-07-28');
    
    //ghl-get-token.php
    define('GHLCONNECTPRO_GET_TOKEN_API',"https://services.leadconnectorhq.com/oauth/token");
    
    //ghl-get-workflows.php
    define('GHLCONNECTPRO_GET_WORKFLOWS_API',"https://services.leadconnectorhq.com/workflows/?locationId=");
    define('GHLCONNECTPRO_GET_WORKFLOWS_VERSION','2021-07-28');

?>